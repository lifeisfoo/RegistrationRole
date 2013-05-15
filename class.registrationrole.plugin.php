<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['RegistrationRole'] = array(
   'Name' => 'Registration Role',
   'Description' => 'This plugin allows users to select a role during registration.',
   'Version' => '0.1',
   'RequiredApplications' => array('Vanilla' => '2.0.18'),
   'RequiredTheme' => FALSE, 
   'SettingsUrl' => 'settings/registrationrole',
   'SettingsPermission' => 'Garden.Settings.Manage',
   'RegisterPermissions' => FALSE,
   'Author' => "Alessandro Miliucci",
   'AuthorEmail' => 'lifeisfoo@gmail.com',
   'AuthorUrl' => 'http://forkwait.net'
);

class RegistrationRolePlugin extends Gdn_Plugin {
 
  /**
   * Display a link in the dashboard side panel
   */  
  public function Base_GetAppSettingsMenuItems_Handler($Sender) {    
    $LinkText = T('Registration Role');
    $Menu = $Sender->EventArguments['SideMenu'];
    $Menu->AddLink('Users', $LinkText, 'settings/registrationrole', 'Garden.Settings.Manage');
  }

  /**
   * Generate data to be displayed in the plugin's settings page
   */
  public function SettingsController_RegistrationRole_Create($Sender) {
    $Sender->Permission('Garden.Plugins.Manage');
    $Sender->AddSideMenu();
    $Sender->Title('Select roles that can be selected by users at registration');
    $ConfigurationModule = new ConfigurationModule($Sender);
    $ConfigurationModule->RenderAll = True;
    $DynamicSchema = array();
    $RoleModel = new RoleModel();
    foreach($RoleModel->Get() as $Role){
      $RoleName = $Role->Name;
      if ($RoleName != 'Administrator') {
        $RoleNameNoSpace = self::normalizeName($RoleName);
        $SingleRoleSchema = array(
          'Plugins.RegistrationRole.'.$RoleNameNoSpace => array(
            'LabelCode' => $RoleName, 
            'Control' => 'CheckBox', 
            'Default' => C('Plugins.RegistrationRole'.$RoleNameNoSpace, '')
          )
        );
      $DynamicSchema = array_merge($DynamicSchema, $SingleRoleSchema);
      }
    }
    
    $Schema = $DynamicSchema;
    $ConfigurationModule->Schema($Schema);
    $ConfigurationModule->Initialize();
    $Sender->View = dirname(__FILE__) . DS . 'views' . DS . 'settings.php';
    $Sender->ConfigurationModule = $ConfigurationModule;
    $Sender->Render();
  }
  
  /**
   * Replace all whitespaces in the string with underscores( vanilla conf keys doesn't support whitespaces)
   */
  private static function normalizeName($CatName){
    return str_replace(" ", "_", $CatName);
  }

  /**
   * Replace underscores with whitespaces
   */
  private static function denormalizeName($CatName){
    return str_replace("_", " ", $CatName);
  }

  /**
   * Replaces registration pages with custom pages (with role selector)
   */
  public function EntryController_Render_Before($Sender) {
    $RoleNames = array();
    foreach (C('Plugins.RegistrationRole') as $Key => $Value) {
      if( strcmp(C('Plugins.RegistrationRole.' . $Key, '0'), '1') == 0 ){
        array_push($RoleNames, self::denormalizeName($Key));
      }
    }
    $Sender->RegistrationRoles = array('test' => 1, 'role2' => 2, 'role3' => 3);;
    $Roles= Gdn::SQL()->Select('r.RoleID', '', 'value')
                           ->Select('r.Name', '', 'text')
                           ->From('Role r')
                           ->WhereIn('r.Name', $RoleNames)
                           ->Get();
    $Sender->RegistrationRoles = $Roles;
    if (strtolower($Sender->RequestMethod) == 'register' 
        || strtolower($Sender->RequestMethod) == 'connect'){//only on registration/connect page
      $RegistrationMethod = $Sender->View;
      switch ($RegistrationMethod) {
        case 'RegisterCaptcha':
          $Sender->View=$this->GetView('registercaptcha.php');
          break;
        case 'RegisterApproval':
          $Sender->View=$this->GetView('registerapproval.php');
          break;
        case 'RegisterInvitation':
          $Sender->View=$this->GetView('registerinvitation.php');
          break;
        case 'connect':
          $Sender->View=$this->GetView('connect.php');
          break;
        default:
          var_dump("default");
          break;
      }
    }
  }

  /**
   * Save selected role
   */
  public function UserModel_AfterInsertUser_Handler($Sender) {
    if (!(Gdn::Controller() instanceof Gdn_Controller)) return;
      
    //Get user-submitted
    $FormPostValues = Gdn::Controller()->Form->FormValues();
    $UserID = GetValue('InsertUserID', $Sender->EventArguments);
    $RoleID = GetValue('Plugin.RegistrationRole.RoleID', $FormPostValues);

    //keep current roles
    $CurrentRoles = Gdn::UserModel()->GetRoles($UserID);
    $RolesToSave = "";
    foreach ($CurrentRoles as $ARole) {
      $RolesToSave .= GetValue('Name', $ARole) . ',';
    }
    //Add selected role
    $RoleModel = new RoleModel();
    $RolesToSave .= GetValue('Name', $RoleModel->GetByRoleID($RoleID));

    //SaveRoles expect a string like "Moderator, Member, ..." see class.usermodel.php
    Gdn::UserModel()->SaveRoles($UserID, $RolesToSave);
  }

  public function Structure() {}

  public function Setup() {}
   
  public function OnDisable() {}

}
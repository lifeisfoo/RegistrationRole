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
   
  public function Base_GetAppSettingsMenuItems_Handler($Sender) {    
    $LinkText = T('Registration Role');
    $Menu = $Sender->EventArguments['SideMenu'];
    //$Menu->AddItem('Users', T('Users'));
    $Menu->AddLink('Users', $LinkText, 'settings/registrationrole', 'Garden.Settings.Manage');
  }

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
  
  private static function normalizeName($CatName){
    return str_replace(" ", "_", $CatName);
  }

  public function EntryController_Render_Before($Sender) {
    echo "EntryController_Render_Before";
    echo "<pre>";
    echo $Sender->View;
    //var_dump($Sender);
    var_dump(C('Plugins.RegistrationRole'));
    echo "</pre>";
    //$RoleNames = explode(',', C('Plugins.EasyMembersList.HideTheseUsers', ''));
    //$TrimmedNames = $this->trimNames($Names);

    $Sender->RegistrationRoles = array('test' => 1, 'role2' => 2, 'role3' => 3);;
    $Groups = Gdn::SQL()->Select('r.RoleID', '', 'value')
                           ->Select('r.Name', '', 'text')
                           ->From('Role r')
                           //->WhereIn('Name',$TrimmedNames)
                           ->Get();
    $Sender->RegistrationRoles = $Groups;
    if (strtolower($Sender->RequestMethod) == 'register'){//only on registration page
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
          # continue to vanilla default view
          break;
      }
    }
  }

  public function Structure() {}

  public function Setup() {}
   
  public function OnDisable() {}

}
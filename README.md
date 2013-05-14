# Registration role for Vanilla Forums
This plugin allows users to select a role during registration.

Requires Vanilla >= 2.0

#ROADMAP

## Versione 0.1 
* OK - trovare hook per sovrascrivere vista registrazione in 2.0 (spiegare poi a lui nell'email che è ok perché tanto ora lo sviluppo della 2.0.x è solo di bugfix e che per qualunque cosa può sempre contattarmi)
* OK - fare in modo che l'override sia selettivo solo su quell'operazione (registrazione)
* OK - non deve intaccare il signIn
* fare in modo che sia compatibili con tutti i metodi di registrazione (captcha, diretta, etc) = sovrascrivere più viste
** test diversi metodi di registrazione
*** 'Closed' -> lasciare vista default
*** 'Basic' -> vista modificata Basic RegisterCaptcha (vedi caso sotto)
*** 'Captcha' -> vista modificata Basic RegisterCaptcha
*** 'Approval' -> vista modificata RegisterApproval
*** 'Invitation' -> vista modificata RegisterInvitation
*** 'Connect' -> gestisce casi facebook e google

** quindi 3 casi da gestire
** prima -> basic/captcha
*** testare anche usando google 
*** [cit. Registration will be basic with email verification and single sign-on (google / facebook)]
** poi -> connect
** dopo -> approval
** dopo -> invitation

* prendere lista ruoli da pannello di configurazione
* hook per intercettare registrazione e controllo della selezione del ruolo (che sia tra quelli impostati)
* l'hook deve essere selettivo solo alla registrazione e non alla modifica del profilo
* controllare che dopo la registrazione l'utente anche abbia gli altri ruoli di default (applicant credo, controllare)

## Versione 0.2
* supporto per vanilla 2.1, senza override delle viste (usare metodo di profile extender)
* riutilizzare la stessa configurazione della 0.1 senza modifiche o alterazioni

* Supporto connect??
##Author and License
Alessandro Miliucci, GPL v3

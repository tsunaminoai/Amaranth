<?php
ini_set('error_reporting', E_ALL );
//ini_set('error_reporting', false );
ini_set('display_errors',true);

spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.class.php';});

$folders = array('inc','lib');
foreach ($folders as $folder)
{
	foreach (glob($folder.'/*.php') as $filename)
		include_once( $filename );
}

require_once('./config.php');
global $CONFIG;

Session::startSession();

try{
	$d = Debug::getDebugger(U_DEBUG);
    $db = DB::getConnection();
}catch(Exception $e)
{
    $d->trace($e);
}

$login = new Login();

if(in_array($_GET['Saction'],array('processLogin','processLogout')))
	Action::handleAction($_GET['Saction'],$_POST);

$siteUser = $login->checkLogin();

if($siteUser)
	$login->showLogoutForm();
else
{
	$signup = new Signup();
	$signup->showSignupForm();
}

Action::handleAction($_GET['action'],$_POST);


$db->db_disconnect();
?>

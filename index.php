<?php
ini_set('error_reporting', E_ALL );
//ini_set('error_reporting', false );
ini_set('display_errors',true);

$folders = array('inc','classes','lib');
foreach ($folders as $folder)
{
	foreach (glob($folder.'/*.php') as $filename)
		include_once( $filename );
}

require_once('./config.php');
global $CONFIG;

Session::startSession();

try{
	Debug::getDebugger(U_DEBUG);
    DB::getConnection();
}catch(Exception $e)
{
    $debug->trace($e);
}


$user = new User();
$user->checkLogin();

Action::handleAction($_GET['action'],$_POST);

?>

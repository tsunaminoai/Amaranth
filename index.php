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



$siteUser = new Login();
$siteUser->checkLogin();

Action::handleAction($_GET['action'],$_POST);


$db->db_disconnect();
?>

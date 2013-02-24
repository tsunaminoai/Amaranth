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

Debug::getDebugger(U_DEBUG);

try{
    DB::getConnection();
}catch(Exception $e)
{
    $debug->trace($e);
}

$user = new User();
$user->loadUser(1);
echo $user->getUsername();

//$db->doquery('insert into user values ();');
//$db->doquery('delete from user where user_id != 1;');
?>

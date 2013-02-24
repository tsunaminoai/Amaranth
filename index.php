<?php
ini_set('error_reporting', E_ALL );
//ini_set('error_reporting', false );
ini_set('display_errors',true);

require('./config.php');
global $CONFIG;

$folders = array('classes','inc','lib');
foreach ($folders as $folder)
{
	foreach (glob($folder.'/*.php') as $filename)
		include_once( $filename );
}


$debug = new debug(U_DEBUG);

try{
    $db = new DB($CONFIG['db'],$debug,$CONFIG['memcached']);
}catch(Exception $e)
{
    $debug->trace($e);
}

$user = new User($db);
$user->loadUser(1);
echo $user->getUsername();

//$db->doquery('insert into user values ();');
//$db->doquery('delete from user where user_id != 1;');
?>

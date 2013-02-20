<?php
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors',true);

require('./classes/debug.class.php');
require('./config.php');
require('./classes/db.class.php');

$debug = new debug(DEBUG);
set_error_handler ( array(&$debug,'errorHandle') );
$debug->setLogFile('./log');
$db = new DB($CONFIG['db'],$CONFIG['debug'],$CONFIG['memcached']);

$res = $db->doquery('select * from user',30);
foreach ($res as $obj)
    echo 'User: '.$obj->user_id.'<br/>';

$cheese;
//$db->doquery('insert into user values ();');
//$db->doquery('delete from user where user_id != 1;');
?>

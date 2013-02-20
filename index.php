<?php
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors',true);

require('./config.php');
require('./classes/db.class.php');
    
$db = new DB($CONFIG['db'],$CONFIG['debug'],$CONFIG['memcached']);

$res = $db->doquery('select * from user',30);
foreach ($res as $obj)
    echo 'User: '.$obj->user_id.'<br/>';

//$db->doquery('insert into user values ();');
$db->doquery('delete from user where user_id != 1;');
?>

<?php
ini_set('error_reporting', E_ALL );
//ini_set('error_reporting', false );
ini_set('display_errors',true);

require('./config.php');

foreach (glob('classes/*.php') as $filename)
{
    include_once( $filename );
}
foreach (glob('inc/*.php') as $filename)
{
    include_once( $filename );
}

$debug = new debug(U_DEBUG);
$debug->setLogFile('./log');

try{
    $db = new DB($CONFIG['db'],$debug,$CONFIG['memcached']);
}catch(Exception $e)
{
    $debug->trace($e);
}

$res = $db->doquery('select * from user',30);
foreach ($res as $obj)
    echo 'User: '.$obj->sak_user.'<br/>';

//$db->doquery('insert into user values ();');
//$db->doquery('delete from user where user_id != 1;');
?>

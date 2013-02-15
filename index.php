<?php
error_reporting(false);

require('./config.php');
require('./classes/db.class.php');

if($CONFIG['memcached'])
{
    if (extension_loaded('memcached'))
        $mc = new Memcached();
    else if (extension_loaded('memcache'))
        $mc = new Memcache();
    else
        echo "Could not find any memcache module!";
}
    
$db = new DB($CONFIG['db'],$CONFIG['debug'],$mc);

?>
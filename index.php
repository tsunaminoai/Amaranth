<?php
error_reporting(true);

require('./config.php');
require('./classes/db.class.php');
    
$db = new DB($CONFIG['db'],$CONFIG['debug'],$CONFIG['memcached']);

$res = $db->mc_query('select * from user',30);
while($row = mysql_fetch_row($res))
{
	print_r($row);
}
?>

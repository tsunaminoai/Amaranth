<?php

function config_get($section,$value=null)
{
	global $CONFIG;
	if(!$CONFIG)
		throw new Exception('No config variable found');

	if(!$CONFIG[$section][$value])
		return false;
	else
		return $CONFIG[$section][$value];
}

?>

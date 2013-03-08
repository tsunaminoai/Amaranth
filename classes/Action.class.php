<?php

class Action
{
	protected static $_actions = array();
	
	
	public static function addAction($actionName,$hook)
	{
		if(array_key_exists($actionName, $hook))
		{
			Action::$_actions[$actionName][] = $hook;
		} else 
		{
			Action::$_actions[$actionName] = array($hook);	
		}
	}
	
	public static function handleAction($actionName, $args=array())
	{
		$res = null;
		if(array_key_exists($actionName, Action::$_actions))
		{
			foreach(Action::$_actions[$actionName] as $handle)
			{
				if(($res = call_user_func_array ( $handle, array($args) )) === false)
					return false;
			}
		}
		return $res;
	}
	
}

?>

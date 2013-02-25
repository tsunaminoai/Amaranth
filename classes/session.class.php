<?php

class Session
{

	private function __construct()
	{
		session_start();
	}
	
	public static function startSession()
	{
		static $init = null;
		if( $init === null)
			$init = new Session();
		return $init;
	}
	public static function setLogin(User $user)
	{
		$_SESSION['sak_user'] = $user->getSakUser();
		$_SESSION['user_name'] = $user->getUsername();
		$_SESSION['loggedIn'] = true;
	}

	public static function getLogin()
	{
		return isset($_SESSION['loggedIn']);
	}
	
	public function __destruct()
	{
	}
}

?>

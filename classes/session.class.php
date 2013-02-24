<?php

class Session
{

	public function __construct()
	{
		session_start();
	}
	
	public function setLogin(User $user)
	{
		
	}

	public function getLogin()
	{
		return $_SESSION['loggedIn'];
	}
	
	public function __destruct()
	{
	}
}

?>

<?php

class Signup
{

	public function __construct()
	{
		
		Action::addAction('processSignup',array($this,'processSignup'));
	}
	
	
	public function showSignupForm()
	{
		echo '<div id="signupForm">';
		echo '<form action="?action=processSignup" method="post">';
		echo '<div>Username</div>';
		echo '<div><input type="text" length="45" name="sub_username" id="sub_username" /></div>';
		echo '<div>Email Address</div>';
		echo '<div><input type="text" length="45" name="sub_email" id="sub_umail" /></div>';
		echo '<div>Password</div>';
		echo '<div><input type="password" length="45" name="sub_password" id="sub_password" /></div>';
		echo '<div><input type="submit" name="sub_submit" id="sub_submit" /></div>';
		echo '</form>';
		echo '</div>';
	}
	
	public function processSignup($args)
	{
		
		var_dump($args);
	}

}

?>
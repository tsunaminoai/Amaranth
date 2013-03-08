<?php

class Login
{

	public function __construct()
	{
		
		Action::addAction('processLogin',array($this,'processLogin'));
		Action::addAction('processLogout',array($this,'processLogout'));		
	}
	
	public function checkLogin()
	{
		if(!Session::getLogin())
		{
			$this->showLoginForm();
			return false;
		}
		else
		{
			return new User(Session::getUserSak());
		}
	}

    public function showLoginForm()
    {
    	echo '<div id="login_form">';
    	echo '<form action="?Saction=processLogin" method="post">
    		  <input type="text" name="login_username" id="login_username" />
    		  <input type="password" name="login_password" id="login_password" />
    		  <input type="submit" name="login_submit" id="login_submit" />
    		  </form>';
    	echo '</div>';
    }
    
    public function showLogoutForm()
    {
	    echo '<div id="logout_form">';
	    echo '<a href="?Saction=processLogout">Logout</a>';
	    echo '&nbsp;<a href="?action=words">test</a>';
	    echo '</div>';
    }
    
    public function processLogin($args=array())
    {
		$db = DB::getConnection();
		
    	$sql = 'select 	sak_user
    			from	user
    			where	user_name = "'.$db->sanitize($_POST['login_username']).'"
    			and		password = "'.md5($_POST['login_password']).'"
    			;';
    	$res = $db->doquery($sql);
    	if($res)
    	{
	    	$siteUser = new User($res[0]->sak_user);
	    	Session::setLogin($siteUser);
    	}else
    	{
	    	echo '<div id="login_error">';
	    	echo 'Invalid username or password';
	    	echo '</div>';
    	}
    	
    }
    
    public function processLogout($args=array())
    {
	    Session::destroySession();
	    Session::startSession();
    }

}

?>
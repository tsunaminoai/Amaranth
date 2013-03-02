<?php

class Login extends User
{

	public function __contruct()
	{
		parent::__construct();
		
		Action::addAction('processLogin',array($this,'processLogin'));		
	}
	
	public function checkLogin()
	{
		if(!Session::getLogin())
		{
			$this->doLogin();
		}
		else
		{
			$this->_mc_set();
			$test = $this->_mc_get();
		}
	}

    public function doLogin()
    {
    	echo '<form action="?action=processLogin" method="post" id="login_form">
    		<input type="text" name="login_username" id="login_username" />
    		<input type="password" name="login_password" id="login_password" />
    		<input type="submit" name="login_submit" id="login_submit" />
    		</form>';
    }
    
    public function processLogin($args=array())
    {
    	$sql = 'select 	sak_user
    			from	user
    			where	user_name = "'.$this->db->sanitize($_POST['login_username']).'"
    			and		password = "'.md5($_POST['login_password']).'"
    			;';
    	$res = $this->_db->doquery($sql);
    	if($res)
    		$this->loadUser($res[0]->sak_user);
    	
    	Session::setLogin($this);
    }

}

?>
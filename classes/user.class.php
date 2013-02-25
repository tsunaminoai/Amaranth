<?php

class User
{
    private $_sak_user;
    private $_user_name;
    
    protected $db;
    protected $debug;
    
    public function __construct()
    {
		$this->db = DB::getConnection();
		$this->debug = Debug::getDebugger();
		
		Action::addAction('processLogin',array($this,'processLogin'));
        return;
    }

	public function checkLogin()
	{
		if(!Session::getLogin())
		{
			$this->doLogin();
		}
		else
		{
		
		}
	}

    private function getLoadUserSQL($sak_user)
    {
        $sak_user = $this->db->sanitize($sak_user);
        $sql = 'select sak_user, user_name from user where sak_user = '.$sak_user;
        return $sql;
    }
    public function loadUser($sak_user)
    {
        $sql = $this->getLoadUserSQL($sak_user);
        $res = $this->db->doquery($sql);
        
        if( sizeof($res) > 1)
            throw new Exception('Too many rows returned. SAK_USER = '.$sak_user,U_ERROR);
        else if ( sizeof($res) == 0)
            throw new Exception('No row found. SAK_USER = '.$sak_user,U_WARNING);
        
        $obj = array_pop($res);
        
        $this->_sak_user = $obj->sak_user;
        $this->_user_name = $obj->user_name;
        

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
    	$res = $this->db->doquery($sql);
    	if($res)
    		$this->loadUser($res[0]->sak_user);
    	
    	Session::setLogin($this);
    }
    
    public function getUsername()
    {
        return $this->_user_name;
    }
    
    public function getSakUser()
    {
        return $this->_sak_user;
    }
    
    private function log($func,$message,$level=U_DEBUG)
    {
        if($this->debug)
            $this->debug->log(__CLASS__ , $func,$message,$level);
    }
}

?>

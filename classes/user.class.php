<?php

class User extends Memcached_Class
{
    protected $_sak;
    private $_user_name;
    
    protected $_db;
    protected $_debug;
    
    public function __construct()
    {
    	parent::__construct();
		$this->_db = DB::getConnection();
		$this->_debug = Debug::getDebugger();
		
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

    private function _getLoadUserSQL($sak_user)
    {
        $sak_user = $this->_db->sanitize($sak_user);
        $sql = 'select sak_user, user_name from user where sak_user = '.$sak_user;
        return $sql;
    }
    
    public function loadUser($sak_user)
    {
        $sql = $this->_getLoadUserSQL($sak_user);
        $res = $this->_db->doquery($sql);
        
        if( sizeof($res) > 1)
            throw new Exception('Too many rows returned. SAK_USER = '.$sak_user,U_ERROR);
        else if ( sizeof($res) == 0)
            throw new Exception('No row found. SAK_USER = '.$sak_user,U_WARNING);
        
        $obj = array_pop($res);
        
        $this->_sak = $obj->sak_user;
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
    
    
    public function getUsername()
    {
        return $this->_user_name;
    }
    
    public function getSakUser()
    {
        return $this->_sak;
    }
    
    private function _log($func,$message,$level=U_DEBUG)
    {
        if($this->_debug)
            $this->_debug->log(__CLASS__ , $func,$message,$level);
    }
}

?>

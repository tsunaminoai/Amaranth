<?php

class User
{
    private $_sak_user;
    private $_user_name;
    
    protected $db;
    
    public function __construct($dblink)
    {
        if(!$dblink)
            throw new Exception('No DB link provided!',U_ERROR);
            
        $this->db = $dblink;
        return;
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
    
    public function getUsername()
    {
        return $this->_user_name;
    }
    
    public function getSakUser()
    {
        return $this->_sak_user;
    }
}

?>
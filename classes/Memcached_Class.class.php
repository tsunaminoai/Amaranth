<?php

class Memcached_Class
{
	private $_mc;
	private $_mcdflag;
	
	protected function __construct ()
	{
		$db = DB::getConnection();
		list($this->_mc,$this->_mcdflag) = $db->get_mc();
	}
	
	protected function _mc_set()
	{	
		$class =  get_class($this);
		$sak = $this->_sak;
		
		try
		{
		$hash = md5($class . '_' . $sak);
	    if($this->_mcdflag)
	        $this->_mc->set($hash,$this, 0);
	    else
	        $this->_mc->set($hash,$this, MEMCACHE_COMPRESSED, 0);
	
	    if($this->_mcdflag && $this->_mc->getResultCode())
	        throw new Exception('Memcached Set Error: '.$this->_mc->getResultCode(),U_ERROR);
	    
	    }catch(Exception $e)
        {
        	throw new Exception('Could not make and cache query',U_FATAL,$e);
        }
	    
	}
	
	protected function _mc_get()
	{
	
		$class =  get_class($this);
		$sak = $this->_sak;
		$hash = md5($class . '_' . $sak);

		return $this->_mc->get($hash);
		
	}
}

?>
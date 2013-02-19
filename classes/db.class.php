<?php

class DB
{
	private $debug = false;
	
	private $db_host;
	private $db_user;
	private $db_pass;
	private $db_name;
	private $mc_host;
	private $mc_port;
	private $memcached;
    private $mcdflag;
    
    private $db_link;
    
    private $query_num = 0;
    
	public function __construct($connectinfo,$conndebug=false,$connmemcached=null)
	{
		$this->db_host = $connectinfo['db_host'];
		$this->db_user = $connectinfo['db_user'];
		$this->db_pass = $connectinfo['db_pass'];
		$this->db_name = $connectinfo['db_name'];
		$this->mc_host = $connmemcached['host'];
		$this->mc_port = $connmemcached['port'];
		
		if($conndebug)
			$this->debugOn();
		
		$this->db_connect();
		
		$this->mc_connect();
        
		$this->time_start = $this->getPageTime();
	}
	
	private function mc_connect()
	{
		if($this->mc_host && $this->mc_port)
		{
			$this->debugger('mc_connect','host: '.$this->mc_host.':'.$this->mc_port);
			
			if (extension_loaded('memcached'))
			{
				$this->memcached = new Memcached();
				if(!$this->memcached->addServer($this->mc_host,$this->mc_port))
					$this->errorHandle('Could not connect to memcached server: '.$this->mc_host.' '.$this->mc_port);
                
                $this->mcdflag = true;
			}
			else if (extension_loaded('memcache'))
			{
				$this->memcached = new Memcache();
				$this->memcached->connect($this->mc_host,$this->mc_port);
                $this->mcdflag = false;
			}	
			else
				$this->errorHandle("Could not find any memcache module!");
		}
	}
	
	public function __destruct()
	{
		$this->db_disconnect();
	}

	private function db_connect()
	{
		$this->debugger('db_connect','host: '.$this->db_host);

		$this->db_link = mysql_connect($this->db_host,$this->db_user,$this->db_pass);
		if(mysql_error())
			echo $this->errorHandle('SQL Error:<br/>'."\t".mysql_error());
	
		$this->debugger('db_connect','db: '.$this->db_name);

		mysql_select_db($this->db_name,$this->db_link);
		if(mysql_error())
			echo $this->errorHandle('SQL Error:<br/>'."\t".mysql_error());
	}

	private function db_disconnect()
	{
		mysql_close($this->db_link);
	}

	public function db_query($query)
	{
		$this->debugger('db_query','sql: '.$query);

		$res = mysql_query($query, $this->db_link);

		$this->debugger('db_query','Result: '.mysql_info());

		if(mysql_error())
			echo $this->errorHandle('SQL Error:<br/>'."\t".$query.'<br/><br/>'."\t".mysql_error());
	
		$this->query_num++;
		
		return $this->resourceToArray($res);
	}
	
    public function mc_query($sql,$ttl=30)
    {
    	$hash = md5($sql);
    	$result = $this->memcached->get($hash);
    	$this->debugger('mc_query','Query: '.$sql);
    	$this->debugger('mc_query','Hash: '.$hash);
    	$this->debugger('mc_query','Found: '.($result?'yes':'no'));
    	if($this->mcdflag && ($ret = $this->memcached->getResultCode()) != 0 && $ret != 16 )
    		$this->errorHandle('Memcached Get Error: '.$this->memcached->getResultCode());
    			
    	if(!$result)
    	{
    		$result = $this->db_query($sql);
            if($this->mcdflag)
                $this->memcached->set($hash,$result, time() + $ttl);
    		else
                $this->memcached->set($hash,$result, MEMCACHE_COMPRESSED, time() + $ttl);
            
    		if($this->mcdflag && $this->memcached->getResultCode())
    			$this->errorHandle('Memcached Set Error: '.$this->memcached->getResultCode());
    	}
    	
    	return $result;
    }
    
    private function resourceToArray($res)
    {
        $return = array();
        while($obj = mysql_fetch_object($res))
            $return[] = $obj;
        
        return $return;
    }
    
	public function db_get_insert_id()
	{
		$this->debugger('db_get_insert_id','');
		
		$id = mysql_insert_id();
		if(mysql_error())
			echo $this->errorHandle('SQL Error:<br/>'."\t".mysql_error());
		
		return $id;
	}

	public function query_stat()
	{
		return $this->query_num;
	}
	
	private function debugOn()
	{
		$this->debug = true;
	}
    
    private function debugger($func,$msg)
    {
            if($this->debug === true)
            {
                    echo '<pre>';
                    echo '<b>'.$func.'</b>'."\t";
                    echo $msg;
                    echo '</pre>';
            }
    }

    private function errorHandle($err)
    {
            echo '<pre>';
            echo '<b>Fatal Error: </b>'.$err;
            echo '</pre>';
        exit();
    }

    public function sanitize($input)
    {
        $clean_input = mysql_real_escape_string($input);
        $this->debugger('sanitize','clean: '.$clean_input);
        return $clean_input;
    }

    private function getPageTime()
    {
        $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                return $time;
    }


}

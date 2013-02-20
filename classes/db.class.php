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
	private $mc;
    private $mcdflag;
    
    private $db;
    
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
				$this->mc = new Memcached();
				if(!$this->mc->addServer($this->mc_host,$this->mc_port))
					$this->errorHandle('Could not connect to memcached server: '.$this->mc_host.' '.$this->mc_port);
                
                $this->mcdflag = true;
			}
			else if (extension_loaded('memcache'))
			{
				$this->mc = new Memcache();
				$this->mc->connect($this->mc_host,$this->mc_port);
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

		$this->db = new mysqli($this->db_host,$this->db_user,$this->db_pass, $this->db_name);
		if($this->db->connect_errno)
			echo $this->errorHandle('DB Connection Error:<br/>'."\t".$db->connect_error);
	
		$this->db->autocommit(TRUE);
		
		$this->debugger('db_connect','db: '.$this->db->host_info);

	}

	private function db_disconnect()
	{
		$this->db->close();
	}

	private function db_query($query)
	{
		$this->debugger('db_query','sql: '.$query);

		$res = $this->db->query($query);

		$this->debugger('db_query','Result: '.$this->db->sqlstate);
		$this->debugger('db_query','Info: '.$this->db->info);
		
		if($this->db->errno)
			echo $this->errorHandle('SQL Error:<br/>'."\t".$query.'<br/><br/>'."\tError #".$this->db->errno." - ".$this->db->error);
			
		return $res;
	}
	
    public function doquery($sql,$ttl=30)
    {
    	$sql = $this->sanitize($sql);
    	//if there is no memcached server, default to being a wrapper
    	if(!$this->mc)
    		return $this->db_query($sql);
    	
    	//get the cached query result
    	$hash = md5($sql);
    	$result = $this->mc->get($hash);
    	$this->debugger('mc_query','Query: '.$sql);
    	$this->debugger('mc_query','Hash: '.$hash);
    	$this->debugger('mc_query','Found: '.($result?'yes':'no'));
    	//if the get() is not "not found" or "success" throw error
    	if($this->mcdflag && ($ret = $this->mc->getResultCode()) != 0 && $ret != 16 )
    		$this->errorHandle('Memcached Get Error: '.$this->mc->getResultCode());
    	
    	//if there is no result, make one and cache it
    	if(!$result)
    	{
    		$result = $this->db_query($sql);
    		//if this was an modification query, do resets
            $type = explode(' ',$sql);
    		if(strcasecmp('SELECT',$type[0]) != 0)
    		{
    			$this->do_resets($sql);
    		}
    		//if this was a select query, cache the results
    		else{
    			$result = $this->resourceToArray($result);
				if($this->mcdflag)
					$this->mc->set($hash,$result, time() + $ttl);
				else
					$this->mc->set($hash,$result, MEMCACHE_COMPRESSED, time() + $ttl);
				
				if($this->mcdflag && $this->mc->getResultCode())
					$this->errorHandle('Memcached Set Error: '.$this->mc->getResultCode());
					
				$this->table_cache($hash, $sql);
    		}
    	}
    	
    	return $result;
    }
    
    private function resourceToArray($res)
    {
        $return = array();
        while($obj = $res->fetch_object())
            $return[] = $obj;
        
        return $return;
    }
    
    private function table_cache($hash, $sql)
    {
    	//get all tables used in the query
    	$res = $this->db->query('EXPLAIN '.$sql);
    	while($row = $res->fetch_object())
    	{
    		//for each table used, add mc entry for the SQL hash
    		$table = md5('IDX_'.$row->table);
    		$cache = $this->mc->get($table);
    		if(!$cache || !in_array ($hash, $cache))
    		{
    			$this->debugger('table_cache','adding hash '.$hash.' to table '.$row->table);
    			$cache[] = $hash;
           	 	if($this->mcdflag)
                	$this->mc->set($table,$cache, 0);
    			else
                	$this->mc->set($table,$cache, MEMCACHE_COMPRESSED, 0);
    		}	
    	}
    }
    
    private function do_resets($sql)
    {
    	$type = explode(' ',$sql);
    	$type = strtoupper($type[0]);
    	$matches=array();
    	switch($type)
    	{
    		case 'INSERT': preg_match ( '/INSERT INTO `?(\w+)`?.*/i',$sql,$matches); break;
    		case 'UPDATE': preg_match ( '/UPDATE `?(\w+)`?.*/i',$sql,$matches); break;
    		case 'DELETE': preg_match ( '/DELETE FROM `?(\w+)`?.*/i',$sql,$matches); break;
    		default: return; break;
    	}

		//for each table used, find all SQLs which use that table and expire their entries
		$table = md5('IDX_'.$matches[1]);
		foreach( $this->mc->get($table) as $hash )
		{
			$this->debugger('do_resets','Resetting hash '.$hash.' for table '.$row->table);
			$this->mc->delete($hash);
		}
    	
    }
    
	public function db_get_insert_id()
	{

		
		$id = $this->db->insert_id;
		$this->debugger('db_get_insert_id',$id);
		if($this->db->errno)
			echo $this->errorHandle('SQL Error:<br/>'."\tError #".$this->db->errno." - ".$this->db->error);
		
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

    private function sanitize($input)
    {
        $clean_input = $this->db->real_escape_string($input);
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

<?php

class DB
{
	private $debug;
	
	private $db_host;
	private $db_user;
	private $db_pass;
	private $db_name;
	private $mc_host;
	private $mc_port;
	private static $mc;
    private static $mcdflag;
    private static $ttl;
    
    private static $db;
    
    private static $query_num = 0;
    
    
	private function __construct()
	{
		$this->db_host = config_get('db','db_host');
		$this->db_user = config_get('db','db_user');
		$this->db_pass = config_get('db','db_pass');
		$this->db_name = config_get('db','db_name');
		$this->mc_host = config_get('memcached','host');
		$this->mc_port = config_get('memcached','port');
		$this->ttl = config_get('memcached','ttl');
		
		$this->debug = Debug::getDebugger();
		
		try{$this->db_connect();}
        catch(Exception $e)
        {   throw new Exception('Database connection failed',U_FATAL,$e);}
        
		try{$this->mc_connect();}
        catch(Exception $e)
        {   throw new Exception('Memcache connection failed',U_FATAL,$e);}
        
		$this->time_start = $this->getPageTime();
	}
	
	public static function getConnection()
	{
		static $dbConn = null;
		if($dbConn === null)
		{
			$dbConn = new DB();
		}
		return $dbConn;
	}	
	
	private function mc_connect()
	{
		if($this->mc_host && $this->mc_port)
		{
			$this->log(__FUNCTION__ , 'host: '.$this->mc_host.':'.$this->mc_port,U_DEBUG);
			
			if (extension_loaded('memcached'))
			{
				$this->mc = new Memcached();
                if(!$this->mc->addServer($this->mc_host,$this->mc_port))
                    throw new Exception('Could not connect to memcached server: '.$this->mc_host.' '.$this->mc_port,U_FATAL);
                
                $this->mcdflag = true;
			}
			else if (extension_loaded('memcache'))
			{
				$this->mc = new Memcache();
				if(!$this->mc->connect($this->mc_host,$this->mc_port))
                    throw new Exception('Could not connect to memcache server: '.$this->mc_host.' '.$this->mc_port,U_FATAL);
                $this->mcdflag = false;
			}	
			else
                throw new Exception('Could not find memcahe or memcached modules, but config is set',U_FATAL);
		}
	}
	
	public function get_mc()
	{
		return array($this->mc,$this->mcdflag);
	}
	

	private function db_connect()
	{
		$this->log(__FUNCTION__ , 'host: '.$this->db_host);

		$this->db = new mysqli($this->db_host,$this->db_user,$this->db_pass, $this->db_name);

		if($this->db->connect_errno)
			throw new Exception('DB Connection Error: '.$this->db->connect_error,U_FATAL);
	
		$this->db->autocommit(TRUE);
	}

	public function db_disconnect()
	{
		$this->db->close();
	}


	private function db_query($query)
	{
		$this->log(__FUNCTION__ , 'sql: '.$query);

		$res = $this->db->query($query);

		$this->log(__FUNCTION__ , 'Result: '.$this->db->sqlstate);
		$this->log(__FUNCTION__ ,'Info: '.$this->db->info);
		
		if($this->db->errno)
			throw new Exception('SQL Error:<br/>'."\t".$query.'<br/><br/>'."\tError #".$this->db->errno." - ".$this->db->error, U_FATAL);
			
		return $res;
	}
	
    public function doquery($sql,$ttl=null)
    {
    	//if there is no memcached server, default to being a wrapper
    	if(!$this->mc){
    		try{ $this->db_query($sql);}
            catch(Exception $e){ throw new Exception('Could not query',U_FATAL,$e); } 
    	}
    	
    	if($ttl === null)
    		$ttl = $this->ttl;
    		
    	//get the cached query result
    	$hash = md5($sql);
    	$result = $this->mc->get($hash);
    	$this->log(__FUNCTION__ , 'Query: '.$sql);
    	$this->log(__FUNCTION__ , 'Hash: '.$hash);
    	$this->log(__FUNCTION__ , 'Found: '.($result?'yes':'no'));
    	//if the get() is not "not found" or "success" throw error
    	if($this->mcdflag && ($ret = $this->mc->getResultCode()) != 0 && $ret != 16 )
    		throw new Exception('Memcached Get Error: '.$this->mc->getResultCode(),U_ERROR);
    	
    	//if there is no result, make one and cache it
    	if(!$result)
    	{
    		try{
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
                        throw new Exception('Memcached Set Error: '.$this->mc->getResultCode(),U_ERROR);
                        
                    $this->table_cache($hash, $sql);
                }
            }catch(Exception $e)
            {
                throw new Exception('Could not make and cache query',U_FATAL,$e);
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
    			$this->log(__FUNCTION__ ,'adding hash '.$hash.' to table '.$row->table);
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
			$this->log(__FUNCTION__ , 'Resetting hash '.$hash.' for table '.$row->table);
			$this->mc->delete($hash);
		}
    	
    }
    
	public function db_get_insert_id()
	{

		
		$id = $this->db->insert_id;
		$this->log(__FUNCTION__ , $id);
		if($this->db->errno)
			throw new Exception('SQL Error:<br/>'."Error #".$this->db->errno." - ".$this->db->error,U_FATAL);
		
		return $id;
	}

	public function query_stat()
	{
		return $this->query_num;
	}

    public function sanitize($input)
    {
        $clean_input = $this->db->real_escape_string($input);
        $this->log(__FUNCTION__ , 'clean: '.$clean_input);
        return $clean_input;
    }

    private function getPageTime()
    {
        $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                return $time;
    }

    private function log($func,$message,$level=U_DEBUG)
    {
        if($this->debug)
            $this->debug->log(__CLASS__ , $func,$message,$level);
    }

}

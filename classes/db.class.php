<?php

class DB
{
	private $debug = false;
	
	private $db_host;
	private $db_user;
	private $db_pass;
	private $db_name;
	private $memcached;
    
    private $db_link;
    
    private $query_num = 0;
    
	public function __construct($connectinfo,$conndebug=false,$connmemcached=null)
	{
		$this->db_host = $connectinfo['db_host'];
		$this->db_user = $connectinfo['db_user'];
		$this->db_pass = $connectinfo['db_pass'];
		$this->db_name = $connectinfo['db_name'];
		
		if($conndebug)
			$this->debugOn();
		
		$this->db_connect();
		
        $this->memcached = $connmemcached;
        
		$this->time_start = $this->getPageTime();
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
		
		return $res;
	}
	
    public function mc_query($query,$ttl)
    {
        
    
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

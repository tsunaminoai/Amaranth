<?php
define( 'DEBUG',0);
define( 'NOTICE',1);
define( 'WARNING',2);
define( 'ERROR', 3);
define( 'OFF', 4);

class debug extends Exception{

	private $level=4;
	private $logfile;
	
	public function __construct($loglevel=4)
	{
		$this->level = $loglevel;
		
		set_error_handler(array(&$this,'errorHandle'));
		set_exception_handler(array(&$this,'exHandle'));
		
	}
	
	public function setLogFile($logfile)
	{
		try
		{
			if(($this->logfile = fopen($logfile,'a')) === FALSE)
				throw new Exception('Cannot open logfile: "'.$logfile.'"');
		}
		catch(Exception $e){
			echo $e->getMessage();
			die();
		}
		
	}
	
	private function report($message)
	{
		if($this->logfile)
			fwrite($this->logfile,date('Ymd [H:s:u]').' :: '.$message) or die('Cannot write to file');
		else
			echo $message;
	}
	
	public function errorHandle($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
        	return;
    	}
    	    	
    	switch ($errno) {
		case E_USER_ERROR:
		case E_ERROR:
			$err += "<b>ERROR</b> [$errno] $errstr<br />\n";
			$err += "  Fatal error  @ $errfile:$errline";
			$err += ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			$err += "Aborting...<br />\n";
			$this->report($err);
			exit(1);
			break;
	
		case E_USER_WARNING:
		case E_WARNING:
			$err += "<b>WARNING</b> [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err);
			break;
		
		case E_USER_NOTICE:
		case E_NOTICE:
			$err += "<b>NOTICE</b> [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err);
			break;
	
		default:
			$err += "Unknown error type: [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err);
			break;
		}
	
		return true;
	}
	
	public function exHandle()
	{
	
	}
}

?>

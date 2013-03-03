<?php

class Debug{

	private $level=0;
	private $logfile;
	
	private function __construct()
	{
		
		$this->setLogFile();
		
		set_error_handler(array($this,'errorHandle'));
		set_exception_handler(array($this,'exHandle'));
		
	}
	
	public static function getDebugger()
	{
		static $debugger = null;
		if($debugger === null)
		{
			$debugger = new Debug();
		}
		return $debugger;
	}
	
	private function setLogFile()
	{
		$logfile = config_get('logging','logfile');
		$this->leve = config_get('logging','level');
		
		if(!$logfile)
			$logfile = '/dev/null';
		
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
	
    public function log($class,$func,$message,$level=U_DEBUG)
    {
        $pre='';
        switch($level)
        {
            case U_DEBUG:   $pre = '[DEBUG]';   break;
            case U_NOTICE:  $pre = '[NOTICE]';  break;
            case U_ERROR:   $pre = '[ERROR]';   break;
            case U_WARNING: $pre = '[WARNING]'; break;
            case U_FATAL:   $pre = '[FATAL]';   break;
            default:        $pre = '[UNKNOWN]'; break;
        }
        $this->report($pre.' '.$class.':'.$func."\t".$message."\n",$level);
    }
    
	private function report($message,$level)
	{
        if( $level >= $this->level)
        {
            if($this->logfile)
                fwrite($this->logfile,date('ymd [H:i:s]').' :: '.strip_tags($message)) or die('Cannot write to logfile');
            else
                echo '<pre>'.$message.'</pre>';
        }
	}
	
	public function errorHandle($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
        	return;
    	}
    	 
        $err = '';
    	switch ($errno) {
        case E_RECOVERABLE_ERROR:
		case E_USER_ERROR:
		case E_ERROR:
			$err .= "<b>ERROR</b> [$errno] $errstr<br />\n";
			$err .= "  Fatal error  @ $errfile:$errline";
			$err .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			$err .= "Aborting...<br />\n";
			$this->report($err,$errno);
			exit(1);
			break;
	
		case E_USER_WARNING:
		case E_WARNING:
			$err .= "<b>WARNING</b> [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err,$errno);
			break;
		
		case E_USER_NOTICE:
		case E_NOTICE:
			$err .= "<b>NOTICE</b> [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err,$errno);
			break;
	
		default:
			$err .= "Unknown error type: [$errno] $errstr @ $errfile:$errline<br />\n";
			$this->report($err,$errno);
			break;
		}
	
		return true;
	}
	
	public function exHandle($e)
	{
        $this->trace($e);
	}
    
    public function trace($e)
    {
        while ($e->getPrevious() != NULL) {$e = $e->getPrevious();}
        $err = '<b>ERROR</b> '.$e->getMessage();
        $err .= ' @ '.$e->getFile().':'.$e->getLine();
        $err .= "<br/>\n";
        $err .= "Stack Trace<br/>\n";
        $err .= $e->getTraceAsString();
        $this->report($err,$e->getCode(),$e->getCode());
        if($e->getCode() == U_FATAL)
            die('<pre>'.$e->getMessage().'<br/>Aborting...</pre>');
    }
}

?>

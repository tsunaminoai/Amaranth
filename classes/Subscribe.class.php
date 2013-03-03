<?php

class Subscribe extends User
{

	public function __construct()
	{
		parent::__construct();
		
		Action::addAction('processSubscribe',array($this,'processSubscribe'));
	}
	
	
	public function showForm()
	{
		echo '<form action="?action=processSubscribe" method="post">';
		echo '<input type="text" length="45" name="sub_username" id="sub_username" />';
		echo '<input type="text" length="45" name="sub_email" id="sub_umail" />';
		echo '<input type="password" length="45" name="sub_password" id="sub_password" />';
		echo '<input type="submit" name="sub_submit" id="sub_submit" />';
		echo '</form>';
	}

}

?>
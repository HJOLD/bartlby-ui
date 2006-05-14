<?
	chdir("../../");
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	include "extensions/SSH/SSH.class.php";
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$inv = new SSH();
	
	$inv->storeServer($_GET[id], $_GET[ssh_ip],$_GET[ssh_port], $_GET[ssh_user], $_GET[ssh_pass]);
	
?>
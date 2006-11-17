<?
	$Bartlby_CONF="/opt/bartlby-dev/etc/bartlby.cfg";
	if(file_exists("setup.php")) {
		include("setup.php");
		exit(1);	
	}
	$_GET=array_merge($_GET, $_POST);
?>

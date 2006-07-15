<?
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	include "extensions/ServerGroups/ServerGroups.class.php";
	
	
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$sg = new ServerGroups();
	
	$layout= new Layout();
	$layout->setTitle("Group overview");
	
	$layout->set_menu("Server Groups");
	$layout->Table("100%");
	
	$layout->TableEnd();
	$layout->display();
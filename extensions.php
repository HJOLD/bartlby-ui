<?
set_time_limit(0);
echo "<!---SMASH IE BUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFER--->";
flush();
function dnl($i) {
	return sprintf("%02d", $i);
}
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);


$layout= new Layout();
$layout->set_menu("core");
$layout->setTitle("Installed Extensions");
$layout->Table("100%");


	
	


$r=$btl->getExtensionsReturn("_About", $layout, true);
$layout->TableEnd();

$layout->display();


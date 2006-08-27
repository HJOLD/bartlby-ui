<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$optind=0;
$y=0;


$optind=0;
$plgs=bartlby_config($btl->CFG, "agent_plugin_dir");
$dh=opendir($plgs);
while ($file = readdir ($dh)) { 
   if ($file != "." && $file != "..") { 
   	clearstatcache();
   	if((preg_match("/\.exe$/i", $file)) || (is_executable($plgs . "/" . $file) && !is_dir($plgs . "/" . $file))) {
   			if(preg_match("/" . $_GET[search] . "/i", $file)) {
       			echo "<a href=\"javascript:setSearch('" . $file . "');\">$file</a>\n";
       			$y++;
       		}
       	}
   }
   if($y>20) {
		break;	
   } 
}
closedir($dh); 



/*
echo "<a href=\"javascript:setSearch('63');\">test1</a>\n";
*/	
?>

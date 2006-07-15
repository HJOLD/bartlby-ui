<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);
$servers=$btl->GetSVCMap();

//Search Servers
echo "<table>";
echo "<tr>";
echo "<td colspan=2>";
echo "<center><b>Servers</b></center>";
echo "</td></tr>";
$sfound=false;
while(list($k, $v) = @each($servers)) {
	
	if(preg_match("/" . $_GET[search] . "/i", $v[0][server_name])) {
		$_GET[server_id]=$v[0][server_id];
		echo "<tr><td><a href='server_detail.php?server_id=" . $v[0][server_id] . "'><font size=1>" . $v[0][server_name] . "</font></A>(<a href='services.php?server_id=" . $v[0][server_id] . "'><font size=1>Services</font></A>)</td><td>" . $btl->getserveroptions(1,1) . "</td></tr>";	
		$sfound=true;
	}
	
	
	
}	
if($sfound == false) {
	echo "<tr><td colspan=2><i>no server matched</i></td></tr>";	
}



echo "<tr>";
echo "<td colspan=2>";
echo "<center><b>Services</b></center>";
echo "</td></tr>";

reset($servers);
$svcfound=false;
while(list($k, $v) = @each($servers)) {
	
	
	
	for($x=0; $x<count($v); $x++) {
		if(preg_match("/" . $_GET[search] . "/i", $v[$x][service_name])) {
			echo "<tr><td><a href='service_detail.php?service_place=" . $v[$x][shm_place] . "'><font size=1>" . $v[$x][server_name] . "/" . $v[$x][service_name] . "</A></font></td><td>" . $btl->getServiceOptions($v[$x], $layout) . "</td>";	
			$svcfound=true;
		}
	}
	
	
}	
if($svcfound == false) {
	echo "<tr><td colspan=2><i>no service matched</i></td></tr>";
}
echo "</table>";
//Search Services	
	

//Search Workers
//Call n get return of Extensions
?>

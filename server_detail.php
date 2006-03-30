<?
function dnl($i) {
	return sprintf("%02d", $i);
}
function getGeoip($ip) {
	$fp=popen("geoiplookup $ip", "r");
	while(!feof($fp)) {
		$rmsg .= fgets($fp, 1024);	
	}
	$exi=pclose($fp);
	if($exi == 127) {
		return "(maybe you dont have 'geoiplookup' not installed or it is not in your PHP path)";
	} else {
		$a=explode(":",$rmsg);
		return $a[1];	
	}
		
}
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->set_menu("main");
$layout->setTitle("Services");
if(preg_match("/^XML:(.*):(.*)$/i", $_GET[server_id], $match)) {
	$defaults=$btl->remoteServerByID($match[1], $match[2]);
} else {
	$defaults=bartlby_get_server_by_id($btl->CFG, $_GET[server_id]);
}

$map=$btl->getSVCMap($btl->CFG, NULL, NULL);
$mode=bartlby_config("ui-extra.conf", "xml_remote_count");
if($mode) {
	$remote_xml=array();
	for($xml_X=1; $xml_X<=$mode; $xml_X++) {
			$xml_cur=bartlby_config("ui-extra.conf", "xml_remote[" . $xml_X . "]");
			$xml_cur_alias=bartlby_config("ui-extra.conf", "xml_alias[" . $xml_X . "]");
			if($xml_cur) {
				$xml_return = $btl->getRemoteStatus($xml_cur, $xml_cur_alias);
				$btl->appendXML_to_svc_map($xml_return[services], $xml_cur_alias, $map, $xml_X);	
			}
	}
	
	
		
}


$server_map=$map[$_GET[server_id]];
$layout->Table("100%");

if($btl->isServerUp($_GET[server_id], $map)) {
	$isup="<font color='green'>UP</font>";	
} else {
	$isup="<font color='red'>DOWN</font>";
}

$services_assigned="";

for($x=0; $x<count($server_map); $x++) {
	$services_assigned .= "<a href='service_detail.php?service_place=" . $server_map[$x][shm_place] . "'>" . $server_map[$x][service_name] . "</A><br>";
	
}


//echo $defaults[last_notify_send] . "<br>";

$info_box_title='Server Info';  
// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
$core_content = "<table  width='100%'>
	<tr>
		<td width=150 class='font2'>Name:</td>
		<td align=left >" . $defaults[server_name] . "</font></td> 
		<td>&nbsp;</td>     
	</tr>
	<tr>
		<td width=150 class='font2'>Status:</td>
		<td align=left >" . $isup . "</font></td> 
		<td>&nbsp;</td>     
	</tr>
	<tr>
		<td width=150 class='font2'>Ip:</td>
		<td align=left >" . $defaults[server_ip] . "/" . gethostbyname($defaults[server_ip]) . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>Port:</td>
		<td align=left >" . $defaults[server_port] . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>Icon:</td>
		<td align=left ><img src='server_icons/" . $defaults[server_icon] . "'></font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>Location:</td>
		<td align=left >" . getGeoip(gethostbyname($defaults[server_ip])) . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	
</table>";
$layout->push_outside($layout->create_box($info_box_title, $core_content));


$info_box_title='Services';  
// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
$core_content = "<table  width='100%'>
	<tr>
		<td width=150 class='font2'>Number of services:</td>
		<td>" . count($server_map) . "</td> 
	</tr>
	<tr>
		<td width=150 class='font2' valign=top>Services:</td>
		<td>" . $services_assigned . "</td> 
	</tr>
	
	
</table>";


$layout->push_outside($layout->create_box($info_box_title, $core_content));
	



$layout->TableEnd();

$layout->display();
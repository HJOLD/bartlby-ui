<?
function dnl($i) {
	return sprintf("%02d", $i);
}
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->setTitle("Actions");
$defaults=bartlby_get_service($btl->CFG, $_GET[service_place]);
$layout->Table("100%");


$svc_color=$btl->getColor($defaults[current_state]);
$svc_state=$btl->getState($defaults[current_state]);

if($defaults[service_type] == 1) {
	$svc_type="Active";
}

if($defaults[service_type] == 2) {
	$svc_type="Passive";
}

if($defaults[service_type] == 3) {
	$svc_type="Group";
}

if($defaults["notify_enabled"]==1) {
	$noti_en="true";
} else {
	$noti_en="false";
}
if($defaults["service_active"]==1) {
	$serv_en="true";
} else {
	$serv_en="false";
}
//echo $defaults[last_notify_send] . "<br>";

$info_box_title='Service Info';  
// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
$core_content = "<table  width='100%'>
	<tr>
		<td width=150 class='font2'>Server:</td>
		<td align=left >" . $defaults[server_name] . " ( IP: " . gethostbyname($defaults[client_ip]) . " Port: " . $defaults[client_port] . " )</font></td> 
		<td>&nbsp;</td>     
	</tr>
	<tr>
		<td width=150 class='font2'>Name:</td>
		<td align=left >" . $defaults[service_name] . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>ID:</td>
		<td align=left >" . $defaults[service_id] . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>Type:</td>
		<td align=left >" . $svc_type . "</font></td>  
		<td>&nbsp;</td>         
	</tr>
	<tr>
		<td width=150 class='font2'>Current State:</td>
		<td align=left><font color='$svc_color'>" . $svc_state . "</font></td> 
		<td>&nbsp;</td>          
	</tr>
	<tr>
		<td width=150 class='font2'>Last Check:</td>
		<td align=left >" . date("d.m.Y H:i:s", $defaults[last_check]) . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>approx. next Check:</td>
		<td align=left >" . date("d.m.Y H:i:s", $defaults[last_check]+$defaults[check_interval]) . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Check intervall:</td>
		<td align=left >" . $defaults[check_interval] . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Last Notify Send:</td>
		
		<td align=left >" . date("d.m.Y H:i:s", $defaults[last_notify_send]) . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Notify Enabled:</td>
		<td align=left >" . $noti_en . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Check Enabled:</td>
		<td align=left >" . $serv_en . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Check From:</td>
		<td align=left >" . dnl($defaults[hour_from]) . ":" . dnl($defaults[min_from]) . ':00' . "</font></td>
		<td>&nbsp;</td>           
	</tr>
	<tr>
		<td width=150 class='font2'>Check To:</td>
		<td align=left >" . dnl($defaults[hour_to]) . ":" . dnl($defaults[min_to]) . ':00' . "</font></td>
		<td>&nbsp;</td>           
	</tr>
		<tr>
		<td width=150 class='font2'>Flap count:</td>
		<td align=left >" . $defaults[flap_count] . "</font></td>
		<td>&nbsp;</td>           
	</tr>
</table>";
$layout->push_outside($layout->create_box($info_box_title, $core_content));

$info_box_title='Last Output';  
// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
$core_content = "<table  width='100%'>
	<tr>
		<td>" . $defaults[new_server_text] . "</td> 
	</tr>
	
	
</table>";


$layout->push_outside($layout->create_box($info_box_title, $core_content));

if($defaults[service_type] == 1){
	$info_box_title='Active Service';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$core_content = "<table  width='100%'>
		<tr>
			<td width=150 class='font2'>Plugin:</td>
			<td align=left >" . $defaults[plugin] . "</font></td>
			<td>&nbsp;</td>           
		</tr>
		<tr>
			<td width=150 class='font2'>Plugin Arguments:</td>
			<td align=left >" . $defaults[plugin_arguments] . "</font></td>
			<td>&nbsp;</td>           
		</tr>
		<tr>
			<td width=150 class='font2'>Plugin Timeout:</td>
			<td align=left >" . $defaults[service_check_timeout] . " Seconds</font></td>
			<td>&nbsp;</td>           
		</tr>
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
}
if($defaults[service_type] == 2){
	$info_box_title='Passive Service';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$core_content = "<table  width='100%'>
		<tr>
			<td width=150 class='font2'>Timeout:</td>
			<td align=left >" . $defaults[service_passive_timeout] . "</font></td>
			<td>&nbsp;</td>           
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
}
if($defaults[service_type] == 3){
	$info_box_title='Group Service';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$core_content = "<table  width='100%'>
		<tr>
			<td width=150 class='font2'>Check String:</td>
			<td align=left >" . $defaults[service_var] . "</font></td>
			<td>&nbsp;</td>           
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
}

//special_ui's
$special_counter=bartlby_config($btl->CFG, "special_addon_ui_" . $defaults[service_id] . "_cnt");
if($special_counter) {
	
	for($spc=1; $spc<=$special_counter; $spc++) {
		
		$spc_name=bartlby_config($btl->CFG, "special_addon_ui_" . $defaults[service_id] . "_[" . $spc ."]_name");
		//$layout->OUT .= "menu" . $defaults[service_id] . "[" . $spc_real . "]='" . str_replace("^", "=", bartlby_config($btl->CFG, "special_addon_ui_" . $defaults[service_id] . "_[" . $spc ."]")) . "';\n";
		$info_box_title="$spc_name";  
		// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
		$core_content = "<table  width='100%'>
			<tr>
				<td>" . str_replace("^", "=", bartlby_config($btl->CFG, "special_addon_ui_" . $defaults[service_id] . "_[" . $spc ."]")) . "</td>           
			</tr>
			
			
		</table>";
		
		$layout->push_outside($layout->create_box($info_box_title, $core_content));
	}
	
}
if($defaults[service_active] == 1) {
	$check = "<a title='Disable Checks for this Service' href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_service'><img src='images/enabled.gif'  border=0></A>";
} else {
	$check = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_service'><img src='images/diabled.gif' title='Enable  Checks for this Service' border=0></A>";
}
if($defaults[notify_enabled] == 1) {
	$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_notify'><img src='images/notrigger.gif' title='Disable Notifications for this Service' border=0></A>";
} else {
	$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_notify'><img src='images/trigger.gif' title='Enable Notifications for this Service' border=0></A>";
}
$comments  ="<a href='view_comments.php?service_id=" . $defaults[service_id] . "'><img src='images/icon_comments.gif' border=0></A>";
				
$layout->OUT .="$notifys $check <a href='logview.php?service_id=" . $defaults[service_id]. "' ><font size=1><img  src='images/icon_view.gif' border=0></A> $comments";
$layout->TableEnd();

$layout->display();
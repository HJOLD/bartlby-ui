<?
	include "layout.class.php";
	include "config.php";
	include "bartlby-ui.class.php";

	$layout= new Layout();
	$layout->MetaRefresh(240);
	$layout->Table("100%");
	
	$btl=new BartlbyUi($Bartlby_CONF);
	
	$map = $btl->GetSVCMap($_GET[service_state]);	
	
	$layout->DisplayHelp(array(0=>"INFO|A detailed list of all services bartlby is monitoring"));
	$layout->setTitle("Services");
	
	$display_serv=$_GET[server_id];
	if(!$display_serv) {
		while(list($k,$v) = each($map)) {
		//	$display_serv=$k;
			
			break;	
		}
		reset($map);	
	}
	
		
		while(list($k, $servs) = @each($map)) {
			$displayed_servers++;
			
			if($display_serv && $display_serv != $k) {
				continue;	
			}
			$curp = $_GET[$k ."site"] > 0 ? $_GET[$k ."site"] : 1;
			$perp=bartlby_config($btl->CFG, "services_per_page");
			$forward_link=$btl->create_pagelinks("services.php?expect_state=" . $_GET[expect_state] . "&server_id=" . $_GET[server_id], count($servs)-1, $perp, $curp,$k ."site");
			
			
			$cur_box_title=$servs[0][server_name] . " ( " . $servs[0][client_ip] . ":" . $servs[0][client_port] . " ) $forward_link"; //. "<a href='package_create.php?action=create_package&server_id="  . $servs[0][server_id] . "'><font size=1><img src='images/icon_work1.gif' border=0></a>";
			$cur_box_content = "<table class='service_table' cellpadding=2>";
			
			$d=0;
			$skip_em=($curp*$perp)-$perp;
			
			for($x=$skip_em; $x<count($servs); $x++) {
				
				
				if($d >= $perp) {
					break;	
				}
				if($_GET[expect_state] != "" && $servs[$x][current_state] != $_GET[expect_state]) {
					continue;	
				}
				$d++;
				/*
				echo "<script>var menu2558=new Array()
			menu2558[0]='<a href=\"http://www.javascriptkit.com\">JavaScript Kit</a>'
			menu2558[1]='<a href=\"http://www.freewarejava.com\">Freewarejava.com</a>'
			menu2558[2]='<a href=\"http://codingforums.com\">Coding Forums</a>'
			menu2558[3]='<a href=\"http://www.cssdrive.com\">CSS Drive</a>'

			//Contents for menu 2, and so on
			var menu2=new Array()
			menu2[0]='<a href=\"http://cnn.com\">CNN</a>'
			menu2[1]='<a href=\"http://msnbc.com\">MSNBC</a>'
			menu2[2]='<a href=\"http://news.bbc.co.uk\">BBC News</a>'
			</script>";*/
			
				$special_menu = "<a href='#' onClick=\"return dropdownmenu(this, event, menu" . $servs[$x][service_id] . ", '200px')\" onMouseout=\"delayhidemenu()\"><img src='images/icon_work1.gif' border=0></A>";
				$layout->OUT .= "<script>var menu" . $servs[$x][service_id] . "=new Array();</script>";
				$special_counter=bartlby_config($btl->CFG, "special_addon_ui_" . $servs[$x][service_id] . "_cnt");
				if($special_counter) {
					$layout->OUT .= "<script>";
					$fspc=0;
					for($spc=0; $spc<$special_counter; $spc++) {
						$spc_name=bartlby_config($btl->CFG, "special_addon_ui_" . $servs[$x][service_id] . "_[" . ($spc+1) ."]_name");
						$layout->OUT .= "menu" . $servs[$x][service_id] . "[" . $fspc . "]='<br>$spc_name<br>';\n";
						$layout->OUT .= "menu" . $servs[$x][service_id] . "[" . ($fspc+1) . "]='" . str_replace("^", "=", bartlby_config($btl->CFG, "special_addon_ui_" . $servs[$x][service_id] . "_[" . ($spc+1) ."]")) . "';\n";
						$fspc++;
						$fspc++;
					}
					$layout->OUT .= "</script>";
				} else {
						$special_menu="";
				}
				
				
				$displayed_services++;
				$svc_color=$btl->getColor($servs[$x][current_state]);
				$svc_state=$btl->getState($servs[$x][current_state]);
				$server_color="black";
				$SERVER="&nbsp;";
				$class="header1";
				if($x % 2 == 1) {
					$class="header1";	
				}
				
				if($servs[$x][service_active] == 1) {
					$check = "<a title='Disable Checks for this Service' href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=disable_service'><img src='images/enabled.gif'  border=0></A>";
				} else {
					$check = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=enable_service'><img src='images/diabled.gif' title='Enable  Checks for this Service' border=0></A>";
				}
				
				/* if($servs[$x][server_enabled] == 1) {
					$checkSRV = "<a href='poseidon_action.php?service_id=$servs[$x][service_id]&server_id=$servs[$x][server_id]&action=disable_server'><img src='enabled.gif' title='Disable Checks for this Host' border=0></A>";
				} else {
					$checkSRV = "<a href='poseidon_action.php?service_id=$servs[$x][service_id]&server_id=$servs[$x][server_id]&action=enable_server'><img src='diabled.gif' alt='Enable  Checks for this Host' border=0></A>";
				}
				*/
				
				
				
				if($servs[$x][notify_enabled] == 1) {
					$notifys = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=disable_notify'><img src='images/notrigger.gif' title='Disable Notifications for this Service' border=0></A>";
				} else {
					$notifys = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=enable_notify'><img src='images/trigger.gif' title='Enable Notifications for this Service' border=0></A>";
				}
				if($servs[$x][is_downtime] == 1) {
					
					$downtime_ico="<img src='images/icon_work.gif' title='Service is in downtime (" . date("d.m.Y H:i:s", $servs[$x][downtime_from])  . "-" . date("d.m.Y H:i:s", $servs[$x][downtime_to]) . "): " . $servs[$x][downtime_notice] . "'>";	
				} else {
					$downtime_ico="&nbsp;";
				}
				
				$comments  ="<a href='view_comments.php?service_id=" . $servs[$x][service_id] . "'><img src='images/icon_comments.gif' border=0></A>";
				//$comments .="<a href='view_comments.php?service_id=" . $servs[$x][server_id] . "'>add comments</A><br>";
				
				if($servs[$x][is_downtime] == 1) {
					$svc_state="Downtime";
					$svc_color="silver";	
				}
				$cur_box_content .= $layout->Tr(
					$layout->Td(
						Array(
							
							0=>array(
								"width"=>58,
								"align"=>"center",
								"show"=>"<b><a href='services.php?expect_state=" . $servs[$x][current_state] . "'><font style='font-size:9px;'>" . $svc_state . "</A></b>",
								'class'=>$svc_color . "_img"
							   ),
							1=>array(
								"width"=>80,
								"class"=>$class,
								"show"=>"<font size=1>" . date("d.m.y H:i:s", $servs[$x][last_check])
							   ),
							2=>array(
								"width"=>80,
								"class"=>$class,
								"show"=>"<font size=1>" .  date("d.m.y H:i:s", $servs[$x][last_check]+$servs[$x][check_interval])
							   ),						
							3=>array(
								"width"=>150,
								"class"=>"header1",
								"show"=>"<a href='service_detail.php?service_place=" . $servs[$x][shm_place] . "'><b>" . $servs[$x][service_name]  . "$working_on $flap_pic</b><br>" . "<br> $notifys $check <a href='logview.php?service_id=" . $servs[$x][service_id]. "' ><font size=1><img  src='images/icon_view.gif' border=0></A> $comments $special_menu $downtime_ico</font>"
							   ),
							4=>array(
								"width"=>450,
								"class"=>$class,
								"show"=>str_replace( "\\dbr", "<br>",nl2br($servs[$x][new_server_text]))
							   )
						)
					)
				, true);
			}
			$cur_box_content .= "</table>";
			$layout->push_outside($layout->create_box($cur_box_title, $cur_box_content));
	}
	$legend_content="<table class='nopad' width='100%'>
		<tr>
			
			<td width=15><img src='images/diabled.gif'></td>
			<td align=left class='font2'>Service Check is disabled</td>
			
			
			<td width=15><img src='images/notrigger.gif'></td>
			<td align=left class='font2'>Notifications are disabled</td>
			
			<td width=15><img src='images/icon_view.gif'></td>
			<td align=left class='font2'>View Logfile</td>
			
			
			
			
		</tr>
		<tr>
			<td width=15><img src='images/enabled.gif'></td>
			<td align=left class='font2'>Service Check is enabled</td>
			
			<td width=15><img src='images/trigger.gif'></td>
			<td align=left class='font2'>Notifications are enabled</td>
			
			<td width=15><img src='images/icon_comments.gif'></td>
			<td align=left class='font2'>Comments</td>
			
			
			
		</tr>
		
	</table>";
	
	$layout->push_outside($layout->create_box("Legend", $legend_content));
	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 6,
					'class'=>'header1',
					'show'=>"Matching Servers: $displayed_servers Matching Services: $displayed_services"
					)
			)
		)

	);	

	$layout->TableEnd();
	$layout->display();
	
?>
<?
	include "layout.class.php";
	include "config.php";
	include "bartlby-ui.class.php";

	$layout= new Layout();
	$layout->MetaRefresh();
	$layout->Table("100%");
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$map = $btl->GetSVCMap($_GET[service_state]);	
	
	$layout->DisplayHelp(array(0=>"INFO|A detailed list of all services bartlby is monitoring"));
	
	
	
	$layout->Tr(
			$layout->Td(
				Array(
					0=>array(
						
						'show'=>"Host",
						'class'=>"header"
					   ),
					1=>array(
						"show"=>"State",
						'class'=>"header"
					   ),
					2=>array(
						"class"=>"header",
						"show"=>"Last Check"
					   ),
					3=>array(
						"class"=>"header",
						"show"=>"~ Next Check"
					   ),
												
					4=>array(
						"class"=>"header",
						"show"=>"Service"
					   ),
					5=>array(
						
						"class"=>"header",
						"show"=>"Service Text"
					   )
				)
			)
		);
		while(list($k, $servs) = @each($map)) {
			$displayed_servers++;
			if($_GET[server_id] && $_GET[server_id] != $k) {
				continue;	
			}
			for($x=0; $x<count($servs); $x++) {
				$displayed_services++;
				$svc_color=$btl->getColor($servs[$x][current_state]);
				$svc_state=$btl->getState($servs[$x][current_state]);
				if($x == 0) {
					$server_color="header";
					
					$SERVER=$servs[$x][server_name] . "<br><a href='package_create.php?action=create_package&server_id="  . $servs[$x][server_id] . "'><font size=1><img src='images/icon_work1.png' border=0></a>";
				} else {
					$server_color="black";
					$SERVER="&nbsp;";
					
				}
				$class="header1";
				if($x % 2 == 1) {
					$class="header";	
				}
				
				if($servs[$x][service_active] == 1) {
					$check = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=disable_service'><img src='images/enabled.png' title='Disable Checks for this Service' border=0></A>";
				} else {
					$check = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=enable_service'><img src='images/diabled.png' title='Enable  Checks for this Service' border=0></A>";
				}
				
				/* if($servs[$x][server_enabled] == 1) {
					$checkSRV = "<a href='poseidon_action.php?service_id=$servs[$x][service_id]&server_id=$servs[$x][server_id]&action=disable_server'><img src='enabled.png' title='Disable Checks for this Host' border=0></A>";
				} else {
					$checkSRV = "<a href='poseidon_action.php?service_id=$servs[$x][service_id]&server_id=$servs[$x][server_id]&action=enable_server'><img src='diabled.png' alt='Enable  Checks for this Host' border=0></A>";
				}
				*/
				
				
				
				if($servs[$x][notify_enabled] == 1) {
					$notifys = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=disable_notify'><img src='images/notrigger.png' title='Disable Notifications for this Service' border=0></A>";
				} else {
					$notifys = "<a href='bartlby_action.php?service_id=" . $servs[$x][service_id] . "&server_id=" . $servs[$x][server_id] . "&action=enable_notify'><img src='images/trigger.png' title='Enable Notifications for this Service' border=0></A>";
				}
				
				
				$comments  ="<a href='view_comments.php?service_id=" . $servs[$x][service_id] . "'><img src='images/icon_comments.png' border=0></A><br>";
				//$comments .="<a href='view_comments.php?service_id=" . $servs[$x][server_id] . "'>add comments</A><br>";
				
				
				$layout->Tr(
					$layout->Td(
						Array(
							0=>array(
								"width"=>100,
								'show'=>$SERVER,
								'class'=>$server_color
							   ),
							1=>array(
								"width"=>100,
								"align"=>"center",
								"show"=>"<b><a href='services.php?service_state=" . $servs[$x][current_state] . "'>" . $svc_state . "</A></b>",
								'class'=>$svc_color
							   ),
							2=>array(
								"width"=>50,
								"class"=>$class,
								"show"=>"<font size=1>" . date("d.m.y H:i:s", $servs[$x][last_check])
							   ),
							3=>array(
								"width"=>50,
								"class"=>$class,
								"show"=>"<font size=1>" .  date("d.m.y H:i:s", $servs[$x][last_check]+$servs[$x][check_interval])
							   ),						
							4=>array(
								"width"=>100,
								"class"=>"header1",
								"show"=>"<b>" . $servs[$x][service_name]  . " $working_on $flap_pic</b><br>" . "<br> $notifys $check <a href='logview.php?service_id=" . $servs[$x][service_id]. "'><font size=1><img src='images/icon_view.png' border=0></A> $comments</font>"
							   ),
							5=>array(
								"width"=>300,
								"class"=>$class,
								"show"=>str_replace( "\\dbr", "<br>",nl2br($servs[$x][new_server_text]))
							   )
						)
					)
				);
			}
	}
	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 6,
					'class'=>'header',
					'show'=>"Matching Servers: $displayed_servers Matching Services: $displayed_services"
					)
			)
		)

	);	

	$layout->TableEnd();
	$layout->display();
	
?>
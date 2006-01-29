<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";

$act=$_GET[action];
if($_POST[action]) {
	$act=$_POST[action];	
}
$layout= new Layout();
$layout->setTitle("Bartlby Action ($act)");
$btl=new BartlbyUi($Bartlby_CONF);

$layout->Table("100%");

function dnl($i) {
	return sprintf("%02d", $i);
}





switch($act) {
	case 'delete_downtime':
		if($_GET[downtime_id]) {
			$rdt = bartlby_delete_downtime($btl->CFG, $_GET[downtime_id]);	
			$layout->OUT .= "<script>doReloadButton();</script>";
			
		} else {
			$act="missing_param";
		}
	break;
	case 'modify_downtime':
		if($_GET[downtime_notice] && $_GET[downtime_from] && $_GET[downtime_to] && $_GET[downtime_type]) {
			sscanf($_GET[downtime_from],"%d.%d.%d %d:%d", &$d, &$m, &$y, &$h, &$s);
			$dfrom=mktime($h, $s, 0, $m, $d,$y);
			sscanf($_GET[downtime_to],"%d.%d.%d %d:%d", &$d, &$m, &$y, &$h, &$s);
			$dto=mktime($h, $s, 0, $m, $d,$y);
			$clean_service=str_replace("s", "", $_GET[service_id]);
			
			$rdt=bartlby_modify_downtime($btl->CFG, $dfrom, $dto, $_GET[downtime_type], $_GET[downtime_notice], $clean_service, $_GET[downtime_id]);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {
			$act="missing_param";
		}
	break;
	case 'add_downtime':
		if($_GET[downtime_notice] && $_GET[downtime_from] && $_GET[downtime_to] && $_GET[downtime_type]) {
			$msg = $_GET[downtime_from];
			sscanf($_GET[downtime_from],"%d.%d.%d %d:%d", &$d, &$m, &$y, &$h, &$s);
			$dfrom=mktime($h, $s, 0, $m, $d,$y);
			sscanf($_GET[downtime_to],"%d.%d.%d %d:%d", &$d, &$m, &$y, &$h, &$s);
			$dto=mktime($h, $s, 0, $m, $d,$y);
			
			$clean_service=str_replace("s", "", $_GET[service_id]);
			
			$notice=$_GET[lappend] . " " . $_GET[downtime_notice];
			
			$rdt=bartlby_add_downtime($btl->CFG, $dfrom, $dto, $_GET[downtime_type], $notice, $clean_service);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
			
		}  else {
			$act="missing_param";
		}
	break;
	
	case 'edit_cfg':
		if($_POST["cfg_file"]) {
			
			$new_cfg=$_POST["cfg_file"];
			//Backup current
			$backup_name=$btl->CFG . ".bak_" . date("d-m-Y_H_i_s");
			$global_msg[backup_cfg]=$backup_name;
		
			copy($btl->CFG, $backup_name);
			$fp=fopen($btl->CFG, "w");
			$new_cfg=str_replace("\r", "", $new_cfg);
			fwrite($fp, $new_cfg);
			fclose($fp);
		}  else {
			$act="missing_param";
		}
		
	break;
	case 'stop':
		$base_dir=bartlby_config($btl->CFG, "basedir");
		if(!$base_dir) {
			$act="missing_param";
		} else {
			$cmd="export BARTLBY_HOME='$base_dir'; cd \$BARTLBY_HOME; ./bartlby.startup stop 2>&1";
		
			$fp=popen($cmd, "r");
			$msg=fgets($fp, 1024);
			pclose($fp);	
		}
	break;
	case 'delete_package':
		if($_GET[package_name]) {
			
			unlink("pkgs/" . $_GET[package_name]);
		} else {
			$act="missing_param";
		}
	break;
	case 'delete_package_ask':
		
		$global_msg[button] .= "<input type=button value='yes' onClick=\"document.location.href='bartlby_action.php?action=delete_package&package_name=" . $_GET[package_name] . "'\">";
	break;
	
	case 'add_comment':
		if($_GET[subject] && $_GET[comment]) {
			$global_msg=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);
			$fp=@fopen("comments/" . (int)$_GET[service_id], "a+");
			if(!$fp) {
				$act="missing_param";	
			} else {
				fwrite($fp, $btl->user . "|" . time() . "|" . str_replace("\n", " ", nl2br($_GET[comment])) . "|" . str_replace("\n", " ", nl2br($_GET[subject])) . "|" . implode(",", $_GET[notify]). "\n");
				fclose($fp);
				
			}
		} else {
			$act="missing_param";
		}
		
	
	break;
	case 'uninstall_package':
		$global_msg["package"] = "Removing package '$_GET[package_name]' from Server:  $_GET[server_id]<br>";
		$fp=@fopen("pkgs/" . $_GET[package_name], "r");
		if($fp) {
			while(!feof($fp)) {
				$bf .= fgets($fp, 1024);	
			}
			$re=unserialize($bf);
			fclose($fp);
			for($y=0; $y<$btl->info[services]; $y++) {
				$svc=bartlby_get_service($btl->CFG, $y);
				if($svc[server_id] == $_GET[server_id]) {
					for($x=0; $x<count($re); $x++) {
						//echo "if($svc[service_name] == " . $re[$x][service_name] . " && $svc[plugin] == " . $re[$x][plugin] . " && $svc[plugin_arguments] == " . $re[$x][plugin_arguments] . " && $svc[check_intervall] ==  $re[$x][check_interval] && $svc[service_type] == $re[$x][service_type] && $svc[hour_from] == $re[$x][hour_from] && $svc[hour_to] == $re[$x][hour_to] && $svc[min_from] == $re[$x][min_from] && $svc[min_to] == $re[$x][min_to]) {<br>";
						if($svc[service_name] == $re[$x][service_name] && $svc[plugin] == $re[$x][plugin] && $svc[plugin_arguments] == $re[$x][plugin_arguments] && $svc[check_interval] ==  $re[$x][check_interval] && $svc[service_type] == $re[$x][service_type] && $svc[hour_from] == $re[$x][hour_from] && $svc[hour_to] == $re[$x][hour_to] && $svc[min_from] == $re[$x][min_from] && $svc[min_to] == $re[$x][min_to]) {
							
							$global_msg["package"] .= "Removing Service: <b>" . $re[$x][service_name] . "</b><br>";	
							$global_msg["package"] .= str_repeat("&nbsp;", 20) . "Plugin:" . $re[$x][plugin] . "/'" . $re[$x][plugin_arguments] . " '<br>";	
							$global_msg["package"] .= str_repeat("&nbsp;", 20) . "Time: $tfrom - $tto / " . $re[$x][check_interval] . "<br>";	
							$global_msg["package"] .= str_repeat("&nbsp;", 20) . "Service Type: " . $re[$x][service_type] . "<br>";
							bartlby_delete_service($btl->CFG, $svc[service_id]);
							$found++;
						}
					}
				}
			}
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {
			$global_msg["package"] = "fopen failed()!!<br>";
			$act="missing_param";	
		}		
	break;
	case 'install_package':
		if($_GET[package_name] && $_GET[server_id]) {
			$global_msg["package"] = $btl->installPackage($_GET[package_name], $_GET[server_id]);
		}  else {
			$act="missing_param";
		}
		
	break;
	case 'create_package':
		$global_msg[pkg_services]="";
		$pkg=array();
		if($_GET[services]) {
			//$msg = "Creating package: " . $_GET[package_name] . "<br>";
			for($x=0; $x<$btl->info[services]; $x++) {
				$svc=bartlby_get_service($btl->CFG, $x);
				if(@in_array($svc[service_id], $_GET[services])) {
					$global_msg[pkg_services] .="<li>" . $svc[server_name] . ":" . $svc[client_port] . "/" . $svc[service_name];
					array_push($pkg, $svc);
				}
				
			}
			$save=serialize($pkg);
			$fp=@fopen("pkgs/" . $_GET[package_name], "w");
			if($fp) {
				fwrite($fp, $save);
				fclose($fp);
			} else {
				$global_msg[pkg_services] = "save failed";	
			}
		} else {                                     
		 	$act="missing_param";
		 }                            
	break;
	
	case 'disable_service':
	case 'enable_service':
		if($_GET[service_id]) {
			$global_msg=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);
			$idx=$btl->findSHMPlace($_GET[service_id]);
			
			$cur=bartlbe_toggle_service_active($btl->CFG, $idx);
			
		} else {                                     
		 	$act="missing_param";
		}     
		
	break;
	case 'disable_notify':
	case 'enable_notify':
		if($_GET[service_id]) {
			$global_msg=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);
			$idx=$btl->findSHMPlace($_GET[service_id]);
			
			$cur=bartlbe_toggle_service_notify($btl->CFG, $idx);
			
		}else {                                     
		 	$act="missing_param";
		 }     
	break;
	
	case 'reload':
		bartlby_reload($btl->CFG);
		while(1) {
			$x++;
			$i = @bartlby_get_info($btl->CFG);
			flush();
			
			if($i[do_reload] == 0) {
				$msg = "Done";
				//$layout->OUT .= "<script>doReloadButton();</script>";
				break;	
			}
		}
	break;
	case 'delete_worker':
		if($_GET[worker_id]) {
			$global_msg=bartlby_get_worker_by_id($btl->CFG, $_GET[worker_id]);
			$d=bartlby_delete_worker($btl->CFG, $_GET[worker_id]);
			
			$layout->OUT .= "<script>doReloadButton();</script>";

		} else {                                     
		 	$act="missing_param";
		 }     
	break;
	case 'modify_worker':
		if($_GET[worker_id] && $_GET[worker_name]) {
			for($x=0;$x<count($_GET[worker_services]); $x++) {
				$svcstr .="" . $_GET[worker_services][$x] . "|";	
			}
			if($svcstr != "") {
				$svcstr = "|"  . $svcstr;
			}
			for($x=0;$x<count($_GET[notify]); $x++) {
				$notifystr .="" . $_GET[notify][$x] . "|";	
			}
			if($notifystr != "") {
				$notifystr = "|" . $notifystr;
			}
			$triggerstr="";
			for($x=0;$x<count($_GET[worker_triggers]); $x++) {
				$triggerstr .="" . $_GET[worker_triggers][$x] . "|";	
			}
			if($triggerstr != "") {
				$triggerstr = "|" . $triggerstr;
			}
			
			
			$add=bartlby_modify_worker($btl->CFG,$_GET[worker_id],  $_GET[worker_mail], $_GET[worker_icq], $svcstr, $notifystr, $_GET[worker_active], $_GET[worker_name], $_GET[worker_password], $triggerstr);
			$layout->OUT .= "<script>doReloadButton();</script>";

		} else {                                     
		 	$act="missing_param";
		}     
	break;
	case 'add_worker':
	
		if($_GET[worker_name] && $_GET[worker_mail]) {
			$msg = "wa:" .  $_GET[worker_active] . "\n";
			
			for($x=0;$x<count($_GET[worker_services]); $x++) {
				$svcstr .="" . $_GET[worker_services][$x] . "|";	
			}
			if($svcstr != "") {
				$svcstr = "|"  . $svcstr;
			}
			
			for($x=0;$x<count($_GET[notify]); $x++) {
				$notifystr .="" . $_GET[notify][$x] . "|";	
			}
			if($notifystr != "") {
				$notifystr = "|" . $notifystr;
			}
			$triggerstr="";
			for($x=0;$x<count($_GET[worker_triggers]); $x++) {
				$triggerstr .="" . $_GET[worker_triggers][$x] . "|";	
			}
			if($triggerstr != "") {
				$triggerstr = "|" . $triggerstr;
			}
			
			
			$add=bartlby_add_worker($btl->CFG, $_GET[worker_mail], $_GET[worker_icq], $svcstr, $notifystr, $_GET[worker_active], $_GET[worker_name], $_GET[worker_password], $triggerstr);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
			
		} else {                                     
		 	$act="missing_param";
		}     
	break;
	case 'delete_service':
		if($_GET[service_id]) {
			$global_msg=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);
			$del = bartlby_delete_service($btl->CFG, $_GET[service_id]);
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {                                     
		 	$act="missing_param";
		 }     
	break;
	case 'modify_service':
	
		if($_GET[service_id] != "" && $_GET[service_id] && $_GET[service_server] && $_GET[service_type] &&  $_GET[service_name] &&  $_GET[service_time_from] &&  $_GET[service_time_to] && $_GET[service_interval]) {
			//echo "$ads=bartlby_modify_service($btl->CFG, $_GET[service_id] , $_GET[service_server], $_GET[service_plugin],$_GET[service_name],$_GET[service_args],1, dnl(substr($_GET[service_time_from], 0, 2)), dnl(substr($_GET[service_time_to], 0, 2)), dnl(substr($_GET[service_time_from], 3, 2)), dnl(substr($_GET[service_time_to], 3, 2)),$_GET[service_interval],$_GET[service_type],$_GET[service_var], $_GET[service_passive_timeout], $_GET[service_check_timeout]);";
			$ads=bartlby_modify_service($btl->CFG, $_GET[service_id] , $_GET[service_server], $_GET[service_plugin],$_GET[service_name],$_GET[service_args],1, dnl(substr($_GET[service_time_from], 0, 2)), dnl(substr($_GET[service_time_to], 0, 2)), dnl(substr($_GET[service_time_from], 3, 2)), dnl(substr($_GET[service_time_to], 3, 2)),$_GET[service_interval],$_GET[service_type],$_GET[service_var], $_GET[service_passive_timeout], $_GET[service_check_timeout]);
			$global_msg=bartlby_get_server_by_id($btl->CFG, $_GET[service_server]);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {                                     
		 	$act="missing_param";
		}     
	break;
	case 'add_service': 
		if($_GET[service_server] && $_GET[service_type] &&  $_GET[service_name] &&  $_GET[service_time_from] &&  $_GET[service_time_to] && $_GET[service_interval]) {
						//&bartlby_config, &server_id, &plugin,&service_name,&plugin_arguments,&notify_enabled,&hour_from,&hour_to,
						//&min_from,
						//&min_to,&check_interva	l, &service_type,&service_var,&service_passive_timeout
			
			$ads=bartlby_add_service($btl->CFG, $_GET[service_server], $_GET[service_plugin],$_GET[service_name],$_GET[service_args],1, substr($_GET[service_time_from], 0, 2), substr($_GET[service_time_to], 0, 2), substr($_GET[service_time_from], 3, 2), substr($_GET[service_time_to], 3, 2),$_GET[service_interval],$_GET[service_type],$_GET[service_var], $_GET[service_passive_timeout], $_GET[service_check_timeout]);
			$global_msg=bartlby_get_server_by_id($btl->CFG, $_GET[service_server]);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {                                     
		 	$act="missing_param";
		}     
	break;
	
	case 'delete_server':
		if($_GET[server_id]) {
			$global_msg=bartlby_get_server_by_id($btl->CFG, $_GET[server_id]);
			
			$s = bartlby_delete_server($btl->CFG, $_GET[server_id]);
			
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {                                     
		 	$act="missing_param";
		}     
	break;
	case 'modify_server':
		if($_GET[server_id] && $_GET[server_name] && $_GET[server_port] && $_GET[server_ip] && $_GET[server_icon]) {
				$mod_server=bartlby_modify_server($btl->CFG, $_GET[server_id], $_GET[server_name], $_GET[server_ip], $_GET[server_port], $_GET[server_icon]);
				
				$defaults=bartlby_get_server_by_id($btl->CFG, $_GET[server_id]);
				$layout->DisplayHelp(array(0=>"CRIT|You should restart bartlby for applieng changes "));
				$layout->OUT .= "<script>doReloadButton();</script>";
		} else {                                     
			$act="missing_param";
		}     
	break;
	case 'add_server':
			if($_GET[server_name] && $_GET[server_port] && $_GET[server_ip] && $_GET[server_icon]) {
				
				$add_server=bartlby_add_server($btl->CFG, $_GET[server_name], $_GET[server_ip], $_GET[server_port], $_GET[server_icon]);
				
				$global_msg["package"]="";
				$global_msg["init_service"]="";
				
				
				if($_GET[package_name] != "") {
					$global_msg["package"].= "<br>" . $btl->installPackage($_GET[package_name], $add_server);	
				} else {
					$add_service=bartlby_add_service($btl->CFG, $add_server, "INIT", "Initial Check", "-h", 0, 0,24,0,59,2000,1,"",200, 20);
					$global_msg["init_service"]="<li>Init";
				}
				
				$layout->OUT .= "<script>doReloadButton();</script>";
			} else {                                     
		 		$act="missing_param";
			}     
	break;
	
	default:
		$msg="Action not implemented ($act)";
		
	break;
		
}
$f=$act;

$msg=$btl->finScreen($f);
$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>$msg
					)
			)
		)

);


$layout->TableEnd();
$layout->display();
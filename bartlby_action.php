<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";


$layout= new Layout();
$btl=new BartlbyUi($Bartlby_CONF);

$layout->Table("100%");

function dnl($i) {
	return sprintf("%02d", $i);
}

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Bartlby action'
					)
			)
		)

);



switch($_GET[action]) {
	case 'disable_service':
	case 'enable_service':
		$idx=$btl->findSHMPlace($_GET[service_id]);
		$msg = $idx;
		$cur=bartlbe_toggle_service_active($btl->CFG, $idx);
		$msg = "Service is: " . $cur;
	break;
	case 'disable_notify':
	case 'enable_notify':
		$idx=$btl->findSHMPlace($_GET[service_id]);
		$msg = $idx;
		$cur=bartlbe_toggle_service_notify($btl->CFG, $idx);
		$msg = "Service is: " . $cur;
	break;
	
	case 'reload':
		bartlby_reload($btl->CFG);
		while(1) {
			$x++;
			$i = @bartlby_get_info($btl->CFG);
			flush();
			
			if($i[do_reload] == 0) {
				$msg = "Done";
				echo "<script>parent.l.document.location.href='nav.php'</script>";
				break;	
			}
		}
	break;
	case 'delete_worker':
		if($_GET[worker_id]) {
			$d=bartlby_delete_worker($btl->CFG, $_GET[worker_id]);
			$msg = "Deleted: $d\n";
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";

		} else {
			$msg = "Missing Param";	
		}
	break;
	case 'modify_worker':
		if($_GET[worker_id] && $_GET[worker_name]) {
			$msg = "wa:" .  $_GET[worker_active] . "\n";
			$add=bartlby_modify_worker($btl->CFG,$_GET[worker_id],  $_GET[worker_mail], $_GET[worker_icq], $_GET[worker_services], $_GET[worker_notifys], $_GET[worker_active], $_GET[worker_name], $_GET[worker_password]);
			$msg .= "Mod: " . $add;
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";

		} else {
			$msg = "Missing Param";	
		}
	break;
	case 'add_worker':
	
		if($_GET[worker_name] && $_GET[worker_mail]) {
			$msg = "wa:" .  $_GET[worker_active] . "\n";
			$add=bartlby_add_worker($btl->CFG, $_GET[worker_mail], $_GET[worker_icq], $_GET[worker_services], $_GET[worker_notifys], $_GET[worker_active], $_GET[worker_name], $_GET[worker_password]);
			$msg .= "ADD: " . $add;
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
		} else {
			$msg = "Missing Parameter";	
		}
	break;
	case 'delete_service':
		$del = bartlby_delete_service($btl->CFG, $_GET[service_id]);
		$msg .= "Del:" . $del  . "<br>";
		echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
	break;
	case 'modify_service':
		if($_GET[service_id] && $_GET[service_server] && $_GET[service_type] &&  $_GET[service_name] &&  $_GET[service_time_from] &&  $_GET[service_time_to] && $_GET[service_interval]) {
			$ads=bartlby_modify_service($btl->CFG, $_GET[service_id] , $_GET[service_server], $_GET[service_plugin],$_GET[service_name],$_GET[service_args],1, dnl(substr($_GET[service_time_from], 0, 2)), dnl(substr($_GET[service_time_to], 0, 2)), dnl(substr($_GET[service_time_from], 3, 2)), dnl(substr($_GET[service_time_to], 3, 2)),$_GET[service_interval],$_GET[service_type],$_GET[service_var], $_GET[service_passive_timeout]);
			
			$msg .=" Updated :" . $ads . "\n";
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
		} else {
			$msg = "Missing Parameter";	
		}
	break;
	case 'add_service': 
		if($_GET[service_server] && $_GET[service_type] &&  $_GET[service_name] &&  $_GET[service_time_from] &&  $_GET[service_time_to] && $_GET[service_interval]) {
						//&bartlby_config, &server_id, &plugin,&service_name,&plugin_arguments,&notify_enabled,&hour_from,&hour_to,
						//&min_from,
						//&min_to,&check_interva	l, &service_type,&service_var,&service_passive_timeout
			$msg ="Server: " . $_GET[service_server];
			$ads=bartlby_add_service($btl->CFG, $_GET[service_server], $_GET[service_plugin],$_GET[service_name],$_GET[service_args],1, substr($_GET[service_time_from], 0, 2), substr($_GET[service_time_to], 0, 2), substr($_GET[service_time_from], 3, 2), substr($_GET[service_time_to], 3, 2),$_GET[service_interval],$_GET[service_type],$_GET[service_var], $_GET[service_passive_timeout]);
			$msg .= "Added ($ads)<br>";
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
		} else {
			$msg = "Missing Parameter";	
		}
	break;
	
	case 'delete_server':
		if($_GET[server_id]) {
			$s = bartlby_delete_server($btl->CFG, $_GET[server_id]);
			$msg .= "Deleted: " . $s . "<br>";
			$layout->DisplayHelp(array(0=>"CRIT|You should restart bartlby for applieng changes "));	
			echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
		}
	break;
	case 'modify_server':
		if($_GET[server_id] && $_GET[server_name] && $_GET[server_port] && $_GET[server_ip]) {
				$mod_server=bartlby_modify_server($btl->CFG, $_GET[server_id], $_GET[server_name], $_GET[server_ip], $_GET[server_port]);
				$msg .= "Modified: " . $mod_server . " --> " .   $_GET[server_id] . "<br>";
				$defaults=bartlby_get_server_by_id($btl->CFG, $_GET[server_id]);
				$layout->DisplayHelp(array(0=>"CRIT|You should restart bartlby for applieng changes "));
				echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
			} else {
				$msg = "Missing Parameter";	
			}
	break;
	case 'add_server':
			if($_GET[server_name] && $_GET[server_port] && $_GET[server_ip]) {
				$add_server=bartlby_add_server($btl->CFG, $_GET[server_name], $_GET[server_ip], $_GET[server_port]);
				$msg .= "Added Server (" . $_GET[server_name] . ") got ID: $add_server<br>";
				//&bartlby_config, &server_id, &plugin,&service_name,&plugin_arguments,&notify_enabled,&hour_from,&hour_to,&min_from,
				//&min_to,&check_interval, &service_type,&service_var,&service_passive_timeout
				$add_service=bartlby_add_service($btl->CFG, $add_server, "INIT", "Initial Check", "-h", 0, 0,24,0,59,2000,1,"",200);
				$msg .=" Registered Service (INIT) -> ID: " . $add_service;
				$layout->DisplayHelp(array(0=>"CRIT|You should restart bartlby for applieng changes "));
				echo "<script>parent.l.document.location.href='nav.php?r=1'</script>";
			} else {
				$msg = "Missing Parameter";	
			}
	break;
	
	default:
		$msg="Action not implemented ($_GET[action])";
		
	break;
		
}

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
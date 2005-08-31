<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";


$layout= new Layout();
$btl=new BartlbyUi($Bartlby_CONF);

$layout->Table("100%");

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
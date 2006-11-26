<?
$do_not_merge_post_get=true;
include "layout.class.php";
include "bartlby-ui.class.php";
include "config.php";

require_once ("xajax/xajax.inc.php");





$xajax = new xajax("formchecker.php");
$xajax->registerFunction("AddModifyService");


$xajax->processRequests();



function AddModifyService($aFormValues) {
	global $_GET, $_POST;
	
	$av = $aFormValues;
	
	$res = new xajaxResponse();
	
	
	
	$al="";
	
	if(!bartlbize_field($av[service_name]))
		$al .= "Service name nasty!!\n";
	
	if(!bartlbize_int($av[service_interval]))
		$al .= "Service Interval name nasty!!\n";
	
	if(!bartlbize_int($av[service_retain]))
		$al .= "Service retain failure\n";

	if(!bartlbize_int($av[flap_seconds]))
		$al .= "Service flap seconds failure\n";
	
	if(!bartlbize_date($av[service_time_from]))
		$al .= "service time from!\n";
	
	if(!bartlbize_date($av[service_time_to]))
		$al .= "service time to!\n";
	
	switch($av[service_type]) {
		case 1:
		case 6:
		case 7:
		case 8:
		case 4:
		case 2:
			if(!bartlbize_int($av[service_check_timeout]))
				$al .= "Service check timeout failure\n";
			if(!bartlbize_field($av[service_plugin]))
				$al .= "Service plugin failure\n";
			if(!bartlbize_field($av[service_args], true))
				$al .= "plugin argumentsfailure\n";
			if($av[service_type] == 2) {
				if(!bartlbize_int($av[service_passive_timeout]))
					$al .= "Service passive timeout failure\n";
			}	
			
		
				
		break;
		case 3:
			if(!bartlbize_field($av[service_var]))
				$al .= "Group failure\n";
		break;	
		case 5:
			if(!bartlbize_field($av[service_snmp_community]))
				$al .= "service_snmp_community failure\n";
				
			
			
			if(!bartlbize_field($av[service_snmp_objid]))
				$al .= "service_snmp_objid failure\n";
			
			if(!bartlbize_int($av[service_snmp_warning]))
					$al .= "service_snmp_warning timeout failure\n";
			if(!bartlbize_int($av[service_snmp_critical]))
					$al .= "service_snmp_critical timeout failure\n";
					
		break;
		
	
		
	}
	
	
	if($al != "")  {
		$res->AddAlert("Form errors:\n" . $al);
	} else {
		$res->addScript("document.fm1.submit()");
	}
	
	return $res;	
}
function bartlbize_date($v) {
	if($v == "") {
		return false;
	}
	if(!preg_match("/[0-9].+:[0-9].+:[0-9].+/i", $v)) {
		
		return false;	
	}
	return true;
	
}
function bartlbize_int($v) {
	if($v == "") {
		return false;
	}
	if(!preg_match("/[0-9]/i", $v)) {
		return false;	
	}
	return true;
	
}


function bartlbize_field($v, $n=false) {
	if(!$n) {
		if($v == "") {
			return false;
		}
	}
	if(preg_match("/'/i", $v)) {
		return false;	
	}
	return true;
	
}

?>
<?
function dnl($i) {
	return sprintf("%02d", $i);
}
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	$btl=new BartlbyUi($Bartlby_CONF,true);
	$lib=bartlby_lib_info($btl->CFG);
	$info=$btl->info;
	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
	
	$map=$btl->GetSVCMap();
	echo "<bartlby version=\"" . $btl->getRelease() . "\" startup=\"" . $btl->info[startup_time] . "\" services=\"" . $info[services] . "\" workers=\"" . $info[workers] . "\">\n";
	while(list($id, $services) = each($map)) {
		
		echo "<server id=\"$id\" name=\"" . $services[0][server_name] . "\" client_ip=\"" . $services[0][client_ip] . "\" client_port=\"" . $services[0][client_port] . "\">\n";
		
		for($x=0; $x<count($services); $x++) {
			$ser=$services[$x];
			echo "<service id=\"" . $ser[service_id] . "\">\n";	
				echo "<name><![CDATA[\n" . $ser[service_name] . "\n]]></name>\n";	
				echo "<current_state>" . $ser[current_state] . "</current_state>\n";	
				echo "<last_check>" . $ser[last_check] . "</last_check>\n";	
				echo "<intervall>" . $ser[check_interval] . "</intervall>\n";	
				echo "<from>" . dnl($ser[hour_from]) . ":" . dnl($ser[min_from]) . ':00' . "</from>\n";	
				echo "<to>" . dnl($ser[hour_to]) . ":" . dnl($ser[min_to]) . ':00' . "</to>\n";	
				echo "<type>" . $ser[service_type] . "</type>\n";	
				echo "<output><![CDATA[\n" . $ser[new_server_text] . "\n]]></output>\n";	
				
			echo "</service>\n";
		}
		echo "</server>\n";
			
	}
	echo "</bartlby>\n";

?>
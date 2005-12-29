<?
function dnl($i) {
	return sprintf("%02d", $i);
}
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->setTitle("Bartlby Core Performance");
$layout->Table("100%");

//Check if profiling is enabled
$core_perf = bartlby_config($btl->CFG, "core_performance");
if($core_perf) {
	$byte_count=0;
	$raw = @file($core_perf);
	
	$check_max=0;
	$check_avg=0;
	$check_count=0;
	$check_sum=0;
	$check_plg_max="";
	
	$round_max=0;
	$round_avg=0;
	$round_count=0;
	$round_sum=0;
	
	
	
	
	
	while(list($nr, $line) = @each($raw)) {
		$byte_count += strlen($line);
		$s_info=explode("\t\t", $line);
		$type=$s_info[6];
		$ms=$s_info[5];
		$plugin=$s_info[4];
		
		$server=$s_info[2];
		$service=$server . "/" . $s_info[3];
		
		
		
		
		switch($type) {
			case 1:
				//SVC_CHECK_TIME
				if($check_max < $ms) {
					$check_max=$ms;
					$check_plg_max=$plugin;
				}
				
				$check_sum += $ms;
				
				$check_count++;
				//Plugin Table
				if(!is_array($plugin_table[$plugin])) {
					$plugin_table[$plugin]=Array();
				}
				array_push($plugin_table[$plugin], $ms);
				
				//Service Table
				if(!is_array($service_table[$service])) {
					$service_table[$service]=Array();
				}
				array_push($service_table[$service], $ms);
				
				//Server Table
				if(!is_array($server_table[$server])) {
					$server_table[$server]=Array();
				}
				array_push($server_table[$server], $ms);
				
					
				
			break;
			case 2:
				//ROUND_TIME
				if($round_max < $ms) {
					$round_max=$ms;
				}
				
				$round_sum += $ms;
				$round_count++;
				
				
			break;
				
		}	
		
	}	
	
	$check_avg=round($check_sum / $check_count,2);
	$round_avg=round($round_sum / $round_count,2);
	
	
	//Make top 10 table plugins
	//Table K1 == AVG == K2 VALUE == K3 MAX
	$plugins_sorted=sort_table($plugin_table);
	$server_sorted=sort_table($server_table);
	$service_sorted=sort_table($service_table);
	$plugin_html=make_html($plugins_sorted);
	$service_html=make_html($service_sorted);
	$server_html=make_html($server_sorted);
	
	$info_box_title="Check Time:";  
	$core_content = "<table  width='100%'>
		<tr>
			<td width=150 valign=top class='font2'>Max:</td>
			<td>$check_max ms</td>
		</tr>
		<tr>
			<td width=150 valign=top class='font2'>Average:</td>
			<td>$check_avg ms</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	
	$info_box_title="Round Time:";  
	$core_content = "<table  width='100%'>
		<tr>
			<td width=150 valign=top class='font2'>Max:</td>
			<td>$round_max ms</td>
		</tr>
		<tr>
			<td width=150 valign=top class='font2'>Average:</td>
			<td>$round_avg ms</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	$info_box_title="Plugins:";  
	$core_content = "<table  width='100%'>
		<tr>
			<td valign=top width=150 valign=top class='font2'>Slowest:</td>
			<td>$check_plg_max</td>
		</tr>
		<tr>
			<td width=150 valign=top class='font2'>Average slowest 10:</td>
			<td>
				
					$plugin_html
				
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	
	
	$info_box_title="Services:";  
	$core_content = "<table  width='100%'>
		
		<tr>
			<td width=150 valign=top class='font2'>Average slowest 10:</td>
			<td>
				
					$service_html
				
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	
	
	
	$info_box_title="Servers:";  
	$core_content = "<table  width='100%'>
		
		<tr>
			<td width=150 valign=top class='font2'>Average slowest 10:</td>
			<td>
				
					$server_html
				
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	

	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'show'=>"Slowest plugin:"
					),
				1=>$check_plg_max
			)
		)

	);
	
	
	
	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>"KBytes looked at:"  . round($byte_count/1024,2)
					)
			)
		)

	);	
} else {
	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>'Looks you dont have enabled core performance tracking (set core_performance= to a regular file on disk in config file)'
					)
			)
		)

	);	
}

$layout->TableEnd();

$layout->display();

function sort_table($plugin_table) {
	while(list($k, $v) = each($plugin_table)) {
		
		$max=0;
		$sum=0;
		$cnt=0;
		for($x=0; $x<count($v); $x++) {
			if($v[$x] > $max) {
				$max=$v[$x];	
			}
			$sum += $v[$x];
			$cnt++;
		}
		$avg=round($sum/$cnt, 2);
		
		$plugins_sortable[$avg][$k][$max] = 1;
	}
	 krsort($plugins_sortable);	
	return $plugins_sortable;
	
}

function make_html($info=array()) {
	$have=0;
	$out = "<table>";
	$out .= "<tr>";
	$out .= "<td class='font2'>&nbsp;</td>";	
	$out .= "<td class='font2'>Average</td>";
	$out .= "<td class='font2'>Maximum</td>";
	$out .= "</tr>";
	while(list($average, $d) = each( $info )) {
		while(list($plugin, $d1) = each($d)) {
			while(list($max, $d2) = each($d1)) {
				$out .= "<tr>";
				$out .= "<td>$plugin</td>";	
				$out .= "<td>$average ms</td>";
				$out .= "<td>$max ms</td>";
				$out .= "</tr>";
				$have++;
				
				if($have == 10) {
					break 3;	
				}
			}	
		}
	}
	
	$out .= "</table>";
	return $out;
}
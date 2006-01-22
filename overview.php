<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	$btl=new BartlbyUi($Bartlby_CONF);
	
	$layout= new Layout();
	$layout->DisplayHelp(array(0=>"WARN|Welcome to BartlbyUI",1=>"INFO|This is the help screen"));
	$layout->MetaRefresh(30);
	$layout->Table("100%");
	$lib=bartlby_lib_info($btl->CFG);
	$info=$btl->info;
	
	$mode=bartlby_config($btl->CFG, "i_am_a_slave");
	if(!$mode) {
		$vmode="MASTER";	
	} else {
		$vmode="SLAVE<br>dont change anything";	
	}
	$info_box_title='Core Information<div class="clock" nowrap>Time: ' . date("d.m.Y H:i:s") . '</div>';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$core_content = "<table class='nopad' width='100%'>
		<tr>
			<td class='font1'>(Logged in as: <font class='font2'>" . $btl->user . "</font>)</td>
			<td align=right class='font1'>Uptime:<font class='font2'>" . $btl->intervall(time()-$btl->info[startup_time]) . "</font></td>
		</tr>
		<tr>
			<td class='font1'>Services: <font class='font2'>" . $info[services] . "&nbsp;&nbsp;&nbsp;&nbsp;Workers: " . $info[workers] . "&nbsp;&nbsp;&nbsp;&nbsp;Downtimes: " . $info[downtimes]. "&nbsp;&nbsp;&nbsp;&nbsp;Running: " . $info[current_running]  . "</font></td>
			<td align=right class='font1'>Datalib:<font class='font2'>" . $lib[Name] . "-" . $lib[Version] . "</font></td>
		</tr>
		<tr>
			<td class='font1'>Version: <font class='font2'>" . $btl->getRelease() . "</font></td>
			<td align=right class='font1'>Mode:<font class='font2'>" . $vmode . "</font></td>
		</tr>
	</table>";
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	
	
	
	
	
	
	
	
	$is_repl_on=bartlby_config($btl->CFG, "replication");
	$repl = "Replication enabled:<font class='font2'> <b>$is_repl_on</b><br>";
	if($is_repl_on == "true") {
			$repl_cnt=bartlby_config($btl->CFG, "replicate_cnt");
			$repl .="Replicating to $repl_cnt Servers every " . bartlby_config($btl->CFG, "replication_intervall") . "<br>";
			for($x=1; $x<=$repl_cnt; $x++) {
				$repl .= str_repeat("&nbsp;", 20) . " Server:" . bartlby_config($btl->CFG, "replicate[" . $x . "]") . "<br>";	
			}
			$repl .= "Last Replication was on:" . date("d.m.Y H:i:s", $btl->info[last_replication]) . "<br></font>";
	}
	
	$servers=$btl->GetSVCMap();
	$hosts_sum=count($servers);
	$hosts_up=0;
	$hosts_down=0;
	$services_critical=0;
	$services_ok=0;
	$services_warning=0;
	$services_unkown=0;
	$all_services=0;
	
	while(list($k,$v)=@each($servers)) {
		$x=$k;
		if($btl->isServerUp($x, $servers)) {
			$hosts_up++;	
		} else {
			$hosts_down++;	
			$hosts_a_down[$k]=1;
			
		}
		for($y=0; $y<count($v); $y++) {
			$qck[$v[$y][server_name]][$v[$y][current_state]]++;	
			$qck[$v[$y][server_name]][10]=$v[$y][server_id];
			if($v[$y][is_downtime] == 1) {
				$qck[$v[$y][server_name]][$v[$y][current_state]]--;
				$qck[$v[$y][server_name]][downtime]++;
			}
			
			
			$all_services++;
			switch($v[$y][current_state]) {

				case 0:
					$services_ok++;
					if($v[$y][is_downtime] == 1) {
						$services_ok--;
						$services_downtime++;	
					}
				break;
				case 1:
					$services_warning++;
					if($v[$y][is_downtime] == 1) {
						$services_warning--;
						$services_downtime++;	
					}
				break;
				case 2:
					$services_critical++;
					if($v[$y][is_downtime] == 1) {
						$services_critical--;
						$services_downtime++;	
					}
				break;
				
				default:
					$services_unkown++;
					if($v[$y][is_downtime] == 1) {
						$services_ok--;
						$services_downtime++;	
					}
				
				
			}	
		}
		
		
	}
	
	$service_sum=$all_services-$services_downtime;
	
	
	
	

	if($service_sum == 0) {
		$criticals=100;
	} else {
		$criticals=(($service_sum-$services_ok) * 100 / $service_sum);
	}

	$proz=100-$criticals;
	
	
	
	
	$prozent_zahl = floor($proz);
	$prozent_float = number_format($proz, 1); 
	$prozent_crit_zahl = floor($criticals);
	$prozent_crit_float = number_format($criticals, 1); 
	
	$color="green";
	
	if($prozent_float <= 60) {
		$color="red";	
	} else if($prozent_float <= 90) {
		$color="yellow";	
	} else if($prozent_float <= 80) {
		$color="red";	
	} else {
		$color="green";
	}

	$bar=$prozent_float . "% Ok - $prozent_crit_float % Critical";

	/*
	for($x=0; $x<$service_sum; $x++) {
		
		$svc=bartlby_get_service($btl->CFG, $x);
		
		$qck[$svc[server_name]][$svc[last_state]]++;	
		$qck[$svc[server_name]][10]=$svc[server_id];
	}
	
	*/
	
	$quick_view = "<table class='nopad' width=100%>";
	while(list($k, $v)=@each($qck)) {
		
		if($k != $last_qck) {
			$cl="";
			$STATE="UP";
			if ($hosts_a_down[$qck[$k][10]] == 1) {
				$cl="";
				$STATE="DOWN";
			}
			$quick_view .= "<tr>";
			$quick_view .= "<td class=$cl><font size=1><a href='services.php?server_id=" . $qck[$k][10] . "'>$k</A></td>";
			$quick_view .= "<td class=$cl><font size=1>$STATE</td>";
			$quick_view .= "<td class=$cl><table width=100>";
			
			$sf=false;
			if($qck[$k][0]) {
				$sf=true;
				$qo="<tr><td class=green><font size=1><a href='services.php?server_id=" . $qck[$k][10] . "&expect_state=0'>" . $qck[$k][0] . " OK's</A></td></tr>";
			}
			if($qck[$k][1]) {
				$sf=true;
				$qw="<tr><td class=orange><font size=1><a href='services.php?server_id=" . $qck[$k][10] . "&expect_state=1'>" . $qck[$k][1] . " Warnings</A></td></tr>";
			}
			
			if($qck[$k][2]) {
				$sf=true;
				$qc="<tr><td class=red><font size=1><a href='services.php?server_id=" . $qck[$k][10] . "&expect_state=2'>" . $qck[$k][2] . " Criticals</A></td></tr>";
			}
			
			if($qck[$k][3]) {
				$sf=true;
				$qk="<tr><td class=yellow><font size=1><a href='services.php?server_id=" . $qck[$k][10] . "&expect_state=3'>" . $qck[$k][3] . " Unkown</A></td></tr>";
			}
			if($qck[$k][4]) {
				$sf=true;
				$qk="<tr><td ><font size=1>" . $qck[$k][4] . " Info</td></tr>";
			}
			if($qck[$k][downtime]) {
				$qk="<tr><td ><font size=1>" . $qck[$k][downtime] . " Downtime</td></tr>";
			}
					
				$quick_view .= "$qo";
				$quick_view .= "$qw";
				$quick_view .= "$qc";
				$quick_view .= "$qk";
			$quick_view .= "</table></td>";
			$quick_view .= "</tr>";
			$quick_view .= "<tr><td colspan=3><hr noshade></td></tr>";
		}
		
		$last_qck=$k;	
		$qo="";
		$qw="";
		$qc="";
		$qk="";
	}
	
	$quick_view .= "</table>";
	$tac_title='Tactical Overview<div class="clock"></div>';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$tac_content = "<table class='nopad' width='100%'>
		<tr>
			<td colspan=2 class='font1'>Hosts:<font class='font2'>" . $hosts_sum . "</font></td>
			<td colspan=4 align=left class='font1'>Services:<font class='font2'>" . $service_sum . "</font></td>
		</tr>
		<tr>
			<td class='font1'>Up:<font class='font2'>" . $hosts_up. "</font></td>
			<td class='font1'>Down:<font class='font2'>" . $hosts_down. "</font></td>
			<td class='font1'>OK:<font class='font2'>" . $services_ok. "</font></td>
			<td class='font1'>Warning:<font class='font2'>" . $services_warning. "</font></td>
			<td class='font1'>Critical:<font class='font2'>" . $services_critical. "</font></td>
			<td class='font1'>Downtime:<font class='font2'>" . $services_downtime. "</font></td>
		</tr>
		<tr>
			<td class='font1' colspan=5>$repl</td>
			
		</tr>
	</table>";
	$layout->push_outside($layout->create_box($tac_title, $tac_content));
	
	$health_title='System Health<div class="clock"></div>';  
	$silverbar = "<table class='nopad'>
		<tr>
			<td class='bar_left_silver'>&nbsp;</td>
			<td class='bar_middle_silver' style='width:" . 100*7.3 . "'></td>
			<td class='bar_right_silver'>&nbsp;</td>
			<td class='font2'>" . $prozent_float . "% OK</td>
			
		</tr>
		
	</table>";
	
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$health_content = "<div style='position:relative; z-index:2; '> <table class='nopad'>
		<tr>
			<td  class='bar_left_" . $color . "'>&nbsp;</td>
			<td class='bar_middle_" . $color . "' style='width:" . $prozent_float*7.3 . "'></td>
			<td class='bar_right_" . $color . "'>&nbsp;</td>
			<td class='font2'>&nbsp;</td>
			
		</tr>
		
	</table></div><div style='position:relative; z-index:1; top:-40px;'>$silverbar</div>";
	$layout->push_outside($layout->create_box($health_title, $health_content));
	

	
	$layout->setTitle("QuickView");
	
	
	
	$layout->Tr(
	$layout->Td(
			array(0=>$quick_view)
		)

	);
	$layout->TableEnd();
	$layout->display();
	
?>

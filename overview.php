<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->DisplayHelp(array(0=>"WARN|Welcome to BartlbyUI",1=>"INFO|This is the help screen"));
	$layout->MetaRefresh(30);
	$layout->Table("100%");
	$lib=bartlby_lib_info($btl->CFG);
	$mode=bartlby_config($btl->CFG, "i_am_a_slave");
	if(!$mode) {
		$vmode="MASTER";	
	} else {
		$vmode="SLAVE<br>dont change anything";	
	}
	$info_box_title='Core Information<div class="clock">Time: ' . date("d.m.Y H:i:s") . '</div>';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$core_content = "<table class='nopad' width='100%'>
		<tr>
			<td class='font1'>(Logged in as: <font class='font2'>" . $btl->user . "</font>)</td>
			<td align=right class='font1'>Uptime:<font class='font2'>" . $btl->intervall(time()-$btl->info[startup_time]) . "</font></td>
		</tr>
		<tr>
			<td class='font1'>Services: <font class='font2'>" . $info[services] . "&nbsp;&nbsp;&nbsp;&nbsp;Workers: " . $info[workers] . "&nbsp;&nbsp;&nbsp;&nbsp;Running: " . $info[current_running]  . "</font></td>
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
	
	$servers=$btl->GetServers();
	$hosts_sum=count($servers);
	$hosts_up=0;
	$hosts_down=0;
	while(list($k,$v)=@each($servers)) {
		$x=$k;
		if($btl->isServerUp($x)) {
			$hosts_up++;	
		} else {
			$hosts_down++;	
			$hosts_a_down[$k]=1;
			
		}
	}
	
	$service_sum=$btl->ServiceCount();
	
	$services_critical=0;
	$services_ok=0;
	$services_warning=0;
	
	for($x=0; $x<$service_sum; $x++) {
		$svc=bartlby_get_service($btl->CFG, $x);
		switch($svc[last_state]) {
			case 0:
				$services_ok++;
			break;
			case 1:
				$services_warning++;
			break;
			case 2:
				$services_critical++;
			break;
				
		}	
	}
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

	for($x=0; $x<$service_sum; $x++) {
		
		$svc=bartlby_get_service($btl->CFG, $x);
		
		$qck[$svc[server_name]][$svc[last_state]]++;	
		$qck[$svc[server_name]][10]=$svc[server_id];
	}
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
			
			
			if($qck[$k][0]) {
				$qo="<tr><td class=green><font size=1>" . $qck[$k][0] . " OK's</td></tr>";
			}
			if($qck[$k][1]) {
				$qw="<tr><td class=orange><font size=1>" . $qck[$k][1] . " Warnings</td></tr>";
			}
			
			if($qck[$k][2]) {
				$qc="<tr><td class=red><font size=1>" . $qck[$k][2] . " Criticals</td></tr>";
			}
			
			if($qck[$k][3]) {
				$qk="<tr><td class=yellow><font size=1>" . $qck[$k][3] . " Unkown</td></tr>";
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
			<td colspan=3 align=left class='font1'>Services:<font class='font2'>" . $service_sum . "</font></td>
		</tr>
		<tr>
			<td class='font1'>Up:<font class='font2'>" . $hosts_up. "</font></td>
			<td class='font1'>Down:<font class='font2'>" . $hosts_down. "</font></td>
			<td class='font1'>OK:<font class='font2'>" . $services_ok. "</font></td>
			<td class='font1'>Warning:<font class='font2'>" . $services_warning. "</font></td>
			<td class='font1'>Critical:<font class='font2'>" . $services_critical. "</font></td>
		</tr>
		<tr>
			<td class='font1' colspan=5>$repl</td>
			
		</tr>
	</table>";
	$layout->push_outside($layout->create_box($tac_title, $tac_content));
	
	$health_title='System Health<div class="clock"></div>';  
	// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
	$health_content = "<table class='nopad'>
		<tr>
			<td class='bar_left_" . $color . "'>&nbsp;</td>
			<td class='bar_middle_" . $color . "' style='width:" . $prozent_float*8.5 . "'></td>
			<td class='bar_right_" . $color . "'>&nbsp;</td>
			<td class='font2'>" . $prozent_float . "% OK</td>
			
		</tr>
		
	</table>";
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

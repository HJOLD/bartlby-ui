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
	
	
	$is_repl_on=bartlby_config($btl->CFG, "replication");
	$repl = "<hr noshade>Replication enabled: <b>$is_repl_on</b><br>";
	if($is_repl_on == "true") {
			$repl_cnt=bartlby_config($btl->CFG, "replicate_cnt");
			$repl .="Replicating to $repl_cnt Servers every " . bartlby_config($btl->CFG, "replication_intervall") . "<br>";
			for($x=1; $x<=$repl_cnt; $x++) {
				$repl .= str_repeat("&nbsp;", 20) . " Server:" . bartlby_config($btl->CFG, "replicate[" . $x . "]") . "<br>";	
			}
			$repl .= "Last Replication was on:" . date("d.m.Y H:i:s", $btl->info[last_replication]) . "<br>";
	}
	
	$servers=$btl->GetServers();
	$hosts_sum=count($servers);
	
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
	
	while(list($k, $v)=@each($qck)) {
		
		if($k != $last_qck) {
			$cl="green";
			$STATE="UP";
			if ($hosts_a_down[$qck[$k][10]] == 1) {
				$cl="red";
				$STATE="DOWN";
			}
			$quick_view .= "<tr>";
			$quick_view .= "<td class=$cl><font size=1><i><a href='services.php?server_id=" . $qck[$k][10] . "'><font size=1>$k</A></i></td>";
			$quick_view .= "<td class=$cl><font size=1>$STATE</td>";
			$quick_view .= "<td class=$cl><table width=100%>";
			
			
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
		}
		
		$last_qck=$k;	
		$qo="";
		$qw="";
		$qc="";
		$qk="";
	}
	
	
	

	
	
	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 10,
						'class'=>'header',
						'show'=>'Tactical Overview'
						)
				)
			)

	);
	
	
	$layout->Tr(
	$layout->Td(
			Array(
				0=>array("width"=>"80%", "show"=>"
					<table width=100%>
					<tr>
						<td class=none colspan=3><b>Hosts ($hosts_sum):</b></td>
					</tr>
					<tr>
						<td class=none>UP:<font color=green> $hosts_up</font></td>
						<td class=none colspan=2>Down:<font color=red> $hosts_down</font></td>
					</tr>
					
					<tr>
						<td class=none colspan=3><b>Services ($service_sum):</b></td>
					</tr>
					<tr>
						<td class=none>(<a href='services.php?service_state=0'>View</A>)Ok:<font color=green> $services_ok</font></td>
						<td class=none>(<a href='services.php?service_state=1'>View</A>)Warning:<font color=orange> $services_warning</font></td>
						<td class=none>(<a href='services.php?service_state=2'>View</A>)Critical:<font color=red> $services_critical</font></td>
					</tr>
					<tr>
						<td colspan=3>
						$repl
						
						
						
						</td>
					</tr>
					
					
					
					</table>
				"),
				1=>"System health<br>
				<table class=none width=250>
					<tr class=none>
						<td class=none width=200 style='background-color:black'>
							<!--<table>
							<tr>
								<td  width=" . $prozent_float*2 . " class=none !style='background-color:$color'><img src='" . $color . ".png' height=20 width=" . $prozent_float*2 . ">&nbsp;</td>
							</tr>
							</table>-->
							<img src='images/" . $color . ".png' height=20 width=" . $prozent_float*2 . ">
						</td>
						<td nowrap class=none><font size=1>$prozent_float % OK</td>
						
						
					</tr>
				</table>
				<br>
				<table width=250>
				<tr>
					<td colspan=3><b>Quick View</b></td>
				</tr>
				$quick_view
				
				
				</table>
				
				"
				
				
			)
		)

	);
	$layout->TableEnd();
	$layout->display();
	
?>

<?
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$btl->hasRight("view_bandwidth");
	$servers=$btl->GetSVCMap();
	
	$layout= new Layout();
	$layout->setTitle("Global bandwidth overview");
	
	$layout->set_menu("main");
	$layout->Table("100%");
	
	




while(list($k, $v) = each($servers)) {
	$dd="";
	for($x=0; $x<count($v); $x++) {
		if($v[$x][plugin] == "bartlby_if") {
			
			$ee=explode(" ", $v[$x][new_server_text]);
			$d=array();
			$u=array();
			$iif="";
			for($z=0; $z<=count($ee); $z++) {
				if($ee[$z] == "interface") {
					$iif=$ee[$z+1];
				}			
				if($ee[$z]{0} == "D") {
					$e=explode(":", $ee[$z]);	
					array_push($d, $e[1]);
				}
				if($ee[$z]{0} == "U") {
					$e=explode(":", $ee[$z]);	
					array_push($u, $e[1]);
				}
			}
			$cur=0;
			$last=$d[0];
			
			for($a=1; $a<count($d); $a++) {
				$bps=$d[$a]-$last;
				$dbps[bps] +=$bps;
				$dbps[counter]++;
				$last=$d[$a];
			}
			$cur=0;
			$last=$u[0];
			
			for($a=1; $a<count($u); $a++) {
				$bps=$u[$a]-$last;
				
				$ubps[bps] +=$bps;
				$ubps[counter]++;
				$last=$u[$a];
			}
			$dlink = round(($dbps[bps] / $dbps[counter]),2) . "";
			$ulink = round(($ubps[bps] / $ubps[counter]),2) . "";
			$sumd += $dlink;
			$sumu += $ulink;
			
			$dlink = format_bytes($dlink);
			$ulink = format_bytes($ulink);
			
			
			$dd .= "<table bordeR=0><tr><td colspan=2><a href='service_detail.php?service_place=" . $v[$x][shm_place] . "'>" . $v[$x][service_name] . "</A>($iif)</td></tr><tr><td>down:" . $dlink . " </td><td>up: " . $ulink . " </td></tr></table><br>";	
			
		}
		
		
	}
	
	if($dd != "") {
		$layout->push_outside($layout->create_box("<a href='server_detail.php?server_id=" . $v[0][server_id] . "'>" . $v[0][server_name] . "</A>", $dd));
	}
	/*
	$layout->Tr(
		$layout->Td(
				Array(
					"<b><img src='server_icons/" . $v[0][server_icon] . "'><a href='server_detail.php?server_id=" . $v[0][server_id] . "'>" . $v[0][server_name] . "</A></b>",
					$dd
				)
			)
	
	);
	*/	
	
}
	$sumd=format_bytes($sumd);
	$sumu=format_bytes($sumu);
	$layout->Tr(
		$layout->Td(
				Array(
					"Summary:",
					"down: $sumd  up: $sumu"
					
				)
			)
	
	);
	
	$layout->TableEnd();
	$layout->display();
	
function format_bytes($bytes) {
	$r = $bytes;
	$unit = "Bytes/sec";	
	
	//B
	
	//KB
	if($r >= 1024) {
		$r = $r/1024;	
		$unit="KB/sec";
	}
	//MB
	if($r >= 1024) {
		$r = $r/1024;	
		$unit="MB/sec";
	}
	//GB
	if($r >= 1024) {
		$r = $r/1024;	
		$unit="MB/sec";
	}
	
	return round($r,2) . " " . $unit;
	
}
	

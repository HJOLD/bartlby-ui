<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);


$ibox[0][c]="green";
$ibox[0][v]=0;
$ibox[0][s]=1;	
$ibox[0][k]="OK";
$ibox[1][c]="orange";        
$ibox[1][v]=1;	  
$ibox[1][k]="Warning";
$ibox[2][c]="red";        
$ibox[2][v]=2;	  
$ibox[2][k]="Critical";


$layout= new Layout();

$layout= new Layout();
$layout->Form("fm1", "report.php");
$layout->Table("100%");

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Report'
					)
			)
		)

);

$log_mask=bartlby_config($btl->CFG, "logfile");

if(!$_GET[report_service] || !$log_mask) {
	$out ="You v choosen a server? or log file is not set";	
} else {
	$out = "creating report for service: $_GET[report_service] From: $_GET[report_start] To: $_GET[report_end]<br>";	
	
	$date_start=explode(".", $_GET[report_start]);
	$date_end=explode(".", $_GET[report_end]);
	
	$time_start=mktime(0,0,0, $date_start[1], $date_start[0], $date_start[2]);
	$time_end=mktime(0,0,0, $date_end[1], $date_end[0], $date_end[2]);
	
	$daycnt = $time_end-$time_start+86400;
	
	$day_x=$daycnt/86400;
	$files_scanned=array();
	
	$work_on=$time_start;
	for($x=0; $x<$day_x; $x++) {
		$filename = $log_mask . "." . date("Y.m.d", $work_on);
		$last_mark=$work_on;
		
		$work_on += 86400;
		
		$fdata=@file($filename);
		$lines = count($fdata);
		
		array_push($files_scanned, array(0=>$filename, 1=>$lines));
		
		
		$last_state=$_GET[report_init];
		
		$dig_map[$time_start]=$last_state;
		while(list($k,$v) = @each($fdata)) {
			$info_array=explode(";",$v);
			
			$log_detail_o=explode("@", $info_array[2]);
			list($d, $m,$y, $h, $s, $i) = sscanf($info_array[0], "%d.%d.%d %d:%d:%d");
			$log_stamp=mktime($h,$s,$i,$m,$d,$y);
					
			if($log_detail_o[1] == "LOG") {
				$tmp=explode("|", $log_detail_o[2]);
				
				if($_GET[report_service] && $tmp[0] != $_GET[report_service]) {
					
					continue;	
				}
				
				if($last_state != $tmp[1]) {
					
					
					
					$diff = $log_stamp - $last_mark;
					//$out .= "State changed from " . $btl->getState($last_state) . " to " . $btl->getState($tmp[1]) . "<br>";	
					//$out .= "Where " . $diff . " in " . $btl->getState($last_state) . "<br>"; 
					
					$svc[$last_state] += $diff;
					
					$last_state=$tmp[1];
					$last_mark=$log_stamp;
					$dig_map[$log_stamp]=$last_state;
				}
				
				//$out = $tmp[2] . " changed to " . $btl->getState($tmp[1]) . "(" . $tmp[3] . ")";
				
			} else if($log_detail_o[1] == "NOT") {
				$tmp=explode("|", $log_detail_o[2]);
				if($_GET[report_service] && $tmp[0] != $_GET[report_service]) {
				
					continue;	
				}
				//$out .= "Done " . $tmp[3] . " for " . $tmp[4] . " Service:" .  $tmp[5] . " " . $btl->getState($tmp[2]);
				if(!is_array($notify[$tmp[4]][$tmp[3]])) {
					$notify[$tmp[4]][$tmp[3]]=array();
				} 
				$el[0]=$log_stamp;
				$el[1]=$tmp[2];
				array_push($notify[$tmp[4]][$tmp[3]], $el);
			
			} 	
		}
		if($work_on > time()) {
			$work_on=time();	
		}
		$diff = $work_on - $last_mark;
		//$out .= "EOD: " . $diff . " " . $btl->getState($last_state) . "<br>";
		$svc[$last_state] += $diff;
		
		
		
		
			
		
	}
		$out .= "<table width=100%>";
		$out .= "<td colspan=3 class=header>Service Time</td>";
		
		$hun=$svc[0]+$svc[1]+$svc[2];
		$flash[0]="0";
		$flash[1]="0";
		$flash[2]="0";
		while(list($state, $time) = @each($svc)) {
			
			$perc =   (($hun-$time) * 100 / $hun);
			$perc =100-$perc;
			
			$out .= "<tr>";
			$out .= "<td width=200 class='" . $btl->getColor($state) . "'>State:  " . $btl->getState($state) . "</td>";
			$out .= "<td>Time:  " . $btl->intervall($time) . " seconds</td>";
			$out .= "<td>Percent:  <b>" . round($perc,2) . "</b> seconds</td>";
			
			$flash[$state]=$perc;
			
			$out .= "</tr>";
		}
		
		for($x=0; $x<3; $x++) {
			$nstate= $x+1;
			$rstr .= "&text_" . $nstate . "=" . $btl->getState($x) . "&value_" . $nstate . "=" . $flash[$x];	
		}
		
		$out .= "<tr>";
		
			$out .= '<td colspan=2 align=center>
			
				<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
					codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" 
					width="350" 
					height="250" 
					id="pie" 
					align="middle">
				<param name="wmode" value="transparent">
				<param name="allowScriptAccess" value="sameDomain" />
				<param name="movie" value="flash/pie.swf?a=' . $rstr . '" />
				<param name="quality" value="high" />
				<embed src="flash/pie.swf?a=' . $rstr . '" quality="high" width="350" height="250" name="pie" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent"/>
				</object>
				<br>
				<!--http://actionscript.org/showMovie.php?id=483-->
			</td>';
		
		$out .= "</tr>";
		$out .= "</table>";
		
		$out .= "<table width=100%>";
		$out .= "<td colspan=2 class=header>Notifications:</td>";
		$hun=$daycnt;
		while(list($worker, $dd) = @each($notify)) {
			
			
			
			$out .= "<tr>";
			$out .= "<td valign=top width=200><b>$worker</b></td>";
			
			$out .= "<td>";
			
			
			while(list($trigger, $dd1) = @each($dd)) {
				$out .=	"<i>" . $trigger . "</i><br>";
				while(list($k, $ts) = @each($dd1)) {
					$out .= "&nbsp;	&nbsp;&nbsp;&nbsp;&nbsp; "  . date("d.m.Y H:i:s", $ts[0]) . " (<font color='" . $btl->getColor($ts[1]) . "'>" . $btl->getState($ts[1]) . "</font>)<br>";
				}
			}
			
			$out .= "</td>";
			
			
			$out .= "</tr>";
		}
		$out .= "</table>";
		
	
}


$layout->Tr(
	$layout->Td(
			Array(
				0=>$out
			)
		)

);
for($x=0; $x<count($files_scanned); $x++)  {
	$worked_on_files++;
	$worked_on_lines += $files_scanned[$x][1]; 	
}

$layout->Tr(
	$layout->Td(
			Array(
				0=>"Looked @ $worked_on_files files and $worked_on_lines Lines"
			)
		)

);





$layout->TableEnd();

$layout->FormEnd();
$layout->display();
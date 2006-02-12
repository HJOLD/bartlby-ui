<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->set_menu("report");
	$layout->Form("fm1", "logview.php");
	$layout->Table("100%");
	
	$ch_time=time();
	if($_GET[l]) {
		$tt=explode(".",$_GET[l]);
		//var_dump($tt);
		$ch_time=mktime(0,0,0,$tt[1],$tt[2],$tt[0]);	
	}
	
	$logf=bartlby_config($btl->CFG, "logfile") . date(".Y.m.d", $ch_time);
	$svcid=$_GET[service_id];
	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 3,
						'class'=>'header',
						'show'=>"<a href='logview.php?bartlby_filter=" . $_GET["bartlby_filter"] . "&service_id=$svcid&l=" . date("Y.m.d", $ch_time-86400)  . "'>&laquo;" . date("Y.m.d", $ch_time-86400) . "</A> Logfile ($logf) <a href='logview.php?bartlby_filter=" . $_GET["bartlby_filter"] . "&service_id=$svcid&l=" . date("Y.m.d", $ch_time+86400)  . "'>&raquo;" . date("Y.m.d", $ch_time+86400) . "</A>"
						)
				)
			)

	);
	$layout->Tr(
		$layout->Td(
				Array(
					0=>array("colspan" => 3, 
						"show" => "Filter:" . $layout->Field("bartlby_filter", "text", $_GET["bartlby_filter"]) . $layout->Field("bartlby_sub", "submit", "Filter")
					)
				)
			)

		);
	$fla=@file($logf);
	$fl=@array_reverse($fla);
	$lcounter=0;
	
	
	$curp = $_GET["site"] > 0 ? $_GET["site"] : 1;
	$perp=100;
	$forward_link=$btl->create_pagelinks("logview.php?logview.php?bartlby_filter=" . $_GET["bartlby_filter"] . "&service_id=$svcid&l=" . date("Y.m.d", $ch_time), count($fl)-1, $perp, $curp,"site");
	$skip_em=($curp*$perp)-$perp;
	$skipped=0;
	
	$layout->setTitle($forward_link);	
	
	while(list($k, $v)=@each($fl)) {
		
		if($_GET["bartlby_filter"]) {
			if(!preg_match("/" . $_GET["bartlby_filter"] . "/i", $v)) {
				continue;
			}
		}
		$info_array=explode(";",$v);
		
		$log_detail_o=explode("@", $info_array[2]);
		
		if($log_detail_o[1] == "PERF") {
			$tmp=explode("|", $log_detail_o[2]);
			
			if($_GET[service_id] && $tmp[0] != $_GET[service_id]) {
				
				continue;	
			}
			
			
			$outline = "" . $tmp[1]  . "(" . $tmp[0] . ")";
			$stcheck=6;
		} else if($log_detail_o[1] == "LOG") {
			$tmp=explode("|", $log_detail_o[2]);
			
			if($_GET[service_id] && $tmp[0] != $_GET[service_id]) {
				
				continue;	
			}
			
			
			$outline = $tmp[2] . " changed to " . $btl->getState($tmp[1]) . "<br>" . $tmp[3] . "<br>";
			$stcheck=$tmp[1];
		} else if($log_detail_o[1] == "NOT") {
			$tmp=explode("|", $log_detail_o[2]);
			if($_GET[service_id] && $tmp[0] != $_GET[service_id]) {
				
				continue;	
			}
			$outline = "Done " . $tmp[3] . " for " . $tmp[4] . " Service:" .  $tmp[5] . " " . $btl->getState($tmp[2]);
			$stcheck=5;	
		} elseif(!$_GET[service_id]) {
			$outline = $info_array[2];
			$stcheck=3;
				
		} else {
			continue;	
		}
		$date=$info_array[0];
		switch($stcheck) {
			case 0: $img="ok.gif"; break;
			case 1: $img="warning.gif"; break;
			case 2: $img="critical.gif"; break;
			case 3: $img="info.gif"; break;	
			case 4: $img="info.gif"; break;
			case 5: $img="trigger.gif"; break;
			case 6: $img="icon_stat.gif"; break;
		}

		if($skipped < $skip_em) {
			$skipped++;
			continue;	
		}
		if($lcounter > 100) {
			break;	
		}
		$lcounter++;
				
		$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'class'=>'header1',
						
						'show'=>"<font size=1>$date</font>"
						),
					1=>Array(
						'class'=>'header1',
						'width'=>25,						
						'show'=>"<img src='images/$img'>"
						),
					2=>Array(
						'width'=>720,
						'class'=>'header1',
						'show'=>str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", str_replace( "\\dbr", "<br>",$outline))
						),
				)
			)

		);
	}
	$layout->TableEnd();
	$layout->FormEnd();
	$layout->display("no");
	
?>

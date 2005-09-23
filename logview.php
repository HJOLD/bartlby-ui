<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF);
	$info=$btl->getInfo();
	$layout= new Layout();
	
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
						'show'=>"<a href='logview.php?service_id=$svcid&l=" . date("Y.m.d", $ch_time-86400)  . "'>&laquo;" . date("Y.m.d", $ch_time-86400) . "</A> Logfile ($logf) <a href='logview.php?service_id=$svcid&l=" . date("Y.m.d", $ch_time+86400)  . "'>&raquo;" . date("Y.m.d", $ch_time+86400) . "</A>"
						)
				)
			)

	);
	$fl=@file($logf);
	
	while(list($k, $v)=@each($fl)) {
		$info_array=explode(";",$v);
		
		$log_detail_o=explode("@", $info_array[2]);
		
		if($log_detail_o[1] == "LOG") {
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
			case 0: $img="ok.png"; break;
			case 1: $img="warning.png"; break;
			case 2: $img="critical.png"; break;
			case 3: $img="info.png"; break;	
			case 4: $img="info.png"; break;
			case 5: $img="trigger.png"; break;
			case 6: $img="flapping.gif"; break;
		}
		
		$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'class'=>'header1',
						'show'=>"$date"
						),
					1=>Array(
						'class'=>'header1',
						'width'=>25,						
						'show'=>"<img src='images/$img'>"
						),
					2=>Array(
						'class'=>'header1',
						'show'=>str_replace( "\\dbr", "<br>",$outline)
						),
				)
			)

		);
	}
	$layout->TableEnd();
	$layout->display("no");
	
?>

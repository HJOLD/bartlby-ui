<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->set_menu("worker");
$layout->setTitle("Add Worker");
$optind=0;
$plgs=bartlby_config($btl->CFG, "trigger_dir");
$dh=opendir($plgs);
while ($file = readdir ($dh)) { 
   if ($file != "." && $file != "..") { 
   	clearstatcache();
   	if(is_executable($plgs . "/" . $file) && !is_dir($plgs . "/" . $file)) {
   		
       		$triggers[$optind][c]="";
       		$triggers[$optind][v]=$file;
       		$triggers[$optind][k]=$file;
       		/*if($defaults[plugin] == $file) {
       			$plugins[$optind][s]=1;	
       		}*/
       		
       		$optind++;
       	}
   } 
}
closedir($dh); 


$map = $btl->GetSVCMap();
$optind=0;
while(list($k, $servs) = @each($map)) {

	for($x=0; $x<count($servs); $x++) {
		//$v1=bartlby_get_service_by_id($btl->CFG, $servs[$x][service_id]);
		
		if($x == 0) {
			//$isup=$btl->isServerUp($v1[server_id]);
			//if($isup == 1 ) { $isup="UP"; } else { $isup="DOWN"; }
			$servers[$optind][c]="";
			$servers[$optind][v]="";	
			$servers[$optind][k]="[ $isup ]&raquo;" . $servs[$x][server_name] . "&laquo;";
			$optind++;
		} else {
			
		}
		$state=$btl->getState($servs[$x][current_state]);
		$servers[$optind][c]="";
		$servers[$optind][v]=$servs[$x][service_id];	
		$servers[$optind][k]="&nbsp;[ $state ]&nbsp;" .  $servs[$x][service_name];
		
		$optind++;
	}
}




$act[0][c]="";
$act[0][v]="0";
$act[0][k]="Inactive";

$act[1][c]="";
$act[1][v]="1";
$act[1][k]="Active";

$layout->OUT .= "<script>
		function simulateTriggers() {
			wname=document.fm1.worker_name.value;
			wmail=document.fm1.worker_mail.value;
			wicq=document.fm1.worker_icq.value;
			TRR=document.fm1['worker_triggers[]'];
			wstr='|';
			for(x=0; x<=TRR.length-1; x++) {
				
				if(TRR.options[x].selected) {
					wstr =  wstr +  TRR.options[x].value + '|';	
				}
				
			}
			window.open('trigger.php?user='+wname+'&mail='+wmail+'&icq='+wicq+'&trs=' + wstr, 'tr', 'width=600, height=600, scrollbars=yes');
		}
		</script>
";

$layout->DisplayHelp(array(0=>"INFO|Adding a new server to monitor cycle"));

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");



$layout->Tr(
	$layout->Td(
		array(
			0=>"Name",
			1=>$layout->Field("worker_name", "text", "") . $layout->Field("action", "hidden", "add_worker")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Password:",
			1=>$layout->Field("worker_password", "password", "")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Mail",
			1=>$layout->Field("worker_mail", "text", "")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"ICQ",
			1=>$layout->Field("worker_icq", "text", "")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Active?:",
			1=>$layout->DropDown("worker_active", $act)
		)
	)
);



$layout->Tr(
	$layout->Td(
		array(
			0=>"Services:",
			1=>$layout->DropDown("worker_services[]", $servers, "multiple")
		)
	)
);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Notifys:",
			1=>"<input type=checkbox value=0 name=notify[]><font color=green>OK</font><input value=1 type=checkbox name=notify[]><font color=orange>Warning</font><input value=2 type=checkbox name=notify[]><font color=red>Critical</font>" 
		)
	)
);
//$_GET[worker_triggers]
$layout->Tr(
	$layout->Td(
		array(
			0=>"Triggers:",
			1=>$layout->DropDown("worker_triggers[]", $triggers, "multiple") . " <a href='javascript:simulateTriggers();'>Simulate</A>"
		)
	)
);

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					"align"=>"right",
					'show'=>$layout->Field("Subm", "submit", "next->")
					)
			)
		)

);


$layout->TableEnd();
$layout->FormEnd();
$layout->display();
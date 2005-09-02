<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

function dnl($i) {
	return sprintf("%02d", $i);
}

$defaults=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);

//Types

$types[0][c]="";
$types[0][v]="1";
$types[0][k]="Active";

if($defaults[service_type] == 1) {
	$types[0][s]=1;
}

$types[1][c]="";
$types[1][v]="2";
$types[1][k]="Passive";
if($defaults[service_type] == 2) {
	$types[1][s]=1;
}

$types[2][c]="";
$types[2][v]="3";
$types[2][k]="Group";
if($defaults[service_type] == 3) {
	$types[2][s]=1;
}



//Get plugins :))
$layout->DisplayHelp(array(0=>"INFO|Add a service to an existing Server"));
$optind=0;
$plgs=bartlby_config($btl->CFG, "agent_plugin_dir");
$dh=opendir($plgs);
while ($file = readdir ($dh)) { 
   if ($file != "." && $file != "..") { 
   	clearstatcache();
   	if(is_executable($plgs . "/" . $file) && !is_dir($plgs . "/" . $file)) {
       		$plugins[$optind][c]="";
       		$plugins[$optind][v]=$file;
       		$plugins[$optind][k]=$file;
       		if($defaults[plugin] == $file) {
       			$plugins[$optind][s]=1;	
       		}
       		$optind++;
       	}
   } 
}
closedir($dh); 

$servs=$btl->GetServers();
$optind=0;
while(list($k, $v) = each($servs)) {
	$sr=bartlby_get_server_by_id($btl->CFG, $k);
	
	$servers[$optind][c]="";
	$servers[$optind][v]=$k;	
	$servers[$optind][k]=$sr[server_name];
	if($defaults[server_id] == $k) {
		$servers[$optind][s]=1;	
	}
	$optind++;
}

$layout->OUT .= "<script>
		function showPlgHelp() {
			plugin=document.fm1.service_plugin.options[document.fm1.service_plugin.selectedIndex].value;
			window.open('execv.php?cmd='+plugin+' -h', 'plgwnd', 'width=600, height=600');
		}
		function GrpChk() {
			window.open('grpstr.php?str='+document.fm1.service_var.value, 'grp', 'width=600, height=600, scrollbars=yes');
		}
		</script>
";
$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");



$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Add Service'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Type",
			1=>$layout->DropDown("service_type", $types) 
		)
	)
);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Name",
			1=>$layout->Field("service_name", "text", $defaults[service_name]) . $layout->Field("action", "hidden", "modify_service")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Server",
			1=>$layout->DropDown("service_server", $servers)
			
		)
	)
);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check From:",
			1=>$layout->Field("service_time_from", "text", dnl($defaults[hour_from]) . ":" . dnl($defaults[min_from]) . ":00")
			
		)
	)
);


$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check To:",
			1=>$layout->Field("service_time_to", "text", dnl($defaults[hour_to]) . ":" . dnl($defaults[min_to]) . ":00")
			
		)
	)
);


$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check intervall",
			1=>$layout->Field("service_interval", "text", $defaults[check_interval]) . " Seconds"
			
		)
	)
);

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Active Service Settings'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Plugin",
			1=>$layout->DropDown("service_plugin", $plugins) .  " <a href='javascript:showPlgHelp();'>Show Help of Plugin</A>"
		)
	)
);


$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Plugin Arguments",
			1=>$layout->Field("service_args", "text", $defaults[plugin_arguments])
			
		)
	)
);



$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Passive Service Settings'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Timeout",
			1=>$layout->Field("service_passive_timeout", "text", $defaults[service_passive_timeout])
			
		)
	)
);


$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Group Service Settings'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Group definition",
			1=>$layout->Field("service_var", "hidden", $defaults[service_var]) . "<a href='javascript:GrpChk();'>Open Group selector</A>"
			
		)
	)
);

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					"align"=>"right",
					'show'=>$layout->Field("Subm", "submit", "next->") . $layout->Field("service_id", "hidden", $_GET[service_id])
					)
			)
		)

);


$layout->TableEnd();
$layout->FormEnd();
$layout->display();
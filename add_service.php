<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout->setTitle("");

//Ack's
$ack[0][c]="";
$ack[0][v] = 0; //No
$ack[0][k] = "No"; //No

$ack[1][c]="";
$ack[1][v] = 1; //No
$ack[1][k] = "Yes"; //No

//Types

$types[0][c]="";
$types[0][v]="1";
$types[0][k]="Active";

$types[1][c]="";
$types[1][v]="2";
$types[1][k]="Passive";


$types[2][c]="";
$types[2][v]="3";
$types[2][k]="Group";

$types[3][c]="";
$types[3][v]="4";
$types[3][k]="Local";

//Get plugins :))
$layout->set_menu("services");
$optind=0;
$plgs=bartlby_config($btl->CFG, "agent_plugin_dir");
$dh=opendir($plgs);
while ($file = readdir ($dh)) { 
   if ($file != "." && $file != "..") { 
   	clearstatcache();
   	if((preg_match("/\.exe$/i", $file)) || (is_executable($plgs . "/" . $file) && !is_dir($plgs . "/" . $file))) {
       		$plugins[$optind][c]="";
       		$plugins[$optind][v]=$file;
       		$plugins[$optind][k]=$file;
       		$optind++;
       	}
   } 
}
closedir($dh); 

$servs=$btl->GetServers();
$optind=0;
while(list($k, $v) = @each($servs)) {
	$sr=bartlby_get_server_by_id($btl->CFG, $k);
	$servers[$optind][c]="";
	$servers[$optind][v]=$k;	
	$servers[$optind][k]=$sr[server_name];
	$optind++;
}

$layout->OUT .= "<script>
		function testPlg() {
		plugin=document.fm1.service_plugin.options[document.fm1.service_plugin.selectedIndex].value;
		server=document.fm1.service_server.options[document.fm1.service_server.selectedIndex].value;
		plg_args=document.fm1.service_args.value;
		window.open('check.php?server=' + server +  '&plugin=' + plugin + '&args=' + plg_args, 'chk','width=600, height=600, scrollbars=yes'); 
		}
		function showPlgHelp() {
			plugin=document.fm1.service_plugin.options[document.fm1.service_plugin.selectedIndex].value;
			window.open('execv.php?cmd='+plugin+' -h', 'plgwnd', 'width=600, height=600');
		}
		function GrpChk() {
			window.open('grpstr.php?str='+document.fm1.service_var.value, 'grp', 'width=600, height=600, scrollbars=yes');
		}
		function CheckTables() {
			va=document.fm1.service_type.options[document.fm1.service_type.selectedIndex].value;
			GenericToggleFix(\"active\", \"none\");
			GenericToggleFix(\"passive\", \"none\");
			GenericToggleFix(\"group\", \"none\");
			
			if(va == 2) {
				GenericToggleFix(\"passive\", \"block\");
			}
			if(va == 1) {
				GenericToggleFix(\"active\", \"block\");
			}
			if(va == 3) {
				GenericToggleFix(\"group\", \"block\");	
			}
			if(va == 4) {
				GenericToggleFix(\"active\", \"block\");	
			}
			
		}
		CheckTables();
		</script>
";
echo "<form name='fm1' action='bartlby_action.php' method=GET>\n";

$layout->Table("100%");


$active_box_out = "<table>";

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Type",
			1=>$layout->DropDown("service_type", $types, "onChange=\"CheckTables()\"") 
		)
	)
,true);

$active_box_out .=$layout->Tr(
	$layout->Td(
		array(
			0=>"Service Name",
			1=>$layout->Field("service_name", "text", "") . $layout->Field("action", "hidden", "add_service")
		)
	)
,true);

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Server",
			1=>$layout->DropDown("service_server", $servers)
			
		)
	)
,true);

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check From:",
			1=>$layout->Field("service_time_from", "text", "00:00:00")
			
		)
	)
,true);


$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check To:",
			1=>$layout->Field("service_time_to", "text", "24:59:00")
			
		)
	)
,true);


$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Check intervall",
			1=>$layout->Field("service_interval", "text", "10") . " Seconds"
			
		)
	)
,true);

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Acknowledgement",
			1=>$layout->DropDown("service_ack", $ack)
			
		)
	)
,true);
$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service retain in status",
			1=>$layout->Field("service_retain", "text", "3") . " Times"
			
		)
	)
,true);

$active_box_out .= "</table>";
$layout->push_outside($layout->create_box("Basic Settings", $active_box_out, "basic"));

$active_box_out = "<table>";

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Timeout(either TCP or Local)",
			1=>$layout->Field("service_check_timeout", "text", "20")
			
		)
	)
,true);

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Plugin",
			1=>$layout->DropDown("service_plugin", $plugins) .  " <a href='javascript:showPlgHelp();'>Show Help of Plugin</A>&nbsp;&nbsp;<a href='javascript:testPlg();'>Test It</A>"
		)
	)
,true);


$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Plugin Arguments",
			1=>$layout->Field("service_args", "text", "")
			
		)
	)
, true);
$active_box_out .= "</table>";
$layout->push_outside($layout->create_box("Active/Local Settings", $active_box_out, "active"));

$active_box_out = "<table >";

$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Service Timeout",
			1=>$layout->Field("service_passive_timeout", "text", "0")
			
		)
	)
,true);
$active_box_out .= "</table>";
$layout->push_outside($layout->create_box("Passive Settings", $active_box_out, "passive"));




$active_box_out = "<table>";


$active_box_out .= $layout->Tr(
	$layout->Td(
		array(
			0=>"Group definition",
			1=>$layout->Field("service_var", "hidden", "") . "<a href='javascript:GrpChk();'>Open Group selector</A>"
			
		)
	)
,true);

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
$active_box_out .= "</table>";
$layout->push_outside($layout->create_box("Group Settings", $active_box_out, "group"));


$layout->TableEnd();
$layout->FormEnd();
$layout->display();
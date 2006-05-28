<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->setTitle("Modify Server");


$layout->set_menu("client");

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");

$defaults=bartlby_get_server_by_id($btl->CFG, $_GET[server_id]);
if($defaults == false) {
	$btl->redirectError("BARTLBY::OBJECT::MISSING");
	exit(1);	
}
$optind=0;
$dhl=opendir("server_icons");
while($file = readdir($dhl)) {
	//$sr=bartlby_get_server_by_id($btl->CFG, $k);
	
	//$isup=$btl->isServerUp($k);
	//if($isup == 1 ) { $isup="UP"; } else { $isup="DOWN"; }
	if(preg_match("/.*\.[png|gif]/", $file)) {
		
		if($defaults[server_icon] == $file) {
			
			$server_icons[$optind][s]=1;
		}
		$server_icons[$optind][c]="";
		$server_icons[$optind][v]=$file;	
		$server_icons[$optind][k]="&raquo;" . $file;
		$optind++;
	}
	
}
closedir($dhl);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Server Name",
			1=>$layout->Field("server_name", "text", $defaults[server_name]) . $layout->Field("action", "hidden", "modify_server") . "<a href=\"javascript:var w=window.open('locate_server.php','','width=353,height=421, scrollbar=yes, scrollbars=yes')\">Find Server Wizard!</A>"
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Server IP",
			1=>$layout->Field("server_ip", "text", $defaults[server_ip])
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Server Port",
			1=>$layout->Field("server_port", "text", $defaults[server_port]) . $layout->Field("server_id", "hidden", $_GET[server_id])
		)
	)
);
$layout->Tr(
	$layout->Td(
			Array(
				0=>"Icon:",
				1=>$layout->DropDown("server_icon", $server_icons, "onChange=\"serviceManageIconChange(this.form);\"") 
			)
		)

);
$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					"align"=>"left",
					'show'=>"<div id=picholder></div><script>serviceManageIconChange(document.fm1);</script>"
					)
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
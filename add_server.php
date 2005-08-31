<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();



$layout->DisplayHelp(array(0=>"INFO|Adding a new server to monitor cycle"));

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Add Server'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Server Name",
			1=>$layout->Field("server_name", "text", "") . $layout->Field("action", "hidden", "add_server") . "<a href=\"javascript:var w=window.open('locate_server.php','','width=353,height=421, scrollbar=yes, scrollbars=yes')\">Find Server Wizard!</A>"
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Server IP",
			1=>$layout->Field("server_ip", "text", "")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Server Port",
			1=>$layout->Field("server_port", "text", "9030")
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
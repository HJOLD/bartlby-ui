<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout= new Layout();
$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");
$layout->DisplayHelp(array(0=>"INFO|Pick a Server From the Dropdown List"));
$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Package Info'
					)
			)
		)

);




$layout->Tr(
	$layout->Td(
			Array(
				0=>"Name:",
				1=>$layout->Field("package_name", "test", "") . $layout->Field("action", "hidden", "create_package")
			)
		)

);

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					"align"=>"right",
					'show'=>$layout->Field("Subm", "submit", "next->") . $layout->Field("server_id", "hidden", $_GET[server_id])
					)
			)
		)

);


$layout->TableEnd();

$layout->FormEnd();
$layout->display();
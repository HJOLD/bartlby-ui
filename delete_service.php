<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->setTitle("Delete Service");
$layout->DisplayHelp(array(0=>"INFO|Confirm delete request?",1=>"CRIT|If you confirm this the Server and all its assigned services will  be deleted [FOR EVER]"));
$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");





$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>'Really want to service?'
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
					'show'=>$layout->Field("Subm", "submit", "next->") . $layout->Field("action", "hidden", "delete_service") . $layout->Field("service_id", "hidden", $_GET[service_id])
					)
			)
		)

);


$layout->TableEnd();
$layout->FormEnd();
$layout->display();
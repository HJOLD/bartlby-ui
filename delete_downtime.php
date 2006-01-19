<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->setTitle("Delete Downtime");

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");





$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>'Really want to delete downtime?'
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
					'show'=>$layout->Field("Subm", "submit", "next->") . $layout->Field("action", "hidden", "delete_downtime") . $layout->Field("downtime_id", "hidden", $_GET[downtime_id])
					)
			)
		)

);


$layout->TableEnd();
$layout->FormEnd();
$layout->display();
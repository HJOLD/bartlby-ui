<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout->setTitle("Edit Config");
$layout->Form("fm1", "bartlby_action.php","POST");
$layout->Table("100%");

$cur_conf=implode(file($btl->CFG), "");

$layout->Tr(
	$layout->Td(
			Array(
				0=>"Server:",
				1=>$layout->TextArea("cfg_file", $cur_conf, 35, 80) . $layout->Field("action", "hidden", "edit_cfg")
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
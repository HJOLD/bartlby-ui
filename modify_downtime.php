<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout->setTitle("Modify Downtime");

$layout->set_menu("downtimes");

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");

$default=false;
$r=bartlby_downtime_map($btl->CFG);
$optind=0;
for($x=0; $x<count($r); $x++) {
	if($r[$x][downtime_id] == $_GET[downtime_id]) {
		$default=$r[$x];	
	}
}

if($default == false) {
	$btl->redirectError("BARTLBY::OBJECT::MISSING");
	exit(1);	
}

if($default[downtime_type]==2) {
	$dt_type="Server";	
	$dt_hidden=2;
	$btl->hasServerRight($default[service_id]);
} else {
	$dt_type="service";
	$dt_hidden=1;
	$btl->hasServerorServiceRight($default[service_id]);
}


$btl->hasRight("action.modify_downtime");



$layout->Tr(
	$layout->Td(
		array(
			0=>"Reason",
			1=>$layout->Field("downtime_notice", "text", $default[downtime_notice]) . $layout->Field("action", "hidden", "modify_downtime")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"From",
			1=>$layout->Field("downtime_from", "text", date("d.m.Y H:i", $default[downtime_from])) . $layout->Field("downtime_type", "hidden", $dt_hidden)
		)
	)
);

$layout->Tr(
	$layout->Td(
		array(
			0=>"To",
			1=>$layout->Field("downtime_to", "text", date("d.m.Y H:i", $default[downtime_to])) . $layout->Field("downtime_id", "hidden", $_GET[downtime_id])
		)
	)
);
$layout->Tr(
	$layout->Td(
			Array(
				0=>"Type",
				1=>$dt_type . $layout->Field("service_id", "hidden", $default[service_id])
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
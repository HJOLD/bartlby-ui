<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$defaults=bartlby_get_worker_by_id($btl->CFG, $_GET[worker_id]);


$act[0][c]="";
$act[0][v]="0";
$act[0][k]="Inactive";
if($defaults[active] == 0) {
	$act[0][s]=1;
}

$act[1][c]="";
$act[1][v]="1";
$act[1][k]="Active";
if($defaults[active] == 1) {
	$act[1][s]=1;
}

$layout->DisplayHelp(array(0=>"INFO|Adding a new server to monitor cycle"));

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Modify Worker'
					)
			)
		)

);

$layout->Tr(
	$layout->Td(
		array(
			0=>"Name",
			1=>$layout->Field("worker_name", "text", $defaults[name]) . $layout->Field("action", "hidden", "modify_worker")
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Password:",
			1=>$layout->Field("worker_password", "password", $defaults[password])
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Mail",
			1=>$layout->Field("worker_mail", "text", $defaults[mail])
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"ICQ",
			1=>$layout->Field("worker_icq", "text", $defaults[icq])
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
			1=>$layout->Field("worker_services", "text", $defaults[services])
		)
	)
);
$layout->Tr(
	$layout->Td(
		array(
			0=>"Notifys:",
			1=>$layout->Field("worker_notifys", "text", $defaults[notify_levels]) . $layout->Field("worker_id", "hidden", $_GET[worker_id])
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
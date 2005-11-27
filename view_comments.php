<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout= new Layout();

$svc=bartlby_get_service_by_id($btl->CFG, $_GET[service_id]);

$layout->setTitle("Comments on " . $svc[server_name] . ":" . $svc[client_port] . "/" . $svc[service_name]);

$layout->Form("fm1", "bartlby_action.php");
$layout->Table("100%");
$layout->DisplayHelp(array(0=>"INFO|Pick a service From the Dropdown List"));





$layout->Tr(
	$layout->Td(
			Array(
				0=>"Add Comment:",
				1=>$layout->TextArea("comment", "") . $layout->Field("service_id","hidden", $_GET[service_id]) . $layout->Field("action","hidden", "add_comment")
			)
		)

);

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					"align"=>"center",
					'show'=>$layout->Field("Subm", "submit", "submit->")
					)
			)
		)

);

$file=@file("comments/" . $_GET[service_id]);
$rfile=@array_reverse($file);
while(list($k, $v) = @each($rfile)) {
	$cminfo=explode("|", $v);

	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 2,
						"align"=>"left",
						"show"=>"Comment by <b>" . $cminfo[0] . "</b> posted on <i>" . date("d.m.Y H:i:s", $cminfo[1]) . "</i><hr noshade><br>" . $cminfo[2] . "<hr>"
						)
				)
			)
	
	);
	
	
}


$layout->TableEnd();

$layout->FormEnd();
$layout->display();
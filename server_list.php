<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);


$layout= new Layout();
$layout->setTitle("Select a Server");
$layout->Form("fm1", $_GET[script]);
$layout->Table("100%");
$layout->DisplayHelp(array(0=>"INFO|Pick a Server From the Dropdown List"));

$servs=$btl->GetServers();
$optind=0;
//$res=mysql_query("select srv.server_id, srv.server_name from servers srv, rights r where r.right_value=srv.server_id and r.right_key='server' and r.right_user_id=" . $poseidon->user_id);

while(list($k, $v) = @each($servs)) {
	//$sr=bartlby_get_server_by_id($btl->CFG, $k);
	
	//$isup=$btl->isServerUp($k);
	//if($isup == 1 ) { $isup="UP"; } else { $isup="DOWN"; }
	$servers[$optind][c]="";
	$servers[$optind][v]=$k;	
	$servers[$optind][k]="[&nbsp;&nbsp;] &raquo;" . $v;
	$optind++;
}



$layout->Tr(
	$layout->Td(
			Array(
				0=>"Server:",
				1=>$layout->DropDown("server_id", $servers)
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
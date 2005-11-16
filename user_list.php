<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();

$layout->setTitle("Select a  Worker");
$layout->Form("fm1", $_GET[script]);
$layout->Table("100%");
$layout->DisplayHelp(array(0=>"INFO|Pick a service From the Dropdown List"));

$servs=$btl->GetWorker();
$optind=0;
//$res=mysql_query("select srv.server_id, srv.server_name from servers srv, rights r where r.right_value=srv.server_id and r.right_key='server' and r.right_user_id=" . $poseidon->user_id);

while(list($k, $v) = @each($servs)) {
	$v1=bartlby_get_worker_by_id($btl->CFG, $v[worker_id]);
	
	$servers[$optind][c]="";
	$servers[$optind][v]=$v1[worker_id];	
	$servers[$optind][k]=$v1[name];
	$optind++;
}



$layout->Tr(
	$layout->Td(
			Array(
				0=>"Server:",
				1=>$layout->DropDown("worker_id", $servers)
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
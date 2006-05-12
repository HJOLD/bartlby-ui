<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$layout= new Layout();
$layout->set_menu("worker");
$layout->setTitle("Select a  Worker");
$layout->Form("fm1", $_GET[script]);
$layout->Table("100%");


$servs=$btl->GetWorker();
$optind=0;
//$res=mysql_query("select srv.server_id, srv.server_name from servers srv, rights r where r.right_value=srv.server_id and r.right_key='server' and r.right_user_id=" . $poseidon->user_id);

$ajaxed = bartlby_config("ui-extra.conf", "ajaxed");
if($ajaxed == "true") {
	$info_box_title="Extended Search";  
	$core_content = "<script language='JavaScript' type='text/javascript' src='images/ajax_search.js'></script>
	<table  width='100%'>
		
		<tr>
			<td width=150 valign=top class='font2'>Search:</td>
			<td>
			<script>
			function setSearch(value) {
				document.location.href='" . $_GET[script] . "?worker_id=' + value;
				document.getElementById('search_suggest').innerHTML = '';
			}
			</script>
				<input type='text' id='txtSearch' name='txtSearch' alt='Search Criteria' onkeyup=\"searchSuggest('ajax_worker_search.php');\" autocomplete='off' /> (PREG syntax)
				
				<div id='search_suggest'>
				</div>
			
			
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
}

$dropdownded = bartlby_config("ui-extra.conf", "disable_dropdown_search");

if($dropdownded != "true")  {

	while(list($k, $v) = @each($servs)) {
		
		if($v[name] != $btl->user) {
			if(!$btl->simpleRight("be_admin", "true")) {
				continue;	
			}
		}
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
} else {
	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 2,
						"align"=>"left",
						'show'=>"Dropdown searches disabled in ui-extra config"
						)
				)
			)
	
	);	
}

$layout->TableEnd();

$layout->FormEnd();
$layout->display();
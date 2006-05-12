<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);


$layout= new Layout();
$layout->setTitle("Select a Server");
$layout->Form("fm1", $_GET[script]);
$layout->Table("100%");
$layout->set_menu("client");


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
				document.location.href='" . $_GET[script] . "?server_id=' + value;
				document.getElementById('search_suggest').innerHTML = '';
			}
			</script>
				<input type='text' id='txtSearch' name='txtSearch' alt='Search Criteria' onkeyup=\"searchSuggest('ajax_server_search.php');\" autocomplete='off' /> (PREG syntax)
				
				<div id='search_suggest'>
				</div>
			
			
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
}


$dropdownded = bartlby_config("ui-extra.conf", "disable_dropdown_search");

if($dropdownded != "true")  {
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
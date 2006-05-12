<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);

$map = $btl->GetSVCMap();
$optind=0;
//$res=mysql_query("select srv.server_id, srv.server_name from servers srv, rights r where r.right_value=srv.server_id and r.right_key='server' and r.right_user_id=" . $poseidon->user_id);
$y=0;
while(list($k, $servs) = @each($map)) {
	$displayed_servers++;
	
	for($x=0; $x<count($servs); $x++) {
		$ostr=$servs[$x][server_name] . "/" . $servs[$x][service_name];
		if(preg_match("/" . $_GET[search] . "/i", $ostr)) {
			echo "<a href=\"javascript:setSearch('" . $servs[$x][service_id] . "');\">$ostr</a>\n";
			$y++;
		}		
		if($y>20) {
			break 2;	
		}
	}
}

/*
echo "<a href=\"javascript:setSearch('63');\">test1</a>\n";
*/	
?>

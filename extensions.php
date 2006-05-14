<?
set_time_limit(0);
echo "<!---SMASH IE BUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFERBUFFER--->";
flush();
function dnl($i) {
	return sprintf("%02d", $i);
}
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
$btl=new BartlbyUi($Bartlby_CONF);


$layout= new Layout();
$layout->set_menu("core");
$layout->setTitle("Installed Extensions");
$layout->Table("100%");


	
	


$r=$btl->getExtensionsReturn("_About", $layout);
$layout->TableEnd();

$layout->display();

function sort_table($plugin_table) {
	global $sorto;
	
	while(list($k, $v) = each($plugin_table)) {
		
		$max=0;
		$sum=0;
		$cnt=0;
		for($x=0; $x<count($v); $x++) {
			if($v[$x] > $max) {
				$max=$v[$x];	
			}
			$sum += $v[$x];
			$cnt++;
		}
		$avg=round($sum/$cnt, 2);
		
		$plugins_sortable[$avg][$k][$max] = 1;
	}
	
	if($sorto == "asc") {
		ksort($plugins_sortable);
	} else {
		krsort($plugins_sortable);	
	}
	return $plugins_sortable;
	
}

function make_html($info=array()) {
	global $maxn;
	$have=0;
	$out = "<table >";
	$out .= "<tr>";
	$out .= "<td class='font2'>&nbsp;</td>";	
	$out .= "<td class='font2'>Average</td>";
	
	$out .= "</tr>";
	while(list($average, $d) = each( $info )) {
		while(list($plugin, $d1) = each($d)) {
			while(list($max, $d2) = each($d1)) {
				$out .= "<tr>";
				$out .= "<td align=left valign=top nowrap>$plugin</td>";	
				$out .= "<td align=right valign=bottom>$average ms</td>";
				
				$out .= "</tr>";
				$have++;
				
				if($have == $maxn) {
					break 3;	
				}
			}	
		}
	}
	
	$out .= "</table>";
	return $out;
}
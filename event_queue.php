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
$layout->setTitle("Bartlby Last Event's");
$layout->Table("100%");

//Check if profiling is enabled
	$evnts="";
	
	for($x=0; $x<=128; $x++) {
		$msg=bartlby_event_fetch($btl->CFG, $x);
		if($msg[id] == 0) {
			continue;	
		}
		$evnts .= "(" . date("d.m.Y H:i:s", $msg[time]) . ") ID:<b>" . $msg[id] . "</b>:" . $msg[message] . "<br>";	
	}

	
	
	
	$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>$evnts
					)
			)
		)

	);	


$layout->TableEnd();

$layout->display();


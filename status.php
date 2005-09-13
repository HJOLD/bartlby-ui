<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->MetaRefresh(10);
	$layout->Table("100%");
	$lib=bartlby_lib_info($btl->CFG);
	
	$base_dir=bartlby_config($btl->CFG, "basedir");
	$cdump="";
	if(!$base_dir) {
		$cdump="Basedir not set!!";	
	} else {
		if(file_exists($base_dir . "/core")) {
			$cdump="coredump found";	
		}
	}
	
	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 13,
						'class'=>'header',
						'show'=>'Core Information (<i>Logged in as:</i><b>' . $btl->user . '</b>)'
						)
				)
			)

	);
	$layout->Tr(
	$layout->Td(
			Array(
				0=>"Time:",
				1=>"<b>" . date("d.m.Y h:i:s"),
				2=>"Services:",
				3=>"<b>" . $info[services],
				4=>"Workers:",
				5=>"<b>" . $info[workers],
				6=>"Running:",
				7=>"<b>" . $info[current_running],
				8=>"Datalib:",
				9=>"<b>" . $lib[Name] . "-" . $lib[Version] . "<br><font size=1></font>",
				10=>"Version:",
				11=>"<b>" . $btl->getRelease(),
				12=>"<font color=red>$cdump</font>"
				
			)
		)

	);
	$layout->TableEnd();
	$layout->display("no");
	
?>

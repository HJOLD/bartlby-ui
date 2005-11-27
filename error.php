<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	$layout= new Layout();
	$layout->setTitle("Error");
	$btl = new BartlbyUi($Bartlby_CONF, false);
	
$layout->OUT .= "<script>
		function doRetry() {
			document.location.href='start.php';	
		}
		function doStartup() {
			document.location.href='error.php?msg=BARTLBY::START';	
		}
		</script>
";
	
	$layout->Table("100%");

	
	
	switch ($_GET[msg]) {
		case 'BARTLBY::START':
			//Set Env
			//Call bartlby.startup start
			$base_dir=bartlby_config($btl->CFG, "basedir");
			if(!$base_dir) {
				$omsg="basedir config not set";
			} else {
				$cmd="export BARTLBY_HOME='$base_dir'; cd \$BARTLBY_HOME; ./bartlby.startup start 2>&1";
			
				$fp=popen($cmd, "r");
				$omsg=fgets($fp, 1024);
				pclose($fp);	
			}
		break;
		case 'BARTLBY::NOT::RUNNING':
			$omsg = "Bartlby doesnt seem to be running<br><input type=button value='try to startup bartlby' onClick='doStartup();'>";
		break;
		
		default:
			$omsg="ERROR is undefined";	
	}
	
	$layout->Tr(
	$layout->Td(
			Array(
				0=>$omsg
				
			)
		)

	);
	
	$layout->Tr(
	$layout->Td(
			Array(
				0=>"<input type=button value='retry' onClick='doRetry();'>"
				
			)
		)

	);
	
	
	$layout->TableEnd();
	$layout->display();
	
?>
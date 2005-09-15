<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	$layout= new Layout();

$layout->OUT .= "<script>
		function doRetry() {
			document.location.href='start.php';	
		}
		</script>
";
	
	$layout->Table("100%");

	$layout->Tr(
		$layout->Td(
				Array(
					0=>Array(
						'colspan'=> 2,
						'class'=>'header',
						'show'=>'Error'
						)
				)
			)

	);
	
	switch ($_GET[msg]) {
		case 'BARTLBY::NOT::RUNNING':
			$omsg = "Bartlby doesnt seem to be running<br>";
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
<?
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";


$layout= new Layout();


$layout->Table("100%");

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'class'=>'header',
					'show'=>'Bartlby action'
					)
			)
		)

);



switch($_GET[action]) {
	
	
	default:
		$msg="Action not implemented ($_GET[action])";
		
	break;
		
}

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 2,
					'show'=>$msg
					)
			)
		)

);


$layout->TableEnd();
$layout->display();
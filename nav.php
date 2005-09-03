<?

include "layout.class.php";

$layout= new Layout("scroll=no");

$layout->Table("100%");

$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Monitoring'
					)
			)
		)

);


	$layout->Tr(
		$layout->Td(
			Array(
				
				0=>"<a href='overview.php' target=main> Overview</a>"
				
			)
		)
	);



	$layout->Tr(
		$layout->Td(
			Array(
				
				0=>"<a href='services.php' target=main>Services</a>"
				
			)
		)
	);

//0=>"<a href='main.php' target=main>Tactical Overview</a>",


$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Report/Logging'
					)
			)
		)

);



	$layout->Tr(
		$layout->Td(
			Array(
				0=>"<a href='create_report.php' target=main>Report/s</a>"
			
			
			)
		)
	);




	$layout->Tr(
		$layout->Td(
			Array(
				0=>"<a href='logview.php' target=main>LogFile</a>"
				
				
			)
		)
	);






$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Server-Admin'
					)
			)
		)

);




	$layout->Tr(
		$layout->Td(
			Array(
				0=>"<a href='add_server.php' target=main>Add Server</a>"
				
				
			)
		)
	);



$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='server_list.php?script=modify_server.php' target=main>Modify Server</a>"
			
			
		)
	)
);



$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='server_list.php?script=delete_server.php' target=main>Delete Server</a>"
			
			
		)
	)
);



$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Service-Admin'
					)
			)
		)

);



$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='add_service.php' target=main>Add Service</a>"
			
			
		)
	)
);




$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='service_list.php?script=modify_service.php' target=main>Modify Service</a>"
			
			
		)
	)
);




$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='service_list.php?script=delete_service.php' target=main>Delete Service</a>"
			
			
		)
	)
);


$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Worker-Admin'
					)
			)
		)

);




$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='add_worker.php' target=main>Add Worker</a>"
			
			
		)
	)
);




$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='user_list.php?script=modify_worker.php' target=main>Modify Worker</a>"
			
			
		)
	)
);




$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='user_list.php?script=delete_worker.php' target=main>Delete Worker</a>"
			
			
		)
	)
);









$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'header',
					'show'=>'Your self ;)'
					)
			)
		)

);






$layout->Tr(
	$layout->Td(
		Array(
			0=>"<a href='logout.php' target=main>Logout</a>"
			
			
		)
	)
);

if($_GET[r] == 1) {


$layout->Tr(
	$layout->Td(
			Array(
				0=>Array(
					'colspan'=> 1,
					'class'=>'red',
					'show'=>'<a  target=main href=bartlby_action.php?action=reload>Settings changed!! reload!!</A>'
					)
			)
		)

);

}

$layout->TableEnd();
$layout->display("no");



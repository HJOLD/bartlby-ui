<?

	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	include "Mail.php";
	include "extensions/ArS/ArS.class.php";
	
	
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$btl->hasRight("super_user");
	$sg = new ArS();
	$servers=$btl->GetSVCMap();
	
	$layout= new Layout();
	$layout->setTitle("ArS: action");
	
	$layout->set_menu("ArS");
	$layout->Form("fm1", "extensions_wrap.php");
	$layout->Table("100%");
	
	if($_GET[action] == "add") {
		$action="Adding report";	
		
		$da = $sg->storage->load_key("reports");
		$reports=unserialize($da);
		if(!is_array($reports)) {
			$reports=array();	
		}
		
		array_push($reports, $_GET);
		
		$da = serialize($reports);
		$da = $sg->storage->save_key("reports", $da);
		
		$output = "report added!!!";
		
		
		
	} else if($_GET[action] == "delete") {
		$action = "delete report";
		
		$da = $sg->storage->load_key("reports");
		$reports=unserialize($da);
		$y=0;
		
		for($x=0; $x<count($reports); $x++) {
			if($x != $_GET[ars_report]) {
				$new[$y]=$reports[$x];
				$y++;	
			}
		}
		
		$da = serialize($new);
		$sg->storage->save_key("reports", $da);
		
		$output = "report deleted";	
	} else if($_GET[action] == "sendout") {
		
		$action = "sending out the reports";
		
		
		$da = $sg->storage->load_key("reports");
		$reports=unserialize($da);
		$y=0;
		
		
		for($x=0; $x<count($reports); $x++) {
			$defaults=bartlby_get_service_by_id($btl->CFG, $reports[$x][ars_service_id]);
			$rap = "Report for: " . $defaults[server_name] . "/" . $defaults[service_name] . "\n";
			$btl_subj = "Bartlby report";
			
			if($_GET[wich] == "daily") {
				
				if($reports[$x][ars_daily]) {
					$rap .= "DAILY REPORT:\n\n";
					$rep = $btl->do_report(date("d.m.Y", time()-86400), date("d.m.Y"), 0, $reports[$x][ars_service_id]);
					$rap .= "FROM: " . date("d.m.Y", time()-86400) . " TO: " . date("d.m.Y", time()) . "\n";
					$rap .= $sg->format_report($rep);	
				
				
				}
			}
			if($_GET[wich] == "weekly") {
				if($reports[$x][ars_weekly]) {
					$rap .= "WEEKLY REPORT:\n\n";
					$rep = $btl->do_report(date("d.m.Y", time()-(86400*7)), date("d.m.Y"), 0, $reports[$x][ars_service_id]);
					$rap .= "FROM: " . date("d.m.Y", time()-(86400*7)) . " TO: " . date("d.m.Y", time()) . "\n";
					$rap .= $sg->format_report($rep);	
					
					
				}		
			}
			
			if($_GET[wich] == "monthly") {
				if($reports[$x][ars_monthly]) {
					$rap .= "MONTHLY REPORT:\n\n";
					$rep = $btl->do_report(date("d.m.Y", time()-(86400*31)), date("d.m.Y"), 0, $reports[$x][ars_service_id]);
					$rap .= "FROM: " . date("d.m.Y", time()-(86400*31)) . " TO: " . date("d.m.Y", time()) . "\n";
					$rap .= $sg->format_report($rep);	
					
					
				}	
			}
			
					
			
			
			
			
			$headers = array('From' => 'helmut@januschka.com' , 'To' => $reports[$x][ars_to],
					   'Subject' => $btl_subj);
					   
					   
			$smtp = Mail::factory('smtp',
		                array ('host' => "localhost",
              		          'auth' => false,
                        		   'timeout' => 10,
                        		'debug' => false
                		));
                		
                	$mail = $smtp->send($reports[$x][ars_to], $headers, $rap);

			
			
			
		}
		
		
		$output = "done";
			
	}
	
	
	
	
	
	
	

$layout->Tr(
	$layout->Td(
			Array(
				array("colspan" => 2, "show" => "<b>$action</b>")
			)
		)

);	
$layout->Tr(
	$layout->Td(
			Array(
				array("colspan" => 2, "show" => "$output")
			)
		)

);	


	$layout->FormEnd();
	
	$layout->TableEnd();
	$layout->display();
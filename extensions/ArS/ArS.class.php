<?

include "config.php";
include_once "bartlbystorage.class.php";



class ArS {
	function ArS() {
		$this->layout = new Layout();
		$this->storage = new BartlbyStorage("ArS");
		
	}
	
	
	function _About() {
		return "Automated Reporting System (EXPERIMENTAL)!!!";
			
	}
	function format_report($rep) {
		global $btl;
		
		
		
		$svc=$rep[svc];
		$state_array=$rep[state_array];
		$notify=$rep[notify];
		$files_scanned=$rep[files_scanned];
		
		$hun=$svc[0]+$svc[1]+$svc[2];
		
		$rap = "-----------------------------------------------\n";
		
		$rap .= "Service Time:\n";
		while(list($state, $time) = @each($svc)) {
			for($xy=0; $xy<count($state_array);$xy++) {
					$o1 .= date("d.m.Y H:i:s", $state_array[$xy][end]) . " ";
					$o1 .=  $btl->getState($state_array[$xy][lstate]) . " ";
			
					$o1 .= $state_array[$xy][msg] . " \n";
								
			}
								
			$perc =   (($hun-$time) * 100 / $hun);
			$perc =100-$perc;
			
			
			$rap .= "State:  " . $btl->getState($state);
			$rap  .= " Time:  " . $btl->intervall($time) . " seconds";
			$rap  .= " " . round($perc,2) . "% \n";
			
			
			
		}
		
		$rap .= "Notifications:\n";
		while(list($worker, $dd) = @each($notify)) {
			
			$rap .= $worker . "\n";
					
			while(list($trigger, $dd1) = @each($dd)) {
				$rap .= "\t" . $trigger . "\n";
				while(list($k, $ts) = @each($dd1)) {
					$rap .= "\t\t"  . date("d.m.Y H:i:s", $ts[0]) . " (" . $btl->getState($ts[1]) . ")\n";
				}
			}
						
		}
		$rap .= "Output:\n";
		$rap .= $o1;
		$rap .= "-----------------------------------------------\n";
		return $rap;
	
	}
	
	function xajax_ars_delete_form() {
		global $xajax;
		$res = new xajaxResponse();
		$values = $xajax->_xmlToArray("xjxquery", $_GET[xajaxargs][2]);
		
		if($values[ars_report] == "") {
			$res->AddScript("alert('no report selected to delete')");	
		} else {
			$res->AddScript("document.fm1.submit()");	
		}
		
		return $res;	
	}
	function xajax_ars_add_form() {
		global $xajax;
		$res = new xajaxResponse();
		$values = $xajax->_xmlToArray("xjxquery", $_GET[xajaxargs][2]);
		
		if($values[ars_to] == "") {
			$res->addAssign("error_ars_to", "innerHTML", "required field");			
		} else {
			$res->AddScript("document.fm1.submit()");	
		}
		
		return $res;
	}
	function _permissions() {
		global $worker_rights;
		
		$r = "ArS requires 'super user' right";
		return $r;
	}
	
	function _Menu() {
		$r =  $this->layout->beginMenu();
		$r .= $this->layout->addRoot("ArS");
		$r .= $this->layout->addSub("ArS", "View/Delete","extensions_wrap.php?script=ArS/view.php");
		$r .= $this->layout->addSub("ArS", "Add","service_list.php?script=extensions_wrap.php%3fscript=ArS/add.php");
		
		$r .= $this->layout->endMenu();
		return $r;
	}
	
	
	
	
	
}

?>

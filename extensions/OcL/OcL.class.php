<?
include "config.php";
@dl("ssh2.so");

class OcL {
	function OcL() {
		$this->layout = new Layout();
		$this->storage = new BartlbyStorage("OcL");
		
	}
	
	function resolveGroupString($str) {
		global $btl;
		$aa=explode("|", $str);
		for($aax=0; $aax<count($aa); $aax++) {
			$bb = explode("=", $aa[$aax]);
			if($aa[$aax]) {
				//$svc = @bartlby_get_service_by_id($this->CFG, $aa[$aax]);
				$idx=$btl->findSHMPlace($aa[$aax]);
				$svc=bartlby_get_service($btl->CFG, $idx);
				$dtemp="";
				if($svc[is_downtime] == 1) {
					$dtemp="<i>DOWNTIME</i>";
				}
				$r .= "Service: <a href='service_detail.php?service_place=" . $idx . "'>$svc[server_name]:$svc[client_port]/$svc[service_name]</A> (Current: <font color=" . $btl->getColor($svc[current_state]) . ">" . $btl->getState($svc[current_state]) . "</font>) $dtemp<br>";
			}
		}	
		return $r;
	}
	function _About() {
		$snotice="enables you to 'blog' about system events";
		return $snotice;
			
	}
	
	function _Menu() {
		$r =  $this->layout->beginMenu();
		$r .= $this->layout->addRoot("OcL");
		$r .= $this->layout->addSub("OcL", "Logs","extensions_wrap.php?script=OcL/index.php");
		$r .= $this->layout->addSub("OcL", "Add","extensions_wrap.php?script=OcL/add.php");
		
		$r .= $this->layout->endMenu();
		return $r;
	}
	
	
	function xajax_ocl_del_entry() {
		global $xajax, $btl;
		$res = new xajaxResponse();
		$btl->hasRight("ocl_delete");
		$identifier=$_GET[xajaxargs][2];
		$ocl_id=$_GET[xajaxargs][3];
		
		$v=unserialize($this->storage->load_key($identifier));
		
		
		$new_x=0;;
		for($x=0; $x<count($v); $x++) {
			if($v[$x][ocl_id] != $ocl_id) {
				$n[$new_x]=$v[$x];
				$new_x++;
			}
		}
		
		
		$this->storage->save_key($identifier,serialize($n));
		$res->AddScript("document.location.reload()");
		
		return $res;
	}
	function xajax_ocl_add_form() {
		global $xajax;
		$res = new xajaxResponse();
		$values = $xajax->_xmlToArray("xjxquery", $_GET[xajaxargs][2]);
		$e=0;
		$res->addAssign("error_ocl_date", "innerHTML", "");
		$res->addAssign("error_ocl_subject", "innerHTML", "");
		$res->addAssign("error_ocl_duration", "innerHTML", "");
		$res->addAssign("error_ocl_caller", "innerHTML", "");
		$res->addAssign("error_ocl_error_long", "innerHTML", "");
		
		if($values[ocl_date] == "") {
			$res->addAssign("error_ocl_date", "innerHTML", "required field");			
			$e++;
		} 
		if($values[ocl_subject] == "") {
			$res->addAssign("error_ocl_subject", "innerHTML", "required field");			
			$e++;
		} 
		
		if($values[ocl_duration] == "") {
			$res->addAssign("error_ocl_duration", "innerHTML", "required field");			
			$e++;
		} 
		if($values[ocl_caller] == "") {
			$res->addAssign("error_ocl_caller", "innerHTML", "required field");			
			$e++;
		} 
		if($values[ocl_error_long] == "") {
			$res->addAssign("error_ocl_error_long", "innerHTML", "required field");			
			$e++;
		} 
		if($e == 0) {
			$res->AddScript("document.fm1.submit()");	
		}
		return $res;
	}
	
	function _permissions() {
		global $worker_rights;
		
		$ky["ocl_add"]="allowed to add ocl-entrys";	
		$ky["ocl_csv"]="can view csv reports";
		$ky["ocl_view"]="can view logbook";
		$ky["ocl_edit"]="can edit entrys";
		$ky["ocl_delete"]="can delete";
		
		while(list($k, $v) = each($ky)) {
			$kc="";
			if($worker_rights[$k][0] && $worker_rights[$k][0] != "false") {
				$kc="checked";	
			}
			$r .= "<input type=checkbox name='$k' $kc> " . $ky[$k] . "<br>";
				
		}
		return $r;
	}
	
	
	
	function _overview() {
		$identifier = date("m.Y",time());
		$v=unserialize($this->storage->load_key($identifier));
		$v=@array_reverse($v);
		
		if(count($v)==0) {
			return "no entrys found";
		}
		$lm = "Latest 3 OcL entry's:";
		
		for($x=0; $x<=3; $x++) {
			if($v[$x][ocl_subject]) {
				$lm .= "<li> <a href='extensions_wrap.php?script=OcL/index.php'>" . $v[$x][ocl_subject] . "</b></A> by <i>" . $v[$x][ocl_poster] . "</i> on " . $v[$x][ocl_date];	
			}
		}
		
		return $lm;	
	}
	/*
	function _services() {
		return "_services";	
	}
	function _processInfo() {
		return "_processInfo";	
	}
	*/
	/*
	function _serverDetail() {
			
	}
	*/
	/*
	function _serviceDetail() {
		global $defaults;
		
		
		return "<a href='extensions_wrap.php?script=OcL/index.php&server_id=" . $defaults[server_id] . "'>Modify/View Inventory Details</A>";		
	}
	*/
	
	
	
	
}

?>

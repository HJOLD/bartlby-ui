<?
class BartlbyUi {
	function BartlbyUi($cfg) {
		$this->CFG=$cfg;
		$this->perform_auth();
		//Check if bartlby is running :-)
		$this->info=@bartlby_get_info($this->CFG);
		
		if(!$this->info) {
			$this->redirectError("BARTLBY::NOT::RUNNING");
			exit(1);
		} 
		$this->release=$this->info[version];
		
	}
	function getRelease() {
		return $this->release;	
	}
	function getInfo() {
		return bartlby_get_info($this->CFG);	
	}
	
	function perform_auth() {
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
       			Header("WWW-Authenticate: Basic realm=\"My Realm\"");
       			Header("HTTP/1.0 401 Unauthorized");
       			
       			exit;
       		} else {
   			$this->user=$_SERVER['PHP_AUTH_USER'];
  		}	
	}
	function redirectError($msg) {
		//header("Location: error.php?msg=" . $msg);	
		echo "<script>parent.location.href='error.php?msg=$msg';</script>";
	}
	function isServerUp($server_id) {
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			if($svc[server_id] == $server_id) {
				if($svc[last_state] == 2) {
					return false;	
				}	
			}
			
		}
		return true;
	}
	function ServiceCount() {
		return $this->info[services];	
	}
	function GetServers() {
		
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			$servers[$svc[server_id]]=$svc[server_name];
			
		}
		
		//var_dump($servers);
		return $servers;
	}
	function GetSVCMap($state) {
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			if(!is_array($servers[$svc[server_id]])) {
				$servers[$svc[server_id]]=array();
			}
			if($svc[current_state] == $state) {
				array_push($servers[$svc[server_id]], $svc);
			}
			
		}
		ksort($servers);
		return $servers;	
	}
	function getColor($state) {
		switch($state) {
			case 0: return "green"; break;
			case 1: return "orange"; break;
			case 2: return "red"; break;
			case 3: return "yellow"; break;
			case 6: return "blue"; break;
				
			
		}
	}


	function getState($state) {
		switch($state) {
			case 0: return "OK"; break;
			case 1: return "WARNING"; break;
			case 2: return "CRITICAL"; break;
			case 3: return "UNKOWN"; break;
			case 4: return "INFO"; break;
			case 5: return "TRIGGER"; break;
			case 6: return "FLAPPING"; break;
				
			
		}
	}
}
?>
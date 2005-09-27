<?



class BartlbyUi {
	
	function BartlbyUi($cfg, $auth=true) {
		
		if(!function_exists("bartlby_version")) {
			dl("bartlby.so");	
		}					
		$this->CFG=$cfg;
		//Check if bartlby is running :-)
		$this->info=@bartlby_get_info($this->CFG);
		
		
		if(!$this->info && $auth == true) {
			$this->redirectError("BARTLBY::NOT::RUNNING");
			exit(1);
		} 
		$this->perform_auth($auth);
		$this->release=$this->info[version];
		
	}
	function getRelease() {
		return $this->release;	
	}
	function getInfo() {
		return @bartlby_get_info($this->CFG);	
	}
	
	function perform_auth($a=true) {
		$wrks=$this->GetWorker();
		$auted=0;
		if($a==false) {
			$auted=1;
		} else {
			while(list($k, $v) = each($wrks)) {
				//$v1=bartlby_get_worker_by_id($this->CFG, $v[worker_id]);
				if($_SERVER[PHP_AUTH_USER] == $v[name] && $_SERVER[PHP_AUTH_PW] == $v[password]) {
					$auted=1;
				}
			}
		}
		
		if ($auted==0) { 
			
	      		 @header("WWW-Authenticate: Basic realm=\"Bartlby Config Admin\"");	
	      		 @Header("HTTP/1.0 401 Unauthorized");
			 echo "BAD LOGIN $_SERVER[PHP_AUTH_USER]/$_SERVER[PHP_AUTH_PW]\n";
			 exit;
		} else {
			$this->user=$_SERVER[PHP_AUTH_USER];
			$this->pw=$_SERVER[PHP_AUTH_PW];
			$this->user_id=$auted;
			
			
		}
	}
	function redirectError($msg) {
		//header("Location: error.php?msg=" . $msg);	
		echo "<script>parent.location.href='error.php?msg=$msg';</script>";
	}
	function findSHMPlace($svcid) {
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			if($svc[service_id] == $svcid) {
				return $x;
			}	
		}
		return -1;	
	}
	function isServerUp($server_id) {
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			if($svc[server_id] == $server_id) {
				if($svc[last_state] == 2) {
					$svcs_crit++;
				}	
				$svcs_ges++;
			}
			
		}
		if($svcs_ges == $svcs_crit) {
			return false;
		}
		return true;
	}
	function ServiceCount() {
		return $this->info[services];	
	}
	function GetWorker() {
		$r=array();
		for($x=0; $x<$this->info[workers]; $x++) {
			$wrk=bartlby_get_worker($this->CFG, $x);
			
			//$r[$wrk[worker_id]]=$wrk[name];
			array_push($r, $wrk);
		}	
		return $r;
	}
	function GetServers() {
		
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			$servers[$svc[server_id]]=$svc[server_name];
			
		}
		
		//var_dump($servers);
		return $servers;
	}
	function GetServices() {
		$ar=array();
		for($x=0; $x<$this->info[services]; $x++) {	
			$svc=bartlby_get_service($this->CFG, $x);
			array_push($ar, $svc);
		}
		return $ar;
	}
	function GetSVCMap($state=false) {
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			if(!is_array($servers[$svc[server_id]])) {
				$servers[$svc[server_id]]=array();
			}
			if($svc[current_state] == $state || $state == false) {
				
				array_push($servers[$svc[server_id]], $svc);
			}
			
		}
		@ksort($servers);
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
	function intervall($sek) {
		// http://faq-php.de/q/q-code-intervall.html
    		$i = sprintf('%d Day%s, %d Hour%s,'.
            		' %d Minute%s und %d Second%s',
            		$sek / 86400,
            		floor($sek / 86400) != 1 ? 's':'',
            		$sek / 3600 % 24,
            		floor($sek / 3600 % 24) != 1 ? 's':'',
            		$sek / 60 % 60,
            		floor($sek / 60 % 60) != 1 ? 's':'',
            		$sek % 60,
            		floor($sek % 60) != 1 ? 's':''
         	);
    		return $i;
	}
}
?>
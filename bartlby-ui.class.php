<?
set_time_limit(0);



class BartlbyUi {
	
	function BartlbyUi($cfg, $auth=true) {
		
		if(!function_exists("bartlby_version")) {
			dl("bartlby.so");	
		}	
		$this->BASE_URL=substr($_SERVER[SCRIPT_URI], 0, strrpos($_SERVER[SCRIPT_URI], "/")+1);				
		$this->CFG=$cfg;
		//Check if bartlby is running :-)
		$this->info=@bartlby_get_info($this->CFG);
		
		/*
			Check if process is still here
		*/
		$pid_file=bartlby_config($this->CFG, "pidfile_dir");
				
		if((!$this->info && $auth == true) || !$pid_file) {
			$this->redirectError("BARTLBY::NOT::RUNNING");
			exit(1);
		} 
		
		$pid_ar=@file($pid_file . "/bartlby.pid");
		$pid_is=@implode($pid_ar, "");
		
		if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
			if(!file_exists("/proc/" . $pid_is . "/cmdline")) {
				$this->redirectError("BARTLBY::SHM::STALE");
				exit(1);
			}
		}
		
		$this->perform_auth($auth);
		$this->release=$this->info[version];
		$this->loadRights();
		
	}
	function finScreen($f=false) {
		global $_GET,$global_msg;
		
		if($f==false) {
			$f=$_GET[action];	
		}
		
		if(file_exists("fin/" . $f)) {
			$fp=fopen("fin/" . $f,"r");
			while(!feof($fp)) {
				$str=fgets($fp, 1024);
				while(list($k, $v)=@each($_GET)) {
					$str=str_replace("\$_GET[" . $k . "]", $v, $str);	
				}
				while(list($k, $v)=@each($global_msg)) {
					$str=str_replace("\$global_msg[" . $k . "]", $v, $str);	
				}
				$r .= $str;
			}
			
		} else {
			return "message file: $f not found";	
		}
		return $r;
	}
	function simpleRight($k, $v) {
		if(!is_array($this->rights[$k])) {
				return true;
		}
		if($this->rights[$k][0] == $v) {
			return true;
		} else {
			return false;
		}	
	}
	function loadRights() {
		if(file_exists("rights/" . $this->user . ".dat")) {
			$fa=file("rights/" . $this->user . ".dat");
			while(list($k, $v) = each($fa)) {
				$s1=explode("=", $v);
				$this->rights[$s1[0]]=explode(",", trim($s1[1]));
				
			}
			for($x=0; $x<count($this->rights[services]); $x++) {
					settype($this->rights[services][$x], "integer");
			}
			for($x=0; $x<count($this->rights[servers]); $x++) {
					settype($this->rights[servers][$x], "integer");
			}
		}
		if($this->rights[servers][0] == 0) {
			$this->rights[servers]=null;
		}
		
		if($this->rights[services][0] == 0) {
			
			$this->rights[services]=null;
		}
		
		
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
	      		
			
			 $this->redirectError("BARTLBY::LOGIN");
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
	function isServerUp($server_id, &$map) {
		for($x=0; $x<count($map[$server_id]); $x++) {
			if($map[$server_id][$x][current_state] == 0) {
				return true;
			}
		}
		return false;
	}
	function ServiceCount() {
		return $this->info[services];	
	}
	function GetWorker() {
		$r=array();
		for($x=0; $x<$this->info[workers]; $x++) {
			$wrk=bartlby_get_worker($this->CFG, $x);
			if($wrk[name] == "") {
				$x=0;
				continue;	
			}
			//$r[$wrk[worker_id]]=$wrk[name];
			array_push($r, $wrk);
		}	
		return $r;
	}
	function GetServers() {
		
		for($x=0; $x<$this->info[services]; $x++) {
			$svc=bartlby_get_service($this->CFG, $x);
			
			if(is_array($this->rights[servers])) {
				
				if(in_array($svc[server_id], $this->rights[servers])) {
					$servers[$svc[server_id]]=$svc[server_name];
				}
			} else {
				$servers[$svc[server_id]]=$svc[server_name];
			}
			
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
		//array(2555, 3191,2558)
		
		
		$r=bartlby_svc_map($this->CFG, $this->rights[services], $this->rights[servers]);
        
        	
        	//Re order map ;-)
        	
        	
        	for($x=0; $x<count($r); $x++) {
        		if(!is_array($map[$r[$x][server_id]])) {
        			$map[$r[$x][server_id]] = array();
        			
        		}
        		
        		array_push($map[$r[$x][server_id]], $r[$x]);
        	}
        	@ksort($map);
        	
 		return $map; 
			
	}
	function getColor($state) {
		switch($state) {
			case 0: return "green"; break;
			case 1: return "orange"; break;
			case 2: return "red"; break;
			
			default:
				return "silver";
				
			
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
            		' %d Minute%s and %d Second%s',
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
	function installPackage($pkg, $server) {
		$msg = "Installing package '$pkg' on Server:  $server<br>";
		$fp=@fopen("pkgs/" . $pkg, "r");
		if($fp) {
			while(!feof($fp)) {
				$bf .= fgets($fp, 1024);	
			}
			$re=unserialize($bf);
			fclose($fp);
			for($x=0; $x<count($re); $x++) {
				$msg .= "Installing Service: <b>" . $re[$x][service_name] . "</b><br>";	
				
				$tfrom=dnl($re[$x][hour_from]) . ":" . dnl($re[$x][min_from]) . ":00";
				$tto=dnl($re[$x][hour_to]) . ":" . dnl($re[$x][min_to]) . ":00";
				
				$msg .= str_repeat("&nbsp;", 20) . "Plugin:" . $re[$x][plugin] . "/'" . $re[$x][plugin_arguments] . " '<br>";	
				$msg .= str_repeat("&nbsp;", 20) . "Time: $tfrom - $tto / " . $re[$x][check_interval] . "<br>";	
				$msg .= str_repeat("&nbsp;", 20) . "Service Type: " . $re[$x][service_type] . "<br>";
				$ads=bartlby_add_service($this->CFG, $server, $re[$x][plugin],$re[$x][service_name],$re[$x][plugin_arguments],$re[$x][notify_enabled],$re[$x][hour_from], $re[$x][hour_to], $re[$x][min_from], $re[$x][min_to],$re[$x][check_interval],$re[$x][service_type],$re[$x][service_var], $re[$x][service_passive_timeout], $re[$x][service_check_timeout]);
				$msg .= str_repeat("&nbsp;", 20) . "New id: " . $ads . "<br>";
				

			}
			$layout->OUT .= "<script>doReloadButton();</script>";
		} else {
			$msg = "fopen failed()!!<br>";	
		}
		
		return $msg;	
	}
	function create_pagelinks($link, $max, $hm=20, $curp, $si) {
		
		$pages       = 1;
		
		if ( ($max % $hm) == 0 ) {
			$pages= $max / $hm;
		} else {
			$number = ($max / $hm);
			$pages= ceil( $number);
		}
		
		$currpage = $curp > 0 ? $curp : 1;
	
		if ($pages> 1) {
			$first = "<a href='$link&" . $si . "=1'>&laquo;</a>";
			for( $i = 0; $i <= $pages - 1; $i++ ) {
				$times = $i+1;
				if ($times == $curp) {
					$pageline .= "&nbsp;<b>$times</b>";
				} else {
					if ($times < ($currpage - 5) and ($currpage >= 6))  {
						$startdots = '&nbsp;...';
						continue;
					}
					$pageline .= "&nbsp;<a href='$link&" . $si . "=$times'>$times</a>";
					if ($times >= ($currpage + 5)) {
						$enddots = '...&nbsp;';
						break;
					}
				}
			}
			$last = "<a href='$link&" . $si . "=".$pages."'>&raquo;</a>";
			$ret    = $first.$startdots.$pageline.'&nbsp;'.$enddots.$last;
		} else {
			$ret    = "Pages: 1";
		}
	
		return $ret;
	}
}
?>
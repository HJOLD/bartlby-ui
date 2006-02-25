<?
set_time_limit(0);



class BartlbyUi {
	
	function BartlbyUi($cfg, $auth=true, $shm_check=true) {
				
		if(!function_exists("bartlby_version")) {
			dl("bartlby.so");	
		}	
	
		if(bartlby_check_shm_size($cfg) == false) {
			if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
				$this->redirectError("BARTLBY::MODULE::MISMATCH");
				exit(1);
			} else {
				return;	
			}
			
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
	      		 $this->_log("Login attempt from " . $_SERVER[REMOTE_ADDR] . " User: '" . $_SERVER[PHP_AUTH_USER] . "'  Pass: '" . $_SERVER[PHP_AUTH_PW] . "'"); 
			 $this->redirectError("BARTLBY::LOGIN");
			 exit;
		} else {
			$this->user=$_SERVER[PHP_AUTH_USER];
			$this->pw=$_SERVER[PHP_AUTH_PW];
			$this->user_id=$auted;
			
			
		}
	}
	function _log($str) {
		$logfile=bartlby_config($this->CFG, "logfile");
		if(!$logfile) {
			echo $str;	
		} else {
			$logfile = $logfile . "." . date("Y.m.d");
			$str=str_replace("\r", "", $str);
			$str=str_replace("\n", "", $str);
			$str=str_replace(";", "", $str);
			$str=str_replace(">", "", $str);
			$str=str_replace("<", "", $str);
			
			
			$logline=date("d.m.Y H:i:s") . ";" . "[" . posix_getpid() . "];" . $str . ";\n";
			
			$fp=@fopen($logfile, "a+");
			@fwrite($fp, $logline);
			@fclose($fp);
		}
	}
	function redirectError($msg) {
		
		//header("Location: error.php?msg=" . $msg);	
		
		echo "<script>parent.location.href='error.php?msg=$msg';</script>";
		
	}
	function findSHMPlace($svcid) {
		$map=bartlby_svc_map($this->CFG, NULL, NULL);
		
		
		
		for($x=0; $x<count($map); $x++) {
			if($map[$x][service_id] == $svcid) {
				
				return 	$map[$x][shm_place];
				
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
		
		$map=bartlby_svc_map($this->CFG, NULL, NULL);
		
		
		
		for($x=0; $x<count($map); $x++) {
			$servers[$map[$x][server_id]] = $map[$x][server_name];
			
		
		}
			
		
		
		return $servers;
	}
	function GetServices() {
		/*
		$ar=array();
		for($x=0; $x<$this->info[services]; $x++) {	
			$svc=bartlby_get_service($this->CFG, $x);
			array_push($ar, $svc);
		}
		return $ar;
		*/
		$map=bartlby_svc_map($this->CFG, NULL, NULL);
		
		
		
		for($x=0; $x<count($map); $x++) {
			//$servers[$map[$x][server_id]] = $map[$x][server_name];
			array_push($ar, $map[$x]);
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
	function installPackage($pkg, $server, $force_plugin, $force_perf) {
		$basedir=bartlby_config($this->CFG, "basedir");
		
		
		if($basedir) {
			$perf_dir=$basedir . "/perf/";	
		}
		
		$plugin_dir=bartlby_config($this->CFG, "agent_plugin_dir");
		
		
		
		
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
				
				$ads=bartlby_add_service($this->CFG, $server, $re[$x][plugin],$re[$x][service_name],$re[$x][plugin_arguments],$re[$x][notify_enabled],$re[$x][hour_from], $re[$x][hour_to], $re[$x][min_from], $re[$x][min_to],$re[$x][check_interval],$re[$x][service_type],$re[$x][service_var], $re[$x][service_passive_timeout], $re[$x][service_check_timeout], $re[$x][service_ack]);
				$msg .= str_repeat("&nbsp;", 20) . "New id: " . $ads . "<br>";
				
				if($re[$x][__install_plugin]) {
					$msg .= str_repeat("&nbsp;", 20) . "Installing plugin: " . $re[$x][plugin] . "<br>";	
					
					if(!file_exists($plugin_dir . "/" . $re[$x][plugin]) || $force_plugin == "checked") {
						$plugin=@fopen($plugin_dir . "/" . $re[$x][plugin], "wb");
						if($plugin){
							fwrite($plugin, $re[$x][__install_plugin]);
							fclose($plugin);
							@chmod($plugin_dir . "/" . $re[$x][plugin], 0777);
						} else {
							$msg .= str_repeat("&nbsp;", 25) . " plugin fopen( " . $plugin_dir . "/" . $re[$x][plugin] . ") failed<br>";
						}
					} else {
						$msg .= 	str_repeat("&nbsp;", 25) .  "plugin (" . $plugin_dir . "/" . $re[$x][plugin] . ") already existing<br>";
					}
					
				}
				if($re[$x][__install_perf]) {
					$msg .= str_repeat("&nbsp;", 20) . "Installing perf handler: " . $re[$x][plugin] . "<br>";	
					
					if(!file_exists($perf_dir . "/" . $re[$x][plugin]) || $force_perf == "checked") {
						$perf=@fopen($perf_dir . "/" . $re[$x][plugin], "wb");
						if($perf){
							fwrite($perf, $re[$x][__install_perf]);
							fclose($perf);
							@chmod($perf_dir . "/" . $re[$x][plugin], 0777);
						} else {
							$msg .= str_repeat("&nbsp;", 25) . " fopen( " . $perf_dir . "/" . $re[$x][plugin] . ") failed<br>";
						}
					} else {
						$msg .= 	str_repeat("&nbsp;", 25) .  "plugin (" . $re[$x][plugin] . ") already existing<br>";
					}
					
				}
				
				
				

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
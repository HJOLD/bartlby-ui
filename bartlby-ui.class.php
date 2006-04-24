<?
set_time_limit(0);



class BartlbyUi {
	
	function BartlbyUi($cfg, $auth=true, $shm_check=true) {
				
		if(!function_exists("bartlby_version")) {
			$dl_ret=@dl("bartlby.so");	
			if(!$dl_ret) {
				echo "Bartlby php module isn't either compiled in nor the shared variant was found!!!";
				exit;	
			}
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
		
			if(!$pid_is || !file_exists("/proc/" . $pid_is . "/cmdline")) {
						
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
				
				$ads=bartlby_add_service($this->CFG, $server, $re[$x][plugin],$re[$x][service_name],$re[$x][plugin_arguments],$re[$x][notify_enabled],$re[$x][hour_from], $re[$x][hour_to], $re[$x][min_from], $re[$x][min_to],$re[$x][check_interval],$re[$x][service_type],$re[$x][service_var], $re[$x][service_passive_timeout], $re[$x][service_check_timeout], $re[$x][service_ack], $re[$x][service_retain]);
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
				if($re[$x][__install_perf_default]) {
					$msg .= str_repeat("&nbsp;", 20) . "Installing perf handler (default): " . $re[$x][plugin] . "<br>";	
					
					if(!file_exists($perf_dir . "/defaults/" . $re[$x][plugin] . ".rrd") || $force_perf == "checked") {
						$perf=@fopen($perf_dir . "/defaults/" . $re[$x][plugin] . ".rrd", "wb");
						if($perf){
							fwrite($perf, $re[$x][__install_perf_default]);
							fclose($perf);
							@chmod($perf_dir . "/defaults/" . $re[$x][plugin] . ".rrd", 0777);
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
	function XMLBoxHealth(&$status, &$remote_data) {
		
		$hosts_up=0;
		$hosts_down=0;
		$services_critical=0;
		$services_ok=0;
		$services_warning=0;
		$services_unkown=0;
		$services_downtime=0;
		$all_services=0;
		$acks_outstanding=0;
		for($rx=0; $rx<=count($remote_data);$rx++) {
			$servers=$remote_data[$rx];
			$hosts_sum +=count($servers);
			
			while(list($k,$v)=@each($servers)) {
				$x=$k;
				if($this->isServerUp($x, $servers)) {
					$hosts_up++;	
				} else {
					$hosts_down++;	
					$hosts_a_down[$k]=1;
					
				}
				
				
				for($y=0; $y<count($v); $y++) {
					
					$qck[$v[$y][server_name]][$v[$y][current_state]]++;	
					$qck[$v[$y][server_name]][10]=$v[$y][server_id];
					$qck[$v[$y][server_name]][server_icon]=$v[$y][server_icon];
					if($v[$y][is_downtime] == 1) {
						$qck[$v[$y][server_name]][$v[$y][current_state]]--;
						$qck[$v[$y][server_name]][downtime]++;
						
					}
					if($v[$y][service_ack] == 2) {
						$qck[$v[$y][server_name]][acks]++;	
						$acks_outstanding++;
						
					}
					
					
					$all_services++;
					switch($v[$y][current_state]) {
	     	
						case 0:
							$services_ok++;
							if($v[$y][is_downtime] == 1) {
								$services_ok--;
								$services_downtime++;	
							}
						break;
						case 1:
							$services_warning++;
							if($v[$y][is_downtime] == 1) {
								$services_warning--;
								$services_downtime++;	
							}
						break;
						case 2:
							$services_critical++;
							if($v[$y][is_downtime] == 1) {
								$services_critical--;
								$services_downtime++;	
							}
						break;
						
						default:
							$services_unkown++;
							if($v[$y][is_downtime] == 1) {
								$services_ok--;
								$services_downtime++;	
							}
						
						
					}	
				}
				
				
			}
		}
		
		$service_sum=$all_services-$services_downtime;
		
		
		
		
	
		if($service_sum == 0) {
			$criticals=100;
		} else {
			$criticals=(($service_sum-$services_ok) * 100 / $service_sum);
		}
	
		$proz=100-$criticals;
		
		
		
		
		$prozent_zahl = floor($proz);
		$prozent_float = number_format($proz, 1); 
		$prozent_crit_zahl = floor($criticals);
		$prozent_crit_float = number_format($criticals, 1); 
		
		$color="green";
		
		if($prozent_float <= 60) {
			$color="red";	
		} else if($prozent_float <= 90) {
			$color="yellow";	
		} else if($prozent_float <= 80) {
			$color="red";	
		} else {
			$color="green";
		}
	
		$bar=$prozent_float . "% Ok - $prozent_crit_float % Critical";
		$r[prozent_float] = $prozent_float;
		$r[color] = $color;
		$r[qck]=$qck;
		
		
		$r[services_critical]=$services_critical;
		$r[services_warning]=$services_warning;
		$r[services_ok]=$services_ok;
		$r[services_downtime]=$services_downtime;
		$r[hosts_up]=$hosts_up;
		$r[hosts_down]=$hosts_down;
		$r[acks_outstanding]=$acks_outstanding;
		$r[service_sum] = $service_sum;
		$r[hosts_sum] = $hosts_sum;
		
		return $r;
		
	
	}
	function appendXML_to_svc_map($xml_data,$alias, &$map, $xml_id) {
		
		for($x=0; $x<count($xml_data); $x++) {
			
			while(list($k, $v) = each($xml_data[$x])) {
				for($y=0; $y<count($v); $y++) {
					if(!is_array($map["XML:" . $xml_id . ":" . $k])) {
						$map["XML:" . $xml_id . ":" . $k]=array();
					}
					$v[$y][server_name] = $alias . "-->" . $v[$y][server_name];
					$v[$y][server_id] = "XML:" . $xml_id . ":" . $v[$y][server_id];
					$v[$y][service_id] = "XML:" . $xml_id . ":" . $v[$y][service_id];
					$v[$y][shm_place] = "XML:" . $xml_id . ":" . $v[$y][shm_place];
					
					 array_push($map["XML:" . $xml_id . ":" . $k], $v[$y]);
				}
			}
		}
	}
	
	function XMLQuickView($status, $qck, $xml_id) {
		$quick_view="<table width=760>";
		while(list($k, $v)=@each($qck)) {
			
			if($k != $last_qck) {
				$cl="";
				$STATE="UP";
				if ($hosts_a_down[$qck[$k][10]] == 1) {
					$cl="";
					$STATE="DOWN";
				}
				$quick_view .= "<tr>";
				$quick_view .= "<td class=$cl><img src='server_icons/" . $qck[$k][server_icon] . "'><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "'>$k</A></td>";
				$quick_view .= "<td class=$cl><font size=1>$STATE</td>";
				$quick_view .= "<td class=$cl><table width=100>";
				
				$sf=false;
				if($qck[$k][0]) {
					$sf=true;
					$qo="<tr><td class=green_box><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "&expect_state=0'>" . $qck[$k][0] . " OK's</A></td></tr>";
				}
				if($qck[$k][1]) {
					$sf=true;
					$qw="<tr><td class=orange_box><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "&expect_state=1'>" . $qck[$k][1] . " Warnings</A></td></tr>";
				}
				
				if($qck[$k][2]) {
					$sf=true;
					$qc="<tr><td class=red_box><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "&expect_state=2'>" . $qck[$k][2] . " Criticals</A></td></tr>";
				}
				
				if($qck[$k][3]) {
					$sf=true;
					$qk="<tr><td class=silver_box><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "&expect_state=3'>" . $qck[$k][3] . " Unkown</A></td></tr>";
				}
				if($qck[$k][4]) {
					$sf=true;
					$qk="<tr><td class=silver_box><font size=1>" . $qck[$k][4] . " Info</td></tr>";
				}
				if($qck[$k][downtime]) {
					$qk="<tr><td class=silver_box><font size=1>" . $qck[$k][downtime] . " Downtime</td></tr>";
				}
				if($qck[$k][acks]) {
					$qk="<tr><td class=silver_box><font size=1><a href='services.php?server_id=XML:" . $xml_id . ":" . $qck[$k][10] . "&expect_state=2&acks=yes'>" . $qck[$k][acks] . " Ack Wait</A></td></tr>";
				}
						
					$quick_view .= "$qo";
					$quick_view .= "$qw";
					$quick_view .= "$qc";
					$quick_view .= "$qk";
				$quick_view .= "</table></td>";
				$quick_view .= "</tr>";
				$quick_view .= "<tr><td colspan=3><hr noshade></td></tr>";
			}
			
			$last_qck=$k;	
			$qo="";
			$qw="";
			$qc="";
			$qk="";
		}
		
		$quick_view .= "</table>";
		
		return $quick_view;		
	}
	function XMLRemoteConfig($xml_id, $file, $var) {
		include_once("IXR_Library.inc.php");
		
		$url=bartlby_config("ui-extra.conf", "xml_remote[" . $xml_id. "]");
		$alias=bartlby_config("ui-extra.conf", "xml_alias[" . $xml_id. "]");
		if(preg_match("/http:\/\/(.*):(.*)@(.*):([0-9]+)\/(.*)/i", $url, $match)) {
			$uname=$match[1];
			$pw=$match[2];
			$port=$match[3];
			$e_url="http://" . $match[3] . "/" . $match[5];
			
		}
			
		$client = new IXR_ClientMulticall($e_url, false, $port, $uname, $pw);
		$client->debug=false;
		$client->addCall('bartlby.config', $file, $var);	
		$client->query();
		$response = $client->getResponse();
		
		
		return $response[0][0];
	}	
	function remoteServerByID($xml_id, $server_id) {
		include_once("IXR_Library.inc.php");
		
		$url=bartlby_config("ui-extra.conf", "xml_remote[" . $xml_id. "]");
		$alias=bartlby_config("ui-extra.conf", "xml_alias[" . $xml_id. "]");
		if(preg_match("/http:\/\/(.*):(.*)@(.*):([0-9]+)\/(.*)/i", $url, $match)) {
			$uname=$match[1];
			$pw=$match[2];
			$port=$match[3];
			$e_url="http://" . $match[3] . "/" . $match[5];
			
		}
			
		$client = new IXR_ClientMulticall($e_url, false, $port, $uname, $pw);
		$client->debug=false;
		$client->addCall('bartlby.get_server_by_id', $server_id);	
		$client->query();
		$response = $client->getResponse();
		
		$response[0][0][server_name] = $alias . "-->" . $response[0][0][server_name];
		$response[0][0][server_id] = "XML:" . $xml_id . ":" . $response[0][0][server_id];
		$response[0][0][service_id] = "XML:" . $xml_id . ":" . $response[0][0][service_id];
		$response[0][0][shm_place] = "XML:" . $xml_id . ":" . $response[0][0][shm_place];
		
		
		return $response[0][0];
	}
	function remoteServiceByID($xml_id, $service_id) {
		include_once("IXR_Library.inc.php");
		
		$url=bartlby_config("ui-extra.conf", "xml_remote[" . $xml_id. "]");
		$alias=bartlby_config("ui-extra.conf", "xml_alias[" . $xml_id. "]");
		if(preg_match("/http:\/\/(.*):(.*)@(.*):([0-9]+)\/(.*)/i", $url, $match)) {
			$uname=$match[1];
			$pw=$match[2];
			$port=$match[3];
			$e_url="http://" . $match[3] . "/" . $match[5];
			
		}
			
		$client = new IXR_ClientMulticall($e_url, false, $port, $uname, $pw);
		$client->debug=false;
		$client->addCall('bartlby.get_service', $service_id);	
		$client->query();
		$response = $client->getResponse();
		
		$response[0][0][server_name] = $alias . "-->" . $response[0][0][server_name];
		$response[0][0][server_id] = "XML:" . $xml_id . ":" . $response[0][0][server_id];
		$response[0][0][service_id_real] = $response[0][0][service_id];
		$response[0][0][service_id] = "XML:" . $xml_id . ":" . $response[0][0][service_id];
		$response[0][0][shm_place] = "XML:" . $xml_id . ":" . $response[0][0][shm_place];
		
		
		return $response[0][0];
	}
	function getRemoteStatus($url, $alias) {
		//Check for IXR
		//Call bartlby_info -> remote
		//call svc_map
		//return percentage
		//return quick_view
		//return svc_array
		
		include_once("IXR_Library.inc.php");
		
		//get uname and pw
		if(preg_match("/http:\/\/(.*):(.*)@(.*):([0-9]+)\/(.*)/i", $url, $match)) {
			$uname=$match[1];
			$pw=$match[2];
			$port=$match[3];
			$e_url="http://" . $match[3] . "/" . $match[5];
		
			$client = new IXR_ClientMulticall($e_url, false, $port, $uname, $pw);
			$client->debug=false;
			$client->addCall('bartlby.get_info');	
			$client->addCall('bartlby.get_service_map');	
			$client->query();
			$response = $client->getResponse();
			if(!$response) {
				return false;	
			}
			$r_array[info]=$response[0];
			$r_array[services]=$response[1];
			$r_array[url]=$url;
			$r_array[alias]=$alias;
			
			
			
			return $r_array;
			
		}
		
			
	}
	
	function create_report_img($map, $from, $to) {
		$im = @ImageCreate (900, 320)  or die ("GD Errror");
		$background_color = ImageColorAllocate ($im, 255, 255, 255);
		$green = ImageColorAllocate($im,0,255,0);
		$orange = ImageColorAllocate($im,255,255,0);
		$red = ImageColorAllocate($im,255,0,0);
		$black = ImageColorAllocate($im,0,0,0);
		
		imagefilledrectangle($im, 0, 320,900,0, $background_color);
		//Default all is green
		imagefilledrectangle($im, 10, 210,890,10,  $green); 
		
		echo "FROM: $from TO: $to <br>";
		$step=($to-$from);
		echo "step: $step <br>";
		if($step == 0) {
			//One day graphic
			$step=86400/890;	
		} else {
			$step = $step / 890;	
		}
		imagestringup($im, 1, 10, 320, date("d.m.Y H:i:s", $from), $black);
		for($xy=0; $xy<count($map);$xy++) {
			
			if($map[$xy][state] != 0) {
				$width=(($map[$xy][end]-$map[$xy][start]) / 890)* $step;
				$startb = (($map[$xy][start] - $from)) / 890;
				
				$width = round($width);
				$startb = round($startb);
				
				
				echo "<br>BLOCK: " . $map[$xy][state] . "===> start: " . $startb . "px " . $width . "<br>";
				//imagefilledrectangle ($im, (10+$starb), 210,($width+$startb+10),20,  $red); 
				$starty=10+$startb;
				$startx=10+$startb+$width;
				echo "y: " . $starty . " X: " . $startx . "<br>";
				imagefilledrectangle ($im, $starty, 210,$startx,20,  $red); 
				imagestringup($im, 1, $starty, 320, date("d.m.Y H:i:s", $map[$xy][start]), $black);
			}
		}
		imagestringup($im, 1, 890, 320, date("d.m.Y H:i:s", $to), $black);
		
		
		
		imagePNG($im, "/var/www/htdocs/test.png");
   		imagedestroy($im);

	}
}
?>
<?
session_start();

set_time_limit(0);
set_magic_quotes_runtime(0);
define("BARTLBY_UI_VERSION", "1.31");

if(!version_compare(phpversion(), "5.0.0", ">=")) {
	echo "you should have at least a php5 series";
	exit;	
}

class BartlbyUi {
		
	function doReload() {
		bartlby_reload($this->CFG);
		while(1) {
			$x++;
			$i = @bartlby_get_info($this->CFG);
			flush();
			
			if($i[do_reload] == 0) {
				$msg = "Done";
				//$layout->OUT .= "<script>doReloadButton();</script>";
				break;	
			}
		}
	}
	function resolveGroupString($str) {
		$aa=explode("|", $str);
		for($aax=0; $aax<count($aa); $aax++) {
			$bb = explode("=", $aa[$aax]);
			if($aa[$aax]) {
				$svc = @bartlby_get_service_by_id($this->CFG, $aa[$aax]);
				$r .= "Service: $svc[server_name]:$svc[client_port]/$svc[service_name] is not allowed to be in <font color=" . $this->getColor($bb[1]) . ">" . $this->getState($bb[1]) . "</font> (Current: <font color=" . $this->getColor($svc[current_state]) . ">" . $this->getState($svc[current_state]) . "</font>)<br>";
			}
		}	
		return $r;
	}
	function isSuperUser() {
		if($this->rights[super_user][0] != "true") {
			return false;
		}else {
			return true;	
		}
		
	}
	function dnl($i) {
		return sprintf("%02d", $i);
	}
	function BartlbyUi($cfg, $auth=true, $shm_check=true) {
				
		if(!function_exists("bartlby_version")) {
			$dl_ret=@dl("bartlby.so");	
			if(!$dl_ret) {
				echo "Bartlby php module isn't either compiled in nor the shared variant was found!!!";
				exit;	
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
		
		if($auth == true && bartlby_check_shm_size($cfg) == false) {
			$this->redirectError("BARTLBY::MODULE::MISMATCH");
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
		$this->BASEDIR=@bartlby_config($this->CFG, "basedir");
		$this->PERFDIR=@bartlby_config($this->CFG, "performance_dir");
		
		
		
	}
	function getServerInfs($id, $map) {
		@reset($map);
		$re[0]=0;
		$re[1]=0;
		$re[2]=0;
		$re[downtimes]=0;
		for($x=0; $x<count($map[$id]); $x++) {
			if($map[$id][$x][is_downtime] == 1) {
				continue;	
			}
			$re[$map[$id][$x][current_state]]++;
			
		}
		
		return $re;
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
	function hasServerorServiceRight($svcid, $do_redir=true) {
		if($this->rights[super_user][0] == "true") {
			return true;	
		}
		
		$rt=false;
		$svc=bartlby_get_service_by_id($this->CFG, $svcid);
		if(!$svc) {
			$rt = false;
		}
		if($this->hasServerRight($svc[server_id], false)) {
			$rt = true;	
			
			
		}
		
		if(in_array($svcid, $this->rights[services])) {
			$rt = true;
		} 
		
		if($rt == false) {
			if($do_redir == true) {
				if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
						$this->redirectError("BARTLBY::RIGHT::MISSING", "&right=service_" . $svcid);
						exit(1);	
				}
			} else {
					return false;	
			}
		} else {
			return $rt;
		}	
		
		
		
		
		
	}
	function hasServerRight($srvid, $do_redir=true) {
		
		if($this->rights[super_user][0] == "true") {
			return true;	
		}
		
		if(!is_array($this->rights[servers])) {
			return false;
		}
	
		settype($srvid, "integer");
		
		
		
		if(in_array($srvid, $this->rights[servers])) {
			return true;	
		}
		if($do_redir == true) {
			if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
					$this->redirectError("BARTLBY::RIGHT::MISSING", "&right=server_" . $srvid);
					exit(1);	
			}
		} else {
				return false;	
		}
	}
	function hasRight($k,$do_redir=true) {
		if($this->rights[super_user][0] == "true") {
			
			return true;	
		}
		
		if($this->rights[$k] && $this->rights[$k][0] != "false") {
			
				return true;
		} else {
			if($do_redir == true) {
				if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
					$this->redirectError("BARTLBY::RIGHT::MISSING", "&right=" . $k);
					exit(1);	
				}
			} else {
				return false;	
			}
		}	
	}
	function loadForeignRights($user) {
		if(!file_exists("rights/" . $user . ".dat")) {
			copy("rights/template.dat", "rights/" . $user . ".dat");
		}
		if(file_exists("rights/" . $user . ".dat")) {
			$fa=file("rights/" . $user . ".dat");
			while(list($k, $v) = each($fa)) {
				$s1=explode("=", $v);
				$r[$s1[0]]=explode(",", trim($s1[1]));
				
			}
			for($x=0; $x<count($r[services]); $x++) {
					settype($r[services][$x], "integer");
			}
			for($x=0; $x<count($r[servers]); $x++) {
					settype($r[servers][$x], "integer");
			}
		} else {
			if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
				$this->redirectError("BARTLBY::RIGHT::FILE::NOT::FOUND");
				exit(1);	
			}
		}
		if($r[servers][0] == 0) {
			$r[servers]=null;
		}
		
		if($r[services][0] == 0) {
			
			$r[services]=null;
		}
		
		// if is super_user ALL services and servers are allowed
		return $r;
		
	}
	function loadRights() {
		if(!file_exists("rights/" . $this->user . ".dat")) {
			copy("rights/template.dat", "rights/" . $this->user . ".dat");
		}
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
		} else {
			if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
				$this->redirectError("BARTLBY::RIGHT::FILE::NOT::FOUND");
				exit(1);	
			}
		}
		if($this->rights[servers][0] == "0") {
			if($this->rights[services][0] ==  "0") {
				$this->rights[servers]=null;
			} else {
				$this->rights[servers][0]=-1;
			}
		}
		
		if($this->rights[services][0] ==  "0") {
			if($this->rights[servers][0] ==  "0") {
				$this->rights[services]=null;
			} else {
				$this->rights[services][0]=-1;	
			}
		}
		
		// if is super_user ALL services and servers are allowed
		
		if($this->user == @bartlby_config("ui-extra.conf", "super_user") || $this->rights[super_user][0] == "true") {
		
				$this->rights[services]=null;
				$this->rights[servers]=null;
				$this->rights[super_user][0]=true;
		}
		
	}
	function getRelease() {
		return $this->release;	
	}
	function getInfo() {
		return @bartlby_get_info($this->CFG);	
	}
	
	function perform_auth($a=true) {
		$wrks=$this->GetWorker(false);
		$auted=0;
		if($a==false) {
			$auted=1;
		} else {
			
			while(list($k, $v) = each($wrks)) {
				if($_SESSION[username] != "" && $_SESSION[password] != "") {
					
					$_SERVER[PHP_AUTH_USER]=$_SESSION[username];
					$_SERVER[PHP_AUTH_PW]=$_SESSION[password];
				}
				if($_SERVER[PHP_AUTH_USER] == $v[name] && (md5($_SERVER[PHP_AUTH_PW]) == $v[password] || $_SERVER[PHP_AUTH_PW] == $v[password])) {
					//FIXME: remove back. comp. to plain pass'es
					$auted=1;
					$this->user_id=$v[worker_id];
				}
			}
		}
		if($auted == 0 && $_SESSION[username] != "") {
			$this->redirectError("BARTLBY::LOGIN");
		}
		if ($auted==0) { 
			
			 session_destroy();
	      		 @header("WWW-Authenticate: Basic realm=\"Bartlby Config Admin\"");	
	      		 @Header("HTTP/1.0 401 Unauthorized");
	      		 $this->_log("Login attempt from " . $_SERVER[REMOTE_ADDR] . " User: '" . $_SERVER[PHP_AUTH_USER] . "'  Pass: '" . $_SERVER[PHP_AUTH_PW] . "'"); 
			 $this->redirectError("BARTLBY::LOGIN");
			 exit;
		} else {
			$this->user=$_SERVER[PHP_AUTH_USER];
			$this->pw=$_SERVER[PHP_AUTH_PW];
			
			
			
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
			@chmod($logfile, 0666);
			
		}
	}
	function redirectError($msg, $qs="") {
		
		//header("Location: error.php?msg=" . $msg);	
		
		if(!preg_match("/error.php/" , $_SERVER[SCRIPT_NAME])) {
			echo "<script>parent.location.href='error.php?msg=$msg" . $qs . "';</script>";
			exit;
		}
		
	}
	function findSHMPlace($svcid) {
		$map=bartlby_svc_map($this->CFG, $this->rights[services], $this->rights[servers]);
		
		
		
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
	function GetWorker($do_check=true) {
		
		$r=array();
		for($x=0; $x<$this->info[workers]; $x++) {
			$wrk=bartlby_get_worker($this->CFG, $x);
			if($wrk[name] == "") {
				$x=0;
				continue;	
			}
			if($do_check == true) {
				if($wrk[name] != $this->user && !$this->hasRight("modify_all_workers", false)) {
					continue;
				}
			}
			//$r[$wrk[worker_id]]=$wrk[name];
			array_push($r, $wrk);
		}	
		return $r;
	}
	function GetServers() {
		
		$map=bartlby_svc_map($this->CFG,$this->rights[services], $this->rights[servers]);
		
		
		
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
		$map=bartlby_svc_map($this->CFG, $this->rights[services], $this->rights[servers]);
		
		
		
		for($x=0; $x<count($map); $x++) {
			//$servers[$map[$x][server_id]] = $map[$x][server_name];
			array_push($ar, $map[$x]);
		}
		return $ar;
			
		
	}
	function GetSVCMap($state=false) {
		//array(2555, 3191,2558)
		#view_service_output
		$has_right = $this->hasRight("view_service_output", false);
		
		$r=bartlby_svc_map($this->CFG, $this->rights[services], $this->rights[servers]);
        
        	
        	//Re order map ;-)
        	
        	
        	for($x=0; $x<count($r); $x++) {
        		if(!is_array($map[$r[$x][server_id]])) {
        			$map[$r[$x][server_id]] = array();
        			
        		}
        		if(!$has_right) {
        			$r[$x][new_server_text] = "you are missing: view_service_output right";	
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
			default:
			return "UNKOWN($state)";
			
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
	function getExtensionsReturn($method, $layout, $ign=false) {
		$r=array();
		$dhl = opendir("extensions");
		while($file = readdir($dhl)) {
			if($file != "." && $file != ".." && !preg_match("/.*\.disabled/", $file)) {
				if($ign == false && file_exists("extensions/" .  $file . ".disabled")) {
						continue;
				}
				@include_once("extensions/" . $file . "/" . $file . ".class.php");
				
				
				if (class_exists($file)) {
					eval("\$clh = new " . $file . "();");
					if(method_exists($clh, $method)) {
						eval("\$o = \$clh->" . $method . "();");
						$ex[ex_name]=$file;
						$ex[out] = $o;
						
						if($o != "") {
							array_push($r, $ex);
							
							if(!file_exists("extensions/" . $file . ".disabled")) {
								$endis="<tr><td colspan=2 align=right><a href='bartlby_action.php?action=disable_extension&ext=$file' title='$file extension is enabled click to change'><img border=0 src='images/extension_enable.gif'></A></td></tr>";
							} else {
								$endis="<tr><td colspan=2 align=right><a href='bartlby_action.php?action=enable_extension&ext=$file' title='$file extension is disabled click to change'><img border=0 src='images/extension_disable.gif'></A></td></tr>";	
							}
							
							
							$info_box_title='Extension: ' . $this->wikiLink("ui-extensions:" . $ex[ex_name], $ex[ex_name]);  
							// (<i>Logged in as:</i><font color="#000000"><b>' . $btl->user . '</b></font>) Uptime: <font color="#000000">' . $btl->intervall(time()-$btl->info[startup_time]) . '</font>'
							$core_content = "<table  width='100%'>
								<tr>
									<td colspan=2>" . $ex[out] .  "</td> 
								</tr>
								$endis
								
								
								
							</table>";
							
							
							@$layout->push_outside($layout->create_box($info_box_title, $core_content));
						}
								
						
						
					}
					
					
				}
			}
		}
		closedir($dhl);
		return $r;	
		
	}
	function wikiLink($page_name, $display) {
		return "<a target='_blank' href='http://wiki.bartlby.org/dokuwiki/doku.php?id=" . $page_name . "'>" . $display . "</A>";	
	}
	function installPackage($pkg, $server, $force_plugin, $force_perf, $my_path="") {
		$basedir=bartlby_config($this->CFG, "basedir");
		
		
		if($basedir) {
			$perf_dir=$basedir . "/perf/";	
		}
		
		$plugin_dir=bartlby_config($this->CFG, "agent_plugin_dir");
		
		
		
		
		$msg = "Installing package '$pkg' on Server:  $server<br>";
		if($my_path == "") {
			$fp=@fopen("pkgs/" . $pkg, "r");
		} else {
			$fp=@fopen($my_path . $pkg, "r");	
		}
		if($fp) {
			while(!feof($fp)) {
				$bf .= fgets($fp, 1024);	
			}
			$re=unserialize($bf);
			fclose($fp);
			for($x=0; $x<count($re); $x++) {
				$msg .= "Installing Service: <b>" . $re[$x][service_name] . "</b><br>";	
				
				$tfrom=$this->dnl($re[$x][hour_from]) . ":" . $this->dnl($re[$x][min_from]) . ":00";
				$tto=$this->dnl($re[$x][hour_to]) . ":" . $this->dnl($re[$x][min_to]) . ":00";
				
				$msg .= str_repeat("&nbsp;", 20) . "Plugin:" . $re[$x][plugin] . "/'" . $re[$x][plugin_arguments] . " '<br>";	
				$msg .= str_repeat("&nbsp;", 20) . "Time: $tfrom - $tto / " . $re[$x][check_interval] . "<br>";	
				$msg .= str_repeat("&nbsp;", 20) . "Service Type: " . $re[$x][service_type] . "<br>";
				
				$ads=bartlby_add_service($this->CFG, $server, $re[$x][plugin],$re[$x][service_name],$re[$x][plugin_arguments],$re[$x][notify_enabled],$re[$x][hour_from], $re[$x][hour_to], $re[$x][min_from], $re[$x][min_to],$re[$x][check_interval],$re[$x][service_type],$re[$x][service_var], $re[$x][service_passive_timeout], $re[$x][service_check_timeout], $re[$x][service_ack], $re[$x][service_retain],$re[$x][service_snmp_community], $re[$x][service_snmp_objid],$re[$x][service_snmp_version],$re[$x][service_snmp_warning],$re[$x][service_snmp_critical],$re[$x][service_snmp_type], $re[$x][service_active], $re[$x][flap_seconds]);
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
			$client->debug=true;
			$client->addCall('bartlby.get_info');	
			$client->addCall('bartlby.get_service_map');	
			$client->query();
			$client->debug=false;
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
	
function create_package($package_name, $in_services = array(), $with_plugins, $with_perf, $my_path="") {
		$pkg=array();
		$basedir=bartlby_config($this->CFG, "basedir");
		if($basedir) {
			$perf_dir=$basedir . "/perf/";	
		}
		$plugin_dir=bartlby_config($this->CFG, "agent_plugin_dir");
		
		if($in_services) {
			
			//$msg = "Creating package: " . $_GET[package_name] . "<br>";
			for($x=0; $x<$this->info[services]; $x++) {
				$svc=bartlby_get_service($this->CFG, $x);
				$svc=bartlby_get_service_by_id($this->CFG, $svc[service_id]);
				
				if(@in_array($svc[service_id], $in_services)) {
					
					$re .="<li>" . $svc[server_name] . ":" . $svc[client_port] . "/" . $svc[service_name];
					
					if($with_plugins) {
						
						if(file_exists($plugin_dir . "/" . $svc[plugin])) {
							$svc[__install_plugin]="";	
							$fp = fopen($plugin_dir . "/" . $svc[plugin], "rb");
							if($fp) {
								while(!feof($fp)) {
									$svc[__install_plugin] .= fgets($fp, 1024);
								}
								fclose($fp);
								$re .= "<li> ---> added plugin " . $svc[plugin] . " to package <br>";
							} else {
								$re .= " Plugin open failed (" . $svc[plugin] . ")<br>";
							}
							
							
						}
					}
					if($with_perf) {
						
						if(file_exists($perf_dir . "/" . $svc[plugin])) {
							$svc[__install_perf]="";	
							$fp1 = fopen($perf_dir . "/" . $svc[plugin], "rb");
							if($fp1) {
								while(!feof($fp1)) {
									$svc[__install_perf] .= fgets($fp1, 1024);
								}
								fclose($fp1);
								$re .= "<li> ---> added perf handler " . $svc[plugin] . " to package <br>";
							} else {
								$re .= " Plugin open failed (" . $svc[plugin] . ")<br>";
							}
							
							
						}
						if(file_exists($perf_dir . "/defaults/" . $svc[plugin] . ".rrd")) {
							$svc[__install_perf_default]="";	
							$fp1 = fopen($perf_dir . "/defaults/" . $svc[plugin] . ".rrd", "rb");
							if($fp1) {
									while(!feof($fp1)) {
										$svc[__install_perf_default] .= fgets($fp1, 1024);
									}
									fclose($fp1);
									$re .= "<li> ---> added perf handler (default) " . $svc[plugin] . ".rrd to package <br>";
							} else {
								$re .= " Plugin open failed (" . $svc[plugin] . ")<br>";
							}
							
							
						}						
					}
					
					
					array_push($pkg, $svc);
				}
				
			}
			$save=serialize($pkg);
			if($my_path == "") {
				$fp=@fopen("pkgs/" . $package_name, "w");
			} else {
				$fp=@fopen($my_path . $package_name, "w");	
			}
			if($fp) {
				fwrite($fp, $save);
				fclose($fp);
			} else {
				$re = "save failed";	
			}
		}
		
		
			return $re;	
	}
	function getserveroptions($defaults, $layout) {
		$modify = "<a href='modify_server.php?server_id=" . $defaults[server_id] . "'><img src='images/modify.gif' title='Modify this server' border=0></A>";
		$copy = "<a href='modify_server.php?copy=true&server_id=" . $defaults[server_id] . "'><img src='images/edit-copy.gif' title='Copy (Create a similar) this Server' border=0></A>";
		$logview= "<a href='logview.php?server_id=" . $defaults[server_id]. "' ><font size=1><img  title='View Events for this Server' src='images/icon_view.gif' border=0></A>";
		
		if($defaults[server_enabled] == 1) {
			$check = "<a title='Disable Checks for this server' href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_server'><img src='images/enabled.gif'  border=0></A>";
		} else {
			$check = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_server'><img src='images/diabled.gif' title='Enable  Checks for this server' border=0></A>";
		}
		if($defaults[server_notify] == 1) {
			$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_notify_server'><img src='images/notrigger.gif' title='Disable Notifications for this Server' border=0></A>";
		} else {
			$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_notify_server'><img src='images/trigger.gif' title='Enable Notifications for this Server' border=0></A>";
		}
		
		
		return $notifys . " " .  $check . " " . $modify . " " . $copy . " " . $logview;
	}
	function updatePerfHandler($srvId, $svcId) {
		$perf_dir=bartlby_config($this->CFG,"performance_dir");
		$btlhome=bartlby_config($this->CFG, "basedir");
		
		if(!$perf_dir) {
			$r ="'performance_dir' not set in bartlby core config file";	
		} else {
			
			$idx=$this->findSHMPlace($svcId);
			$r=$idx;
			$svc=bartlby_get_service($this->CFG, $idx);
			$cmd=$perf_dir . "/" . $svc[plugin];
			if(!file_exists($cmd)) {
				$r="Perfhandler '$cmd' does not exists";
			} else {
				
				$exec="export BARTLBY_CURR_SERVICE=\"" . $svc[service_name] . "\"; export BARTLBY_CURR_HOST=\"" . $svc[server_name] . "\"; export BARTLBY_CURR_PLUGIN=\"" . $svc[plugin] . "\"; export BARTLBY_HOME=\"$btlhome\"; export BARTLBY_CONFIG=\"" . $this->CFG . "\"; " . $cmd . "  graph " . $svc[service_id] . " 2>&1";
				
				$fp=popen($exec, "r");
				$output="<hr><pre>";
				while(!feof($fp)) {
					$r .= nl2br(fgets($fp));	
				}	
				pclose($fp);
				$r .="</pre><hr>";
				$r .= "<br> Perf handler called (see output above)";
			}
			
			
			
		}	
		return $r;
	}
	function getserviceOptions($defaults, $layout) {
		if($defaults[service_active] == 1) {
			$check = "<a title='Disable Checks for this Service' href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_service'><img src='images/enabled.gif'  border=0></A>";
		} else {
			$check = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_service'><img src='images/diabled.gif' title='Enable  Checks for this Service' border=0></A>";
		}
		if($defaults[notify_enabled] == 1) {
			$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=disable_notify'><img src='images/notrigger.gif' title='Disable Notifications for this Service' border=0></A>";
		} else {
			$notifys = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=enable_notify'><img src='images/trigger.gif' title='Enable Notifications for this Service' border=0></A>";
		}
		if($defaults[is_downtime] == 1) {
			$downtime="<img src='images/icon_work.gif' title='Service is in downtime (" . date("d.m.Y H:i:s", $defaults[downtime_from])  . "-" . date("d.m.Y H:i:s", $servs[$x][downtime_to]) . "): " . $defaults[downtime_notice] . "'>";	
		} else {
			$downtime="&nbsp;";
		}
		
		
		$special_menu = "<a href='javascript:void();' onClick=\"return dropdownmenu(this, event, menu" . $defaults[service_id] . ", '200px')\" onMouseout=\"delayhidemenu()\"><img title='Click to view special addons' src='images/icon_work1.gif' border=0></A>";
		$layout->OUT .= "<script>var menu" . $defaults[service_id] . "=new Array();</script>";
		$special_counter=bartlby_config("ui-extra.conf", "special_addon_ui_" . $defaults[service_id] . "_cnt");
		if($special_counter) {
			$layout->OUT .= "<script>";
			$fspc=0;
			for($spc=0; $spc<$special_counter; $spc++) {
				$spc_name=bartlby_config("ui-extra.conf", "special_addon_ui_" . $defaults[service_id] . "_[" . ($spc+1) ."]_name");
				$layout->OUT .= "menu" . $defaults[service_id] . "[" . $fspc . "]='<br>$spc_name<br>';\n";
				$layout->OUT .= "menu" . $defaults[service_id] . "[" . ($fspc+1) . "]='" . str_replace("^", "=", bartlby_config("ui-extra.conf", "special_addon_ui_" . $defaults[service_id] . "_[" . ($spc+1) ."]")) . "';\n";
				$fspc++;
				$fspc++;
			}
			$layout->OUT .= "</script>";
		} else {
				$special_menu="";
		}
		
		$modify = "<a href='modify_service.php?service_id=" . $defaults[service_id] . "'><img src='images/modify.gif' title='Modify this Service' border=0></A>";
		$force = "<a href='javascript:void(0);' onClick=\"xajax_forceCheck('" . $defaults[server_id] . "', '" . $defaults[service_id] . "')\"><img title='Force an immediate Check' src='images/force.gif' border=0></A>";
		$comments="<a href='view_comments.php?service_id=" . $defaults[service_id] . "'><img title='Comments for this Service' src='images/icon_comments.gif' border=0></A>";
		$logview= "<a href='logview.php?service_id=" . $defaults[service_id]. "' ><font size=1><img  title='View Events for this Service' src='images/icon_view.gif' border=0></A>";				
		$reports = "<a href='create_report.php?service_id=" . $defaults[service_id]. "' ><font size=1><img  title='Create Report' src='images/create_report.gif' border=0></A>";				
		if(file_exists($this->PERFDIR . "/" . $defaults[plugin])) {
			$stat = "<a href='bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=perfhandler_graph'><img title='Graph collected perf handler data' src='images/icon_stat.gif' border=0></A>";				
		} else {
			$stat = "";
		}
		$copy = "<a href='modify_service.php?copy=true&service_id=" . $defaults[service_id] . "'><img src='images/edit-copy.gif' title='Copy (Create a similar) this Service' border=0></A>";				
		$ret ="$notifys $check $logview $comments $modify $force $downtime $special_menu $copy $reports $stat";
		
		return $ret;
	}
}
?>
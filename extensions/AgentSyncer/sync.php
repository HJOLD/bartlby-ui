<?
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	include "extensions/AgentSyncer/AgentSyncer.class.php";
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$btl->hasRight("super_user");
	$sg = new AgentSyncer();
	$servers=$btl->GetSVCMap();
	$plugin_root=bartlby_config($btl->CFG, "agent_plugin_dir");
	//echo "PLUGIN " . $_SERVER["REMOTE_ADDR"] . " " .  $_GET[clientname] . "\n";
	
	//response with required plugins
	$client_found = 0;
	while(list($k, $v) = each($servers)) {
		//echo $v[0][server_name] . "-" .   $_SERVER["REMOTE_ADDR"] .  "-" . gethostbyname($v[0][client_ip]) . "-\n";
		if($v[0][server_name] == $_GET[clientname]) {
			$client_found=1;
			for($x=0; $x<count($v); $x++) {
				
				$distinct[$v[$x][plugin]]=1;
				
			}
		}
	}
	if($client_found == 0) {
		//Auto register?
		$auto_reg = bartlby_config("ui-extra.conf", "sync_auto_register");
		$exclude = bartlby_config("ui-extra.conf", "sync_exclude_clients");
		$default_pkg = bartlby_config("ui-extra.conf", "sync_default_pkg");
		$exclude_list=explode(",", $exclude);
		
		
		if($auto_reg == "true") {
			if(!in_array($_GET[clientname], $exclude_list)) {
				$add_server=bartlby_add_server($btl->CFG, $_GET[clientname],$_SERVER["REMOTE_ADDR"], 9030, "01generic.gif");
				echo "ADDSERVER $add_server\n";
				if(!$default_pkg) {
					$add_service=bartlby_add_service($btl->CFG, $add_server, "INIT", "Initial Check", "-h", 0, 0,24,0,59,2000,1,"",200, 20, 0, 3, "", "", "", "", "", "", 1);	
					echo "SERVICEADD INIT\n";
				} else {
					$btl->installPackage($default_pkg, $add_server, NULL, NULL);
					echo "INSTPKG $default_pkg \n";
				}
				$btl->_log("AgentSyncer: $_GET[clientname] / " . $_SERVER["REMOTE_ADDR"]  . " registered");
			} else {
				echo "INFO clientname_excluded \n";		
			}	
		} else {
			echo "INFO auto_register_off \n";	
		}
	}
	
	
	while(@list($k, $v) = @each($distinct)) {
		$fn = $plugin_root . "/" . $k;
		
		echo "PLUGIN extensions_wrap.php?script=AgentSyncer/getplugin.php&plugin=$k $k";
		if(file_exists($fn)) {
			$xy=@md5_file($fn);	
		} else {
			$xy="-";	
		}
		echo " " . $xy . "\n";
	}
	$btl->_log("AgentSyncer: $_GET[clientname] / " . $_SERVER["REMOTE_ADDR"] . " synced");
	



?>
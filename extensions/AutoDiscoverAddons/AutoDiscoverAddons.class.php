<?

include "config.php";

class AutoDiscoverAddons {
        function AutoDiscoverAddons() {
                $this->layout = new Layout();

        }


        function _About() {
                return "AutoDiscoverAddons Version 0.1 by h.januschka";
        }
        function _permissions() {
        	global $worker_rights;
        	$checked="";
        	if($worker_rights[ada_allowed][0] && $worker_rights[ada_allowed][0] != "false") {
        		$checked="checked";
        	}
        	
        	$r = "<input type=checkbox name='ada_allowed' $checked>allowed<br>";
        	return $r;	
        }
        /*
        function _overview() {
                return "_overview";
        }
        function _services() {
                return "_services";
        }
        function _processInfo() {
                return "_processInfo";
        }
        */
        /*
        function _serverDetail() {
                return "";
        }
        */
        function _globExt($svcid, $path) {
        		
                foreach(glob($path . "/" . $svcid . "_*.png") as $fn) {
                        $r .= "<img src='rrd/" . basename($fn) . "'><br>";
                }
                return $r;
        }

        function _serviceDetail() {
                global $defaults, $btl;
                if($btl->hasRight("ada_allowed", false)) {
                	$rrd_dir=bartlby_config($btl->CFG, "performance_rrd_htdocs");
                	if($rrd_dir) {
                     	   $svcid=$defaults[service_id];
                        	//see if someone has hardcoded some special_addon_stuff in ui config
                        	$svc_counter=bartlby_config("ui-extra.conf", "special_addon_ui_" . $svcid . "_cnt");
                        	if(!$svc_counter) {
                        	
                            	    return $this->_globExt($svcid, $rrd_dir);
                        	}


	                } else {
       	                 return "";
              	  }
		}


        }
}

?>

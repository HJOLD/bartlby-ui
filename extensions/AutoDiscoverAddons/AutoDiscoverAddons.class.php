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
        	  global $defaults;
        	  $x = 0;
        	  
        	  $r = "<script language='JavaScript'>
        	  	
        	  	function updatePerfhandlerExt(cnt) {
        	  		as = document.getElementById('AutoDiscoverAddonsHide');
        	  		if(as.style.display == 'block') {
        	  			return;
        	  		}
 				if (searchReq.readyState == 4 || searchReq.readyState == 0) {
					searchReq.open('GET', 'bartlby_action.php?service_id=" . $defaults[service_id] . "&server_id=" . $defaults[server_id] . "&action=perfhandler_graph', true);
					searchReq.onreadystatechange = redrawExt; 
					searchReq.send(null);
					
					as.style.display = 'block';
					
				}		
        	  	}
        	  	function redrawExt(cnt) {
        	  		st = new Date();
        	  		if (searchReq.readyState == 4) {
        	  			if(maxExt == 0) document.location.reload();
        	  			for(x=0; x<maxExt; x++) {
        	  				el = document.getElementById('perfh' + x);
        	  				el.src=el.src + '?a=1&a=' + st;
        	  				//alert('updated: ' + x);
        	  			}
        	  			as = document.getElementById('AutoDiscoverAddonsHide');
					as.style.display = 'none';
        	  		}
        	  	}
        	  </script>";
        	  
                foreach(glob($path . "/" . $svcid . "_*.png") as $fn) {
                        $r .= "<img onClick='updatePerfhandlerExt();' id='perfh" . $x . "' src='rrd/" . basename($fn) . "'><br>";
                        $x++;
                }
                
                $r = "<div id=AutoDiscoverAddonsHide style='display:none'><font color='red'><img src='extensions/AutoDiscoverAddons/ajax-loader.gif'> reload in progress....</font></div><script>var maxExt = " . $x . ";</script><a href='javascript:updatePerfhandlerExt();'>Update Perfhandler data</A><br>" . $r;
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

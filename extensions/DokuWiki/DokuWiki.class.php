<?

include "config.php";

class DokuWiki {
        function DokuWiki() {
                $this->layout = new Layout();

        }


        function _About() {
                return "DokuWiki Version 0.1 by h.januschka";
        }
        function _permissions() {
        	global $worker_rights;
        	$checked="";
        	if($worker_rights[dw_allowed][0] && $worker_rights[dw_allowed][0] != "false") {
        		$checked="checked";
        	}
        	
        	$r = "<input type=checkbox name='dw_allowed' $checked>allowed<br>";
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
	function dwDisp($p) {
	        $ifile = "extensions/DokuWiki/config.ini";
		$ra=parse_ini_file($ifile,TRUE);
		
		return "<iframe frameborder=0 src='" . $ra[DokuWiki][URL] . "?id=" . $p . "' width='100%' height='600'></iframe>";
	}

	function _serverDetail() {
                global $defaults, $btl;
                if($btl->hasRight("dw_allowed", false)) {
			return $this->dwDisp("bartlby:doku:hosts:" . $defaults[server_name] . ":01index");
		}
	}

        function _serviceDetail() {
                global $defaults, $btl;
                if($btl->hasRight("dw_allowed", false)) {

			return $this->dwDisp("bartlby:doku:hosts:" . $defaults[server_name] . ":" . $defaults[service_name] . ":01index");
		} 


        }
}

?>

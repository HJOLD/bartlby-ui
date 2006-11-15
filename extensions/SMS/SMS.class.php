<?

include "config.php";
include "bartlbystorage.class.php";

class SMS {
        function SMS() {
              $this->layout = new Layout();
		$this->storage=new bartlbyStorage("SMS");
        }


        function _About() {
                return "SMS Version 0.1 by h.januschka";
        }
        
        function mobile_nr_field($nr) {
        	return "<table width=100%><tr><td>Mobile Nummer:</td><td><input type='text' name='SMSextension_mobile_nr' value='$nr'></td></tr></table>";	
       }
        function _PRE_add_worker() {
		global $layout, $defaults, $btl;
		
			
		return $this->mobile_nr_field("");
		
	}
	function _POST_add_worker() {
		global $layout, $_GET, $defaults, $btl;
		$k = $btl->user . "_SMSextension_mobile_nr";
		$this->storage->save_key($k, $_GET["SMSextension_mobile_nr"]);
		return "Mobile number: <b>" . $_GET["SMSextension_mobile_nr"] . "</b> stored";
	}
	function _POST_modify_worker() {
		global $layout, $_GET, $defaults, $btl;
		$k = $btl->user . "_SMSextension_mobile_nr";
		$this->storage->save_key($k, $_GET["SMSextension_mobile_nr"]);
		return "Mobile number: <b>" . $_GET["SMSextension_mobile_nr"] . "</b> stored";		
	}
	function _PRE_modify_worker() {
		global $layout, $_GET, $defaults, $btl;
		$k = $btl->user . "_SMSextension_mobile_nr";
		$v = $this->storage->load_key($k);
		
		return $this->mobile_nr_field($v);	
		
		
	}
        
        
}

?>

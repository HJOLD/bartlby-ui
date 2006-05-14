<?
include "config.php";

class Bartlby_Inventory {
	function Bartlby_Inventory() {
		$this->layout = new Layout();
		
	}
	function storeServer($id, $serial, $war,$info) {
		$s[id] = $id;
		$s[serial] = $serial;
		$s[war] = $war;
		$s[info]=$info;
		
		$store=serialize($s);
		
		$fp = fopen("extensions/Bartlby_Inventory/data/" . $id . ".ser", "w");
		fwrite($fp, $store);
		fclose($fp);
		
		header("Location: ../../extensions_wrap.php?script=Bartlby_Inventory/index.php");
	}
	function getDefaults($id) {
		
		$fp = @fopen("extensions/Bartlby_Inventory/data/" . $id . ".ser", "r");
		if($fp) {
			while(!feof($fp)) {
				$bf .= fgets($fp, 1024);	
			}
			fclose($fp);
		}
		$r=unserialize($bf);
		
		return $r;
		
	}
	function _Menu() {
		$r =  $this->layout->beginMenu();
		$r .= $this->layout->addRoot("Inventory");
		$r .= $this->layout->addSub("Inventory", "Overview","extensions_wrap.php?script=Bartlby_Inventory/index.php");
		
		$r .= $this->layout->endMenu();
		return $r;
	}
	function _overview() {
		return "_overview";	
	}
	function _services() {
		return "_services";	
	}
	function _processInfo() {
		return "_processInfo";	
	}
	function _serverDetail() {
		global $defaults;
		$d=$this->getDefaults($_GET[server_id]);
		$r="<a href='extensions_wrap.php?script=Bartlby_Inventory/index.php&server_id=" . $_GET[server_id] . "'>Modify/View Inventory Details</A><br>";
		$r .= "<br><b>Serial: </b> " . $d[serial] . "<br>";
		$r .= "<b>Warranty End: </b> " . $d[war] . "<br>";
		$r .= "<b>Aditional Info: </b> " . nl2br($d[info]) . "<br>";
		
		return $r;		
	}
	function _serviceDetail() {
		global $defaults;
		
		
		return "<a href='extensions_wrap.php?script=Bartlby_Inventory/index.php&server_id=" . $defaults[server_id] . "'>Modify/View Inventory Details</A>";		
	}
}

?>

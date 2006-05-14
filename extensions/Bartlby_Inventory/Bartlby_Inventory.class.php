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
		$r .= $this->layout->addSub("Inventory", "Search","server_list.php?script=extensions_wrap.php%3fscript=Bartlby_Inventory/index.php");
		$r .= $this->layout->endMenu();
		return $r;
	}
}

?>

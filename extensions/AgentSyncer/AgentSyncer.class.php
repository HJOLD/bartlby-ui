<?
include "config.php";

	class AgentSyncer {
		function _About() {
			return "Manages the synchronization of the client binarys and plugins";	
		}
		function AgentSyncer() {
			$this->layout = new Layout();
		}
		function _Menu() {
			
			$r =  $this->layout->beginMenu();
			$r .= $this->layout->addRoot("Agent Sync");
			$r .= $this->layout->addSub("Agent Sync", "Available Binarys","extensions_wrap.php?script=AgentSyncer/view_binarys.php");
			$r .= $this->layout->addSub("Agent Sync", "Edit default config","extensions_wrap.php?script=AgentSyncer/edit_cfg.php");
			
			
			$r .= $this->layout->endMenu();
			return $r;
		}
		function _permissions() {
			global $worker_rights;
			
			$ky["sg_serverdetail"]="View group membership in serverdetail";	
			$ky["sg_overview"]="View groups in the overview";
			$ky["sg_add"]="add groups";
			$ky["sg_edit"]="modify groups";
			$ky["sg_delete"]="delete groups";
			
			while(list($k, $v) = each($ky)) {
				$kc="";
				if($worker_rights[$k][0] && $worker_rights[$k][0] != "false") {
					$kc="checked";	
				}
				$r .= "<input type=checkbox name='$k' $kc> " . $ky[$k] . "<br>";
					
			}
			return $r;
		}
	}
	
?>

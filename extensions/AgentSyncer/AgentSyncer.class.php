<?
include "config.php";

	class AgentSyncer {
		function _About() {
			return "Manages the synchronization of the client binarys and plugins";	
		}
		function AgentSyncer() {
			$this->layout = new Layout();
		}
	}
?>

<?

include "config.php";

define(GROUPS_PER_ROW, 4);

class ServerGroups {
	function ServerGroups() {
		$this->layout = new Layout();
		
	}
	function _restore() {
		global $orig_servers, $o, $bdir;	
	
		foreach(glob("extensions/ServerGroups/*.ser") as $fname) {
			@unlink($fname);
		}
		foreach(glob($bdir . "/*.ServerGroups") as $fname) {
			$mn = str_replace("ServerGroups", "ser", $fname);
			@copy($fname, $mn);			
		}
		
	}
	function _backup() {
		global $o, $bdir;	
		$o .= "ServerGroups saving to " . $bdir . "<br>";
		foreach(glob("extensions/ServerGroups/data/*.ser") as $fname) {
			clearstatcache();
   			$ta = explode(".",basename($fname));
   			
   			$unser = base64_decode($ta[0]);
   			
			$o .= $unser  . "<br>";	
			@copy($fname, $bdir . "/" . $ta[0] . ".ServerGroups");
		}
	}
	function _About() {
		return "enables you to build server groups";
			
	}
	
	function _Menu() {
		$r =  $this->layout->beginMenu();
		$r .= $this->layout->addRoot("Server Groups");
		$r .= $this->layout->addSub("Server Groups", "Overview","extensions_wrap.php?script=ServerGroups/overview.php");
		$r .= $this->layout->addSub("Server Groups", "Manage","extensions_wrap.php?script=ServerGroups/manage.php");
		
		$r .= $this->layout->endMenu();
		return $r;
	}
	
	function _overview() {
		global $servers, $btl, $quickview_disabled;
		if(!$btl->hasRight("sg_overview", false)) {
			return "you are missing 'sg_overview' right to view server groups";
		}
		
		$quickview_disabled="false";
		$r = "<table border=0>";
		$r .= "<tr>";
		reset($servers);
		
		$dhl = opendir("extensions/ServerGroups/data/");
		$c=0;
		while($file = readdir($dhl)) {
			
			if($file == "." || $file == "..") {
				continue;	
			}	
			
			$defaults=$this->load($file);
			if(!$defaults[name]) {
				continue;	
			}
			$all[0]=0;
			$all[1]=0;
			$all[2]=0;
			$c++;
			$r .= "<td align=left valign=top width=300>";					
			
			for($x=0; $x<count($defaults[servers]); $x++) {
				$ret=$btl->getServerInfs($defaults[servers][$x], $servers);	
				$all[0] += $ret[0];
				$all[1] += $ret[1];
				$all[2] += $ret[2];
				
				
				
			}
			
			//Prozenteeee
			$service_sum=($all[0]+$all[1]+$all[2]);
			if($service_sum == 0) {
				$criticals=100;
			} else {
				$criticals=(($service_sum-$all[0]) * 100 / $service_sum);
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
				$color="orange";	
			} else if($prozent_float <= 80) {
				$color="red";	
			} else {
				$color="green";
			}
			
			$r .= "<table width='100%'><tr><td><b><a href='extensions_wrap.php?script=ServerGroups/groupview.php&grpname=$file'>$defaults[name]</b></td><tr><tr><td class=" . $color . "_box>$prozent_float % OK</td></tr></table>";
			
			
			$r .= "</td>";
			if($c == GROUPS_PER_ROW) {
				$r .= "</tr><tr>";
				$c=0;	
			}
		}
		while($c < GROUPS_PER_ROW) {
			$r .= "<td>&nbsp;</td>";
			$c++;	
		}
		
		
		$r .= "</tr>";	
		$r .= "<tr>";
		$r .= "<td colspan=" . GROUPS_PER_ROW . "><a title='View All Servers' href='extensions_wrap.php?script=ServerGroups/groupview.php'><img src='extensions/ServerGroups/view_all.gif' border=0></A>";
		$r .= "</tr>";	
		$r .= "</table>";
		return $r;	
	}
	/*
	function _services() {
		return "_services";	
	}
	function _processInfo() {
		return "_processInfo";	
	}
	*/
	
	function _quickLook() {
		global $_GET, $rq, $btl;
		if(!$btl->hasRight("sg_quicklook", false)) {
			return;
		}
		$rq .= "<tr>";
		$rq .= "<td colspan=2>";
		$rq .= "<center><b>Groups</b></center>";
		$rq .= "</td></tr>";
		
		
		$svcfound=false;
		$dhl = opendir("extensions/ServerGroups/data/");
		
		while($file = readdir($dhl)) {
			
			if($file == "." || $file == "..") {
				continue;	
			}	
			
			$defaults=$this->load($file);
			if(!$defaults[name]) {
				continue;	
			}
			if(@preg_match("/" . $_GET[search] . "/i", $defaults[name])) {
				$svcfound=true;
				$rq .= "<tr><td colspan=1><a href='extensions_wrap.php?script=ServerGroups/groupview.php&grpname=$file'>" . $defaults[name] . "</A></td><td><img src='extensions/ServerGroups/group_member.gif'></td></tr>";
			}
			
		}	
			
		if($svcfound == false) {
			$rq .= "<tr><td colspan=2><i>no group matched</i></td></tr>";
		}
		return "";
	}
	
	/*
	function _serviceDetail() {
		global $defaults;
		
		
		return "<a href='extensions_wrap.php?script=SSH/index.php&server_id=" . $defaults[server_id] . "'>Modify/View Inventory Details</A>";		
	}
	*/
	
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
	
	function _serverDetail() {
		global $btl;
		if(!$btl->hasRight("sg_serverdetail", false)) {
			return;
		}
		$r = "<b>Member of following groups</b>:<br>";
		$dhl = opendir("extensions/ServerGroups/data/");
		
		while($file = readdir($dhl)) {
			
			if($file == "." || $file == "..") {
				continue;	
			}	
			
			$defaults=$this->load($file);
			
			if(@in_array($_GET[server_id], $defaults[servers])) {
				$r .= "<img src='extensions/ServerGroups/group_member.gif'><a href='extensions_wrap.php?script=ServerGroups/groupview.php&grpname=" . $file . "'>" . $defaults[name] . "</a><br>";	
			}
			
		}
		return $r;	
	}
	
	
	function delete($grpname) {
		$fname=base64_encode($grpname);
		@unlink("extensions/ServerGroups/data/" . $fname . ".ser");
		
	}
	function save($ar=array(), $path="") {
		$fname=base64_encode($ar[grpname]);
		$ser[name]=$ar[grpname];
		$ser[servers] = $ar[choosen_servers];
		if($path == "") {
			$fp = fopen("extensions/ServerGroups/data/" . $fname . ".ser", "w");
		} else {
			$fp = fopen($path. "/" . $fname . ".ser", "w");	
		}
		fwrite($fp, serialize($ser));
		fclose($fp);
		
		
	}
	function load($file, $path="") {
		if($path == "") {
			$fp = @fopen("extensions/ServerGroups/data/" . $file, "r");
		} else {
			$fp = @fopen($path . "/" . $file, "r");
		}
		if($fp) {
			while(!feof($fp)) {
				$bf .= fgets($fp, 1024);	
			}
			fclose($fp);
		}
		$r=unserialize($bf);
		
		return $r;
	}
	
}

?>

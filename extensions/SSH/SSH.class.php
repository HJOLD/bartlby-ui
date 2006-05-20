<?
include "config.php";
@dl("ssh2.so");

class SSH {
	function SSH() {
		$this->layout = new Layout();
		
	}
	
	
	function _About() {
		$snotice="You have to enable 'PasswordAuthentication' in your sshd_config<br>
		and <font color=red>be sure you you hide your extensions/SSH/data directory from you webserver<br>
		with rewriteRules or somthing similar</font>";
		if(function_exists("ssh2_connect")) {
			return "SSH2 Extensions [enabled]<br>$snotice";
		} else {
			return "SSH2 Extensions [disabled] get ssh2 support into your php <br>$snotice";
		}	
			
	}
	
	function _Menu() {
		$r =  $this->layout->beginMenu();
		$r .= $this->layout->addRoot("SSH");
		$r .= $this->layout->addSub("SSH", "Passwords","extensions_wrap.php?script=SSH/index.php");
		
		$r .= $this->layout->endMenu();
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
	function _serverDetail() {
		global $defaults;
		
		$st = $this->getDefaults($_GET[server_id]);
		
		
		if($st[ssh_user] && $st[ssh_pass]) {
			$r = "<script language='javascript'>
				function getXmlHttpRequestObject() {
					if (window.XMLHttpRequest) {
						return new XMLHttpRequest();
					} else if(window.ActiveXObject) {
						return new ActiveXObject(\"Microsoft.XMLHTTP\");
					} else {
						alert(\"Ajax? enabled?!\");
					}
				}
				var sshr = getXmlHttpRequestObject();
				function SSHExec(urll) {
					
					if (sshr.readyState == 4 || sshr.readyState == 0) {
						
						var str = escape(document.getElementById('ssh_cmd').value);
						
						sshr.open(\"GET\", urll + '?ip=" . $defaults[server_ip] . "&server_id=" .  $_GET[server_id] . "&exec=' + str, true);
						sshr.onreadystatechange = sshOutput; 
						sshr.send(null);
						document.getElementById('exe').disabled=true;
						document.getElementById('ssh_cmd').disabled=true;
						
						
					}		
				}
				function sshOutput() {
					if (sshr.readyState == 4) {
						
						var ss = document.getElementById('ssh_output');
						
						var str = sshr.responseText + ss.innerHTML;
						ss.innerHTML = str;
						document.getElementById('exe').disabled=false;
						document.getElementById('ssh_cmd').disabled=false;
						document.getElementById('ssh_cmd').value='';
						
					}
				}
				function sshKeyUp(evnt){
				
					if(evnt.wich) {
						kcode = evnt.wich;
							
					} else if (evnt.keyCode) {
						kcode = evnt.keyCode;	
					}
					
					if(kcode == 13 && document.getElementById('cape').checked) {
						//Enter
						SSHExec('extensions/SSH/exec.php');
						
					}
				}
				function sshClear() {
					var ss = document.getElementById('ssh_output')
					ss.innerHTML = '';	
				}
			</script>";
			$r .= "<a href='extensions_wrap.php?script=SSH/index.php?server_id=" .  $_GET[server_id] . "'>Manage Authentication</A><input type=button id=exe value='execute' onClick=\"SSHExec('extensions/SSH/exec.php');\"><input type=button value='clear' onClick=\"sshClear();\"><input type=checkbox value='1' id=cape checked>Capture &lt;Enter&gt;<br><textarea rows=20 cols=50 id='ssh_cmd' onKeyUp=\"sshKeyUp(event)\"></textarea>\n<div id=\"ssh_output\"></div>";
		
		} else {
			$r = "";	
		}
		return $r;		
	}
	/*
	function _serviceDetail() {
		global $defaults;
		
		
		return "<a href='extensions_wrap.php?script=SSH/index.php&server_id=" . $defaults[server_id] . "'>Modify/View Inventory Details</A>";		
	}
	*/
	
	
	function storeServer($id, $ip, $port, $user, $pass) {
		$s[id] = $id;
		$s[ssh_user] = $user;
		$s[ssh_pass] = $pass;
		$s[ssh_ip]=$ip;
		$s[ssh_port]=$port;
		
		$store=serialize($s);
		
		$fp = fopen("extensions/SSH/data/" . $id . ".ser", "w");
		fwrite($fp, $store);
		fclose($fp);
		
		header("Location: ../../extensions_wrap.php?script=SSH/index.php");
	}
	function getDefaults($id) {
		
		$fp = @fopen("extensions/SSH/data/" . $id . ".ser", "r");
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

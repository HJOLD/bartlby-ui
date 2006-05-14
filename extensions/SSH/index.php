<?
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	include "extensions/SSH/SSH.class.php";
	
	
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$inv = new SSH();
	
	$layout= new Layout();
	$layout->set_menu("SSH");
	$layout->Table("100%");
	
	$servers=$btl->getSVCMap();
	
	while(list($k, $v) = each($servers)) {
		if($_GET[server_id]) {
			if($v[0][server_id] != $_GET[server_id]) {
				continue;	
			
			}
		}
		$tac_title=$v[0][server_name];  
		$defaults=$inv->getDefaults($v[0][server_id]);
		
		
		$tac_content = "<form name='fm1' action='extensions/SSH/save.php'><table class='nopad' width='100%'>
			<tr>
				<td>SSH IP:</td>
				<td><input type=text value='" . $defaults[ssh_ip] . "' name='ssh_ip'><input type=hidden value='" . $v[0][server_id] . "' name='id'></td>
			</tr>
			<tr>
				<td>SSH Port:</td>
				<td><input type=text value='" . $defaults[ssh_port] . "' name='ssh_port'></td>
			</tr>
			<tr>
				<td>Username:</td>
				<td><input type=text value='" . $defaults[ssh_user] . "' name='ssh_user'></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type=text value='" . $defaults[ssh_pass] . "' name='ssh_pass'></td>
			</tr>
			<tr>
				
				<td colspan=2><input type=submit value='store'></td>
			</tr>
			
			
			
		</table></form>";
		$layout->push_outside($layout->create_box($tac_title, $tac_content));
	
		
		
	}
	
	$layout->TableEnd();
	$layout->display();

?>
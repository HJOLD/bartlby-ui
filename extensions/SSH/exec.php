<?

chdir("../../");
include "layout.class.php";
include "config.php";
include "bartlby-ui.class.php";
include "extensions/SSH/SSH.class.php";

$btl=new BartlbyUi($Bartlby_CONF);
$ssh=new SSH();

echo "<pre>";

$defaults=$ssh->getDefaults($_GET[server_id]);


if($defaults[ssh_ip] == "") {
	$defaults[ssh_ip]=$_GET[ip];	
}

if($defaults[ssh_port] == "") {
	$defaults[ssh_port]=22;	
}

if (!($resource=@ssh2_connect($defaults[ssh_ip], $defaults[ssh_port]))) {
   echo "[FAILED] " . $defaults[ssh_ip] . ":" . $defaults[ssh_port] . "<br />";
   exit(1);
}


if (!@ssh2_auth_password($resource,$defaults[ssh_user],$defaults[ssh_pass])) {
	echo "[FAILED authentication]<br />";
	exit(1);
}
if (!($stdio = @ssh2_shell($resource,"xterm"))) {
	echo "[FAILED shell]<br />";
	exit(1);
}

fwrite($stdio,$_GET[exec] . "\n");
$stderr = ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR);

sleep(1);

while($line = fgets($stdio)) {
        flush();
        echo $line;
}


?>
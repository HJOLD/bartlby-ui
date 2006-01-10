<?
function dnl($i) {
	return sprintf("%02d", $i);
}
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	header("Content-type: text/xml");
	
	$btl=new BartlbyUi($Bartlby_CONF,false);
	$lib=bartlby_lib_info($btl->CFG);
	$info=$btl->info;
	
	$file=$_FILES[xml_file][tmp_name];
	
	$xml_parser = xml_parser_create();
	if (!($fp = fopen($file, "r"))) {
	   	echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
		echo "<bartlby>\n";
		echo "<result code=\"-1\">\n";
			echo "<string>Authentication failed</string>\n";
		echo "</result>\n";
		echo "</bartlby>\n";	
		exit;
	}
	$contents = fread($fp, filesize($file));
	fclose($fp);
	xml_parse_into_struct($xml_parser, $contents, $arr_vals);
	xml_parser_free($xml_parser);
	
	$params=array();
	for($x=0; $x<count($arr_vals); $x++) {
		switch($arr_vals[$x][tag]) {
			
			case "FUNCTION":
				$function=$arr_vals[$x][value];
			break;
			case "PARAM":
				$o=$arr_vals[$x][value];
				
				array_push($params,$o); 
			break;
			case "USER":
				$user=$arr_vals[$x][value];
			break;
			case "PASS":
				$pass=$arr_vals[$x][value];
			break;
		}	
		
	}
	
	//AUth Here
	$wrks=$btl->GetWorker();
	$auted=0;
	while(list($k, $v) = each($wrks)) {
		if($user == $v[name] && $pass == $v[password]) {
			$auted=1;
		}
	}
	
	if($auted == 1 ) {
		$e_str=$function . "(";
		for($x=0; $x<count($params); $x++) {
			if(is_int($params[$x])) {
				$e_str .= $params[$x] . ",";	
			} else if(is_string($params[$x])) {
				$quote='"';
				if(preg_match("/array\(.*/i", $params[$x])) {
					$quote="";	
				}
				$e_str .=  $quote . $params[$x] . "" . $quote . ",";
			}	
		}
		$e_str=substr($e_str, 0, -1) . ");";
		
		$err_str="";
		set_error_handler("myErrorHandler");
		
		$funcs=get_extension_funcs("bartlby");
		if(in_array($function,$funcs)) { 		
			eval("\$out = $e_str");
		} else {
			$err_str="Function '$function' does not exist";	
		}
		
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
		echo "<bartlby>\n";
		echo "<result code=\"1\">\n";
			if($err_str != "") {
				echo output_result_type($err_str, "result");
			} else {
				echo output_result_type($out, "result");	
			}
			
		echo "</result>\n";
		echo "</bartlby>\n";	
		
		
	} else {
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
		echo "<bartlby>\n";
		echo "<result code=\"-1\">\n";
			echo "<string>Authentication failed</string>\n";
		echo "</result>\n";
		echo "</bartlby>\n";	
		
	}
	
	

function output_result_type($a, $k) {
	$r = "";
	
	switch(gettype($a)) {
		case "array":
			$r .= "<array name=\"" . $k .  "\">\n";
			while(list($k, $v) = each($a)) {
				$r .= output_result_type($v, $k);	
			}			
			$r .= "</array>\n";
		break;	
		case "string":
			$r .= "<string name=\"" . $k .  "\"><![CDATA[\n" . $a . "\n]]></string>\n";
		break;
		case "integer":
			$r .= "<integer name=\"" . $k .  "\">" . $a . "</integer>\n";
		break;
		case "double":
			$r .= "<double name=\"" . $k .  "\">" . $a . "</double>\n";
		break;
	}	
	return $r;
}

function myErrorHandler($errno, $errstr, $errfile, $errline){
	global $err_str;
	
	switch ($errno) {
		default:
  		$err_str=$errstr;
	}
}

?>
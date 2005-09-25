<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF, false);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->DisplayHelp(array(0=>"WARN|Welcome to BartlbyUI",1=>"INFO|This is the help screen"));
	$layout->MetaRefresh(30);
	$layout->Table("100%");
	$lib=bartlby_lib_info($btl->CFG);
	
	
	$is_repl_on=bartlby_config($btl->CFG, "replication");
	$repl = "<hr noshade>Replication enabled: <b>$is_repl_on</b><br>";
	if($is_repl_on == "true") {
			$repl_cnt=bartlby_config($btl->CFG, "replicate_cnt");
			$repl .="Replicating to $repl_cnt Servers every " . bartlby_config($btl->CFG, "replication_intervall") . "<br>";
			for($x=1; $x<=$repl_cnt; $x++) {
				$repl .= str_repeat("&nbsp;", 20) . " Server:" . bartlby_config($btl->CFG, "replicate[" . $x . "]") . "<br>";	
			}
			$repl .= "Last Replication was on:" . date("d.m.Y H:i:s", $btl->info[last_replication]) . "<br>";
	}
	
	$servers=$btl->GetServers();
	$hosts_sum=count($servers);
	
	while(list($k,$v)=@each($servers)) {
		$x=$k;
		if($btl->isServerUp($x)) {
			$hosts_up++;	
		} else {
			$hosts_down++;	
			$hosts_a_down[$k]=1;
			
		}
	}
	
	$service_sum=$btl->ServiceCount();
	
	$services_critical=0;
	$services_ok=0;
	$services_warning=0;
	
	for($x=0; $x<$service_sum; $x++) {
		$svc=bartlby_get_service($btl->CFG, $x);
		switch($svc[last_state]) {
			case 0:
				$services_ok++;
			break;
			case 1:
				$services_warning++;
			break;
			case 2:
				$services_critical++;
			break;
				
		}	
	}
	if($service_sum == 0) {
		$criticals=100;
	} else {
		$criticals=(($service_sum-$services_ok) * 100 / $service_sum);
	}

	$proz=100-$criticals;
	
	
	
	
	$prozent_zahl = floor($proz);
	$prozent_float = number_format($proz, 1); 
	$prozent_crit_zahl = floor($criticals);
	$prozent_crit_float = number_format($criticals, 1); 
	
	$color="green";
	
	if($prozent_float <= 60) {
		$cl[0]=255;
		$cl[1]=0;
		$cl[2]=0;	
	} else if($prozent_float <= 90) {
		$cl[0]=255;
		$cl[1]=255;
		$cl[2]=0;
	} else if($prozent_float <= 80) {
		$cl[0]=255;
		$cl[1]=0;
		$cl[2]=0;
	} else {
		$cl[0]=0;
		$cl[1]=255;
		$cl[2]=0;
	}
	
	
	


Header("Content-type: image/png");
drawRating($prozent_float, $cl, $services_ok, $services_critical, $services_warning, $btl->info);


function drawRating($rating, $cl=array(), $ok, $crit, $warn, $info=array()) {
   	if(!is_array($info)) {
   		$image = imagecreate(50,30);
   	} else {
   		$image = imagecreate(102,80);
   	}
	$back = ImageColorAllocate($image,255,255,255);
   	$font = ImageColorAllocate($image,0,0,0);
   	$border = ImageColorAllocate($image,0,0,0);
   
  // $cl = ImageColorAllocate($image,255,60,75);
   
  	$fill = ImageColorAllocate($image,$cl[0],$cl[1],$cl[2]);
  	  
  if(is_array($info)) { 
   	 
  	 $a=explode("(", $info[version]);
  	 $mv=$a[0];
  	 
  	 imagestring($image, 2, 5,0, $mv,$font); 
  	 
  	 ImageFilledRectangle($image,0,20,101,15,$back);
  	 ImageFilledRectangle($image,1,21,$rating,15,$fill);
  	 ImageRectangle($image,0,20,101,15,$border);
  	 imagestring($image, 2, 20,21, "$rating % OK",$font); 
  	 imagestring($image, 2, 5,36, "$crit Critical",$font); 
  	 imagestring($image, 2, 5,46, "$warn Warning",$font); 
  	 imagestring($image, 2, 5,56, "$ok OK",$font); 
  	 imagestring($image, 2, 5,66, "running: " . $info[current_running],$font); 
  } else {
  	imagestring($image, 2, 5,5, "Bartlby",$fill); 
  	imagestring($image, 2, 5,15, "Down!",$fill); 
  }
   imagePNG($image);
   imagedestroy($image);
}
?>
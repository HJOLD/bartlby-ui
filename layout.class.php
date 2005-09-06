<?
#////////////////////////////////////////////
#//
#// MXS(erver) Project by h.januschka
#// initatated august 2003
#// -------------------------------------
#// MultiPlayerMedia Server with the main 
#// goal to have an easy interface for 
#// Macromedia FLASH© games and Guis
#//
#//
#//
#//
#//--------------------------------------------
#// @package WAI
#// @file LAYOUT.class.php
#// @author klewan <klewan@chello.at>
#// @desc centralized layout control :)
#//
#//
#//
#////////////////////////////////////////////
class Layout {
	var $OUT;
	function microtime_float()
	{
   		list($usec, $sec) = explode(" ", microtime());
   		return ((float)$usec + (float)$sec);
	}
	function Layout($scr='') {
		$this->start_time=$this->microtime_float();
		$this->OUT .= "<html>
		<head>
		<style>
		td {font-family:verdana;  padding:2; font-size:12px; color:000000; background-color:c2cbcf}
		td.none {font-family:verdana; border: dotted #404040 0px; padding:2; font-size:12px; color:#000000; }
		td.header { padding: 4; border: dotted #404040 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#7eaec5}
		td.header1 { padding: 4; border: dotted #404040 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#c2cbcf}
		
		
		td.red { padding: 4; border: outset white 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#F83838}
		td.green { padding: 4;   border: outset white 1px; text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#33FF00}
		td.orange { padding: 4; border: outset white 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#FFFF00}
		td.blue { padding: 4; border: outset white 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#ACACAC}
		td.yellow { padding: 4; border: outset white 1px;  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:#ACACAC}
		
		td.black {  text-valign: middle;  font-family:verdana; font-size:12px; height:20px; color:000000; background-color:c2cbcf}
		
		a {font-family:verdana; font-size:12px; color:#0A246A}
		a.mine {font-family:verdana; font-size:12px; color:#ffffff}
		input { font-family: verdana; font-size:12px;}
		select { font-family: verdana; font-size:12px;}
		</style>
		</head>
		<body bgcolor=c2cbcf link=000000 text=000000 $scr>
		";
	
	}

	function Table($proz="100%") {
		$this->OUT .= "<table width='$proz' cellpadding=0 cellspacing=0 border=0>";
	}
	function MetaRefresh($time=20) {
		$this->OUT .= "<script>window.setTimeout('document.location.reload()', " . $time . "000);</script>";	
	}
	function TableEnd() {
		$this->OUT .= "</table>";	
	}
	function DisplayHelp($msg=array()) {
		for($x=0; $x<=count($msg);$x++) {
			$fin .= "msg[]=" . $msg[$x] . "&";
		}
		$this->OUT .= "<script>parent.unten.location.href='help.php?" . $fin . "';</script>";
	}
	function Td($data=array()) {
		for($x=0;$x<count($data);$x++) {
			$width="";
			$height="";
			$class="";
			$colspan="";
			$disp=$data[$x];
			$align="align=left";
			if (is_array($data[$x])) {
				if ($data[$x]["width"]) $width="width='" . $data[$x]["width"] .  "'";
				if ($data[$x]["height"]) $height="height='" . $data[$x]["width"] .  "'";
				if ($data[$x]["class"]) $class="class='" . $data[$x]["class"] .  "'";
				if ($data[$x]["colspan"]) $colspan="colspan='" . $data[$x]["colspan"] .  "'";
				if ($data[$x]["align"]) $align="align='" . $data[$x]["align"] .  "'";
			}
			
			if (is_array($data[$x])) $disp=$data[$x]["show"];
			
			$r .= "<td $colspan  $align  valign=top $width $height $class>\n" . $disp . "\n</td>\n";	
		}
		return $r;
	}
	
	function Tr($td) {
		$this->OUT .= "<tr>\n$td\n</tr>\n";	
		
	}
	function Form($name,$action) {
		$this->OUT .= "<form name='$name' action='$action' method=get>\n";	
		
	}
	function FormEnd() {
		$this->OUT .= "</form>\n";	
	}
	
	function Field($name, $type='text', $value='',$L='', $chkBox='', $help = array()) {
		$n="name='$name'";
		if($help) {
			$hIcon="<a href='help.php?msg[0]=$help&msg[1]=NULL' target='unten'><img src='info.gif' border=0></A>";
		}
		$r="<input type='$type' value='$value' $n $chkBox>$hIcon\n";
		if ($L) {
			$this->OUT .= $r;
		} else {
			return $r;
		}
		
	}
	function DropDown($name,$options=array(), $type='', $style='') {
		$r = "<select name='$name' $type $style>\n";
		for ($x=0;$x<count($options); $x++) {
			$sel="";
			if ($options[$x][s] == 1) $sel="selected";
			$r .= "<option style='background-color: " .  $options[$x][c] . "' value='" . $options[$x][v] . "' $sel>" . $options[$x][k] . "\n";	
		}		
		$r .= "</select>\n";
		return $r;
	}
	
	function display($cr="") {
		$this->end_time=$this->microtime_float();
		$diff=$this->end_time-$this->start_time;
		if (!$cr) {
			$this->OUT .= "<br><br>
			<center>
			<table><tr><td>
			( Service Monitoring ) UI<br>
			bartlby-team © 2005<br>
			<a href='http://bartlby.sourceforge.net'>bartlby.sourceforge.net</A>
			<br>
			$diff seconds
			</td></tr></table>
			
			";
		}
		if(preg_match("/lynx/i", getenv("HTTP_USER_AGENT"))) {
			$this->OUT=preg_replace("/<img .*>/", "",$this->OUT);	
		}
			echo $this->OUT;	
		
		//echo $this->create_window("MXServer", $this->OUT  ,585,'center',"<center>","</center>");
	}

function create_window($title, $text, $width=100,$walign='left', $pre='',$post='') {
		$r="
		$pre
		<table border=0  cellpadding=0 cellspacing=0>
	<tr>
		<td  style='padding:0px;border:0px;font-family:verdana; font-size:12px; color:ffffff'><img src='images/ps_export/images/corner_left_top.gif'></td>
		<td  style='padding:0px;border:0px;background-repeat: repeat-x;background-image:url(images/ps_export/images/tile_top.gif);font-family:verdana; font-size:12px; color:ffffff' width='$width'>$title</td>
		<td  style='padding:0px;border:0px;font-family:verdana; font-size:12px; color:ffffff'><img src='images/ps_export/images/corner_right_top.gif'></td>
	</tr>
	
	<tr>
		<td  style='padding:0px;border:0px;background-repeat: repeat-y;background-image:url(images/ps_export/images/tile_left.gif);font-family:verdana; font-size:12px; color:ffffff'>
		<img src='images/ps_export/images/tile_left.gif'>
		</td>
		<td  align=$walign style='padding:0px;border:0px;background-image:url(images/ps_export/images/content_bg.gif);font-family:verdana; font-size:12px; color:000000' width='$width'>
		$text
		</td>
		<td  style='padding:0px;border:0px;background-repeat: repeat-y;background-image:url(images/ps_export/images/tile_right.gif);font-family:verdana; font-size:12px; color:ffffff'>
		</td>
	</tr>
	
	<tr>
		<td  style='padding:0px;border:0px;font-family:verdana; font-size:12px; color:ffffff'><img src='images/ps_export/images/corner_left_bottom.gif'></td>
		<td  style='padding:0px;border:0px;background-repeat: repeat-x;background-image:url(images/ps_export/images/tile_bottom.gif);font-family:verdana; font-size:12px; color:ffffff' width='100'>&nbsp;</td>
		<td  style='padding:0px;border:0px;font-family:verdana; font-size:12px; color:ffffff'><img src='images/ps_export/images/corner_right_bottom.gif'></td>
	</tr>



</table>
		$post
		
		";
		return $r;
		
	}
}


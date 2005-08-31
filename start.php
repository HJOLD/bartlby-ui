<?
	include "config.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF);
?>

<HTML>
<HEAD>
<TITLE>Bartlby</TITLE>

</HEAD>

<frameset cols='10%,*' border='0' frameborder='0' framespacing='0'>
<frame src='nav.php' name='l' marginwidth='0' marginheight='0' scrolling='auto' noresize>
<frameset rows='58,*,20' border='0' frameborder='0' framespacing='0'>
<frame src='status.php' name='oben' marginwidth='0' marginheight='0' scrolling='auto' noresize>
<frame src='overview.php' name='main' marginwidth='0' marginheight='0' scrolling='auto' noresize>
<frame src='help.php' name='unten' marginwidth='0' marginheight='0' scrolling='auto' noresize>
</frameset>
<noframes>

</noframes>
</frameset>

</HTML>
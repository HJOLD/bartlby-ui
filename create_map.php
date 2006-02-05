<script>
	var layer_array = new Array();
	var active_layer=null;
	var layerCount=0;
	
	function pushLayer(la) {
		layer_array.push(la);	
		layerCount++;		
	}
	function layer_pos() {
		document.storeit.storeString.value='';
		
		for(x=0; x<layer_array.length; x++) {
			if(layer_array[x].innerHTML != "") {
				document.storeit.storeString.value += "$layer["+layer_array[x].id+"][top]='" + layer_array[x].style.top +"';\n";
				document.storeit.storeString.value += "$layer["+layer_array[x].id+"][left]='" + layer_array[x].style.left +"';\n";
				document.storeit.storeString.value += "$layer["+layer_array[x].id+"][title]='" + layer_array[x].title +"';\n";
			}
			
		}
		document.storeit.submit();
		
		
		
			
	}
	function addLayer(title, HTML) {
		var newElem = document.createElement("div"); // neues DIV-Tag erstellen
		newElem.setAttribute("style", "position:relative; width:100px; height: 100px;"); // DIV stylen
		newElem.setAttribute("id", "uniq"+layerCount); // DIV eine ID geben, damit später noch darauf zugegriffen werden kann
		newElem.innerHTML = HTML; // HTML-Code in das DIV einfügen
		newElem.title=title;
		
		//newElem.onDblClick=function() {RemoveLayer(newElem)};
		newElem.setAttribute("onDblClick", "RemoveLayer(" + newElem.id + ")", true);
		//"RemoveLayer(" + newElem + ")"
		document.getElementsByTagName("body")[0].appendChild(newElem); // DIV-Tag in den Body einfügen 
		
		
		
		Drag.init(newElem);	
		pushLayer(newElem);
		
		
		
		return newElem.id;
	}
	function addLayerForm() {
		capt=document.addfm.newCaption.value;
		ico=document.addfm.icon[document.addfm.icon.selectedIndex].value;
		
		addTitleIcon(capt, ico);
		cancelAddLayer();
	}
	function addTitleIcon(capt, ico) {
		title=capt + "/" + ico;
		return addLayer(title, "<img src='server_icons/"+ico+"'><br>" + capt);	
	}
	function showAdd() {
		cancelLayerOpts();
		var obj = document.getElementById("addNew");
        	obj.style.visibility = "visible";	
	}
	function cancelAddLayer() {
		var obj = document.getElementById("addNew");
        	obj.style.visibility = "hidden";			
	}
	function RemoveLayer(wich) {
		active_layer=wich;
		
		showLayerOpts();
	}
	function showLayerOpts() {
		cancelAddLayer();
		var obj = document.getElementById("layeropts");
        	obj.style.visibility = "visible";	
	}
	function cancelLayerOpts() {
		var obj = document.getElementById("layeropts");
        	obj.style.visibility = "hidden";	
	}
	function ModifyLayer() {
		alert("Modify: " + active_layer.id);
	}
	function RemoLayer() {
		//alert("Remove:" + active_layer.id);	
		if(active_layer.id.substring(0, 6) == "server") {
			alert("servers can't be deleted");
		} else {
			active_layer.innerHTML="";
		}
		cancelLayerOpts();
	}
	function repositionIT() {
		/* echo "var layerTop = new Array();\n";
		echo "var layerLeft = new Array();\n";
		echo "var layerTitle = new Array();\n";
		echo "var layerId = new Array();\n";
		*/
		
		for(x=0; x<layerTop.length; x++) {
			
			obj = document.getElementById(layerId[x]);	
			if(obj) {
				
				obj.style.top=layerTop[x];	
				obj.style.left=layerLeft[x];
			} else {
				//Add a new One :-)
				ar=layerTitle[x].split("/");
				
				
				obj = document.getElementById(addTitleIcon(ar[0], ar[1]));	
				obj.style.top=layerTop[x];	
				obj.style.left=layerLeft[x];
			}
		}
	}
</script>
<input type="button" onClick='layer_pos();' value="Store">
<input type="button" onClick='showAdd();' value="Add">
<?
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->setTemplate("nonav.html");
	$layout->Table("100%");
	
	$server_ico="<select name='icon'>";
	$dhl=opendir("server_icons");
	while($file = readdir($dhl)) {
		//$sr=bartlby_get_server_by_id($btl->CFG, $k);
		
		//$isup=$btl->isServerUp($k);
		//if($isup == 1 ) { $isup="UP"; } else { $isup="DOWN"; }
		if(preg_match("/.*\.[png|gif]/", $file)) {
			
			$server_ico .="<option value='$file'>$file</option>";	
			
		}
		
	}
	closedir($dhl);
	$server_ico .="</select>";
	
	$map=$btl->getSVCMap();
	while(list($server, $svcs) = each($map)) {
		if($btl->isServerUp($svcs[0][server_id], $map)) {
			$is_up="green";	
		} else {
			$is_up="red";
		}
		$layout->OUT .= "<div title='" . $svcs[0][server_name] . "/" . $svcs[0][server_icon] . "' onDblClick='RemoveLayer(this);' id='server" . $svcs[0][server_id] . "' style=\"position: relative;width:100px;height:100px\"><img  src='server_icons/" . $svcs[0][server_icon] . "'><br><font color='$is_up'>" . $svcs[0][server_name] . "</font></div>\n";	
		$layout->OUT .= '<script type="text/javascript">var hndl' . $svcs[0][server_id] . '=document.getElementById("server' . $svcs[0][server_id] . '"); Drag.init(hndl' . $svcs[0][server_id] . ', null);pushLayer(hndl' . $svcs[0][server_id] . ');</script>' . "\n";
	}
	
	$layout->TableEnd();
	$layout->display("no");
	
	//Load Defaults ;-)
	$fp=fopen("create_map.dat","r");
		while(!feof($fp)) {
			$estr .= @fgets($fp, 1024);
		}
	@fclose($fp);
	@eval($estr);
	echo "<script>\n";
	echo "var layerTop = new Array();\n";
	echo "var layerLeft = new Array();\n";
	echo "var layerTitle = new Array();\n";
	echo "var layerId = new Array();\n";
	echo "layerCount +=" . count($layer)+2 . ";";
	$cnt=0;
	while(list($k, $v) = @each($layer)) {
		echo "layerTop[$cnt] = '" . $v[top] . "';\n";
		echo "layerLeft[$cnt] = '" . $v[left] . "';\n";
		echo "layerTitle[$cnt] = '" . $v[title] . "';\n";
		echo "layerId[$cnt] = '" . $k . "';\n";
		$cnt++;
	}
	
	echo "</script>\n";
	
	
?>
<div id="bartlby" style="position: relative; left:0; top:0;"><img src='images/btl-logo.gif'></div>

<script language="javascript">
var aThumb = document.getElementById("bartlby");
pushLayer(aThumb);
Drag.init(aThumb, null);
repositionIT();
</script>



<div id="addNew" style="visibility:hidden; position: absolute; height: 300px; width: 200px;  left: 412px; top:100px; color:#000000; background-color: yellow; z-index:3;">
<form name="addfm">
<table width=100%>
	<tr>
		<td colspan=2><b>Add Element</td>
	</tr>
	<tr>
		<td>Caption:</td>
		<td><input type=text name="newCaption"></td>
	</tr>
	<tr>
		<td>Icon:</td>
		<td><?=$server_ico?></td>
	</tr>
	<tr>
		<td colspan=2><input type="button" value="add" onClick="addLayerForm();">
		<input type="button" value="cancel" onClick="cancelAddLayer();">
		</td>
	</tr>
	
</table>
</form>
</div>


<div id="layeropts" style="visibility:hidden; position: absolute; height: 300px; width: 200px;  left: 412px; top:100px; color:#000000; background-color: yellow; z-index:3;">
<form name="layeroptsfm">
<table width=100%>
	
	<tr>
		<td colspan=2><input type="button" value="remove" onClick="RemoLayer();">
		<input type="button" value="cancel" onClick="cancelLayerOpts();">
		</td>
	</tr>
	
</table>
</form>
</div>

<div id="output" style="visibility:hidden; position: absolute; height: 300px; width: 200px;  left: 600; top:100px; color:#000000; background-color: yellow; z-index:3;">
<form method=post name="storeit" action='bartlby_action.php'>
<input type=hidden name='action' value='storeMap'>
<textarea name='storeString' cols=50 rows=20>
</textarea>
</form>
</div>




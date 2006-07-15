<?
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	
	include "extensions/ServerGroups/ServerGroups.class.php";
	
	
	
	$btl=new BartlbyUi($Bartlby_CONF);
	$sg = new ServerGroups();
	
	$layout= new Layout();
	$layout->set_menu("Server Groups");
	$layout->Table("100%");

	
	$info_box_title="Group Search";  
	$core_content = "<script language='JavaScript' type='text/javascript' src='images/ajax_search.js'></script>
	<table  width='100%'>
		
		<tr>
			<td width=150 valign=top class='font2'>Search:</td>
			<td>
			<script>
			function setSearch(value) {
				document.location.href='extensions_wrap.php?script=ServerGroups/edit.php&group_name=' + value;
				document.getElementById('search_suggest').innerHTML = '';
			}
			</script>
				<input type='text' id='txtSearch' name='txtSearch' alt='Search Criteria' onkeyup=\"searchSuggest('extensions/ServerGroups/ajax_search.php');\" autocomplete='off' /> (PREG syntax)
				
				<div id='search_suggest'>
				</div>
			
			
			</td>
		</tr>
		
		
	</table>";
	
	$layout->push_outside($layout->create_box($info_box_title, $core_content));
	
	
	$layout->setTitle("Actions");
	$del_icon = "<a href='extensions_wrap.php?script=ServerGroups/edit.php'><img src='extensions/ServerGroups/new_group.png' border=0></A>";
				
	$layout->Tr(
	$layout->Td(
			Array(
				0=>$del_icon
			)
		)

	);
	
	$layout->TableEnd();
	$layout->display();
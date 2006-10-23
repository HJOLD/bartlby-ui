/*
	This is the JavaScript file for the AJAX Suggest Tutorial

	You may use this code in your own projects as long as this 
	copyright is left	in place.  All code is provided AS-IS.
	This code is distributed in the hope that it will be useful,
 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	
	For the rest of the code visit http://www.DynamicAJAX.com
	
	Copyright 2006 Ryan Smith / 345 Technical / 345 Group.	

*/
//Gets the browser specific XmlHttpRequest Object
var ajax_timeout_id=-1;

function getXmlHttpRequestObject() {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert("Your Browser Sucks!\nIt's about time to upgrade don't you think?");
	}
}

//Our XmlHttpRequest object to get the auto suggest
var searchReq = getXmlHttpRequestObject();

//Called from keyup on the search textbox.
//Starts the AJAX request.
function searchSuggest(urll) {
	window.clearTimeout(ajax_timeout_id);
	if (searchReq.readyState == 4 || searchReq.readyState == 0) {
		var str = escape(document.getElementById('txtSearch').value);
		searchReq.open("GET", urll + '?search=' + str, true);
		searchReq.onreadystatechange = handleSearchSuggest; 
		searchReq.send(null);
	}else {
		var ss = document.getElementById('search_suggest');
		ss.innerHTML = "<a href=\"#\" onClick=\"javascript:document.getElementById('search_suggest').innerHTML=''\">X close</A><hr>";
		
		ajax_timeout_id=window.setTimeout("searchSuggest('" + urll + "')", 1000);
			
	}		
}

function quickSuggest(urll) {
	window.clearTimeout(ajax_timeout_id);
	if (searchReq.readyState == 4 || searchReq.readyState == 0) {
		var str = escape(document.getElementById('qlook').value);
		searchReq.open("GET", urll + '?search=' + str, true);
		searchReq.onreadystatechange = handleQuickSuggest; 
		searchReq.send(null);
	} else {
		var ss = document.getElementById('quick_suggest')
		ss.innerHTML = "<font color=red>Loading...</font>";
		
		ajax_timeout_id=window.setTimeout("quickSuggest('" + urll + "')", 1000);	
	}		
}

//Called when the AJAX response is returned.
function handleSearchSuggest() {
	if (searchReq.readyState == 4) {
		var ss = document.getElementById('search_suggest')
		ss.innerHTML = "<a href=\"#\" onClick=\"javascript:document.getElementById('search_suggest').innerHTML=''\">X close</A><hr>";
		var str = searchReq.responseText.split("\n");
		for(i=0; i < str.length - 1; i++) {
			//Build our element string.  This is cleaner using the DOM, but
			//IE doesn't support dynamically added attributes.
			var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
			suggest += 'onmouseout="javascript:suggestOut(this);" ';
			suggest += 'onclick="" ';
			suggest += 'class="suggest_link">' + str[i] + '</div>';
			ss.innerHTML += suggest;
		}
	}
}
function handleQuickSuggest() {
	if (searchReq.readyState == 4) {
		var ss = document.getElementById('quick_suggest')
		ss.innerHTML = "<div style=''><a href=\"#\" onClick=\"javascript:document.getElementById('quick_suggest').innerHTML=''\">X close</A><hr>" + searchReq.responseText + "</div>";
		
		
	}
}

//Mouse over function
function suggestOver(div_value) {
	div_value.className = 'suggest_link_over';
}
//Mouse out function
function suggestOut(div_value) {
	div_value.className = 'suggest_link';
	
}
//Click function

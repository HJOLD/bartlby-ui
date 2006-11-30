<?
$xajax = new xajax("xajax_dispatcher.php");
$xajax->registerFunction("AddModifyWorker");
$xajax->registerFunction("AddModifyClient");
$xajax->registerFunction("AddModifyService");
$xajax->registerFunction("QuickLook");
$xajax->registerFunction("ServerSearch");
$xajax->registerFunction("jumpToServerId");

$xajax->registerFunction("ServiceSearch");
$xajax->registerFunction("jumpToServiceId");

$xajax->registerFunction("UserSearch");
$xajax->registerFunction("PluginSearch");
$xajax->registerFunction("SelectPlugin");

$xajax->registerFunction("jumpToUserId");

$xajax->registerFunction("removeDIV");

$xajax->registerFunction("updatePerfHandler");

$xajax->registerFunction("ExtensionAjax");


?>
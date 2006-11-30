<?
$xajax = new xajax("xajax_dispatcher.php");
$xajax->registerFunction("AddModifyWorker");
$xajax->registerFunction("AddModifyClient");
$xajax->registerFunction("AddModifyService");
$xajax->registerFunction("CreateReport");
$xajax->registerFunction("CreatePackage");
$xajax->registerFunction("AddDowntime");
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
$xajax->registerFunction("group_search");
$xajax->registerFunction("forceCheck");

?>
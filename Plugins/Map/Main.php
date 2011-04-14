<?php

include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');
include('codebase-php\GetSettingsFromXml.php');

AutoStart($argv);

include('Map_class.php');
$fi = new MapPlugin();
$fi->ld->userId=$CurrentUserId;
$fi->ld->EasyConnect();
$fi->GetInfoFromDB();
$fi->GetForm();
?>

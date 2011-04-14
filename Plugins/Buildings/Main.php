<?php

include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');
include('codebase-php\BotClass.php');
include('codebase-php\GetSettingsFromXml.php');

set_include_path(get_include_path() . PATH_SEPARATOR . "codebase-php");
$bot = new Bot();
date_default_timezone_set($bot->GetParamByName("sTimeZone"));
require_once 'Zend\Translate.php';
require_once 'Zend\Log.php';
require_once 'Zend\Log\Writer\Stream.php';

AutoStart($argv);

include('Plugins\Buildings\Buildings_class.php');
$bp = new BuildingsPlugin();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $bp->ld->userId=$CurrentUserId;
        $bp->ld->EasyConnect();
        $bp->GetForm();
    }
    if ($getP['action'] == 'save') {
        $bp->ld->userId=$CurrentUserId;
    $bp->ld->EasyConnect();
        $bp->ld->SavePlSettings('buildings', $postdata);
    }
}

?>
<?php
include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');
include('codebase-php\BotClass.php');
include('codebase-php\GetSettingsFromXml.php');
set_include_path(get_include_path() . PATH_SEPARATOR . "codebase-php");
include('Zend\config.php');
include('Zend\Config\Xml.php');

AutoStart($argv);

include('Plugins/Wall/Wall_class.php');
$Wall = new WallPlugin();

if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $Wall->ld->userId=$CurrentUserId;
        $Wall->ld->EasyConnect();
        $Wall->GetForm($getP);
    }
    if ($getP['action'] == 'menu') {
        $Wall->ld->userId=$CurrentUserId;
        $Wall->ld->EasyConnect();
        $Wall->GetForm($getP);
    }
    if ($getP['action'] == 'save') {
        $Wall->ld->userId=$CurrentUserId;
        $Wall->ld->EasyConnect();
        $Wall->ld->SavePlSettings('Wall', $postdata);
    }

    if ($getP['action'] == 'SavePost') { // What = tableName
        $Wall->ld->userId=$CurrentUserId;
        $Wall->ld->EasyConnect();
        $Wall->ld->SavePlSettings('WallR', $postdata);
        $Wall->ld->UpdatePlSettings('WallRequests', $postdata);
    }


}

?>
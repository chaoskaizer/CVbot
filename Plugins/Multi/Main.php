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

include('Multi_class.php');
$np = new MultiPlugin();


if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
            $np->GetForm($getP);
    }
    if ($getP['action'] == 'invOver') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
            $np->GetForm($getP);
    }
    if ($getP['action'] == 'menu') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
            $np->GetForm($getP);
    }
    if ($getP['action'] == 'SendGift') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('MultiSendGift', $postdata);
    }
    if ($getP['action'] == 'CityName') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('MultiCityName', $postdata);
    }

    if ($getP['action'] == 'MultiCollection') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('MultiCollection', $postdata);
    }
    if ($getP['action'] == 'MultiEnergy') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('MultiEnergy', $postdata);
    }
    if ($getP['action'] == 'btn_save_MaxGoods') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('MultiMaxGoods', $postdata);
    }
    if ($getP['action'] == 'savewlist') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('wishlist', $postdata);
    }
    if ($getP['action'] == 'btn_save_HQ') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('Franchise', $postdata);
    }
    if ($getP['action'] == 'btn_rst_image') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('ImageDownload', $postdata);
    }
    if ($getP['action'] == 'createnlist') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('Multiplugin', $postdata);
        $bot->pm->RefreshMePlugin("Multi");
    }
}
?>
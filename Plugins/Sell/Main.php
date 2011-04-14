<?php

include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');
include('codebase-php\BotClass.php');
include('codebase-php\GetSettingsFromXml.php');

AutoStart($argv);

include('Sell_class.php');
$np = new SellPlugin();
$bot = new Bot();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
            $np->GetForm();
    }
    if ($getP['action'] == 'reload') {
        $np->ld->userId=$CurrentUserId;
        //$np->ld->EasyConnect();
        $bot->ReloadConfig();
        echo "bla";
        $bot->SendMsg('** Reloading City **');
            $np->GetForm();
    }

    if ($getP['action'] == 'save') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('Sellplugin', $postdata);
    }
    if ($getP['action'] == 'createnlist') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('Sellplugin', $postdata);
    }
}
?>
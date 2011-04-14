<?php

include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');
include('codebase-php\BotClass.php');
include('codebase-php\GetSettingsFromXml.php');

AutoStart($argv);

include('Neighbors_class.php');
$np = new NeighborsPlugin();
$bot = new Bot();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
            $np->GetForm();
    }

    if ($getP['action'] == 'save') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('neighborsplugin', $postdata);
    }
    if ($getP['action'] == 'createnlist') {
        $np->ld->userId=$CurrentUserId;
        $np->ld->EasyConnect();
        $np->ld->SavePlSettings('neighborsplugin', $postdata);
    }
}
?>

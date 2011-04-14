<?php
include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');

AutoStart($argv);

include('Plugins/Coin3/Coin3_class.php');
$coin2 = new Coin3Plugin();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $coin2->ld->userId=$CurrentUserId;
        $coin2->ld->EasyConnect();
        $coin2->GetForm();
    }
    if ($getP['action'] == 'save') {
        $coin2->ld->userId=$CurrentUserId;
          $coin2->ld->EasyConnect();
        $coin2->ld->SavePlSettings('Coin3', $postdata);
    }
}

?>
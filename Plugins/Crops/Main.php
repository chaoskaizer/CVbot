<?php
include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\LocalDataClass.php');

AutoStart($argv);

include('Plugins\Crops\Crops_class.php');
$cp = new CropsPlugin();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $cp->ld->userId=$CurrentUserId;
        $cp->ld->EasyConnect();
        $cp->GetForm();
    }
    if ($getP['action'] == 'save') {
        $cp->ld->userId=$CurrentUserId;
	$cp->ld->EasyConnect();
        $cp->ld->SavePlSettings('cropsplugin', $postdata);
    }
}

?>

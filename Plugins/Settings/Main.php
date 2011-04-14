<?php
include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('codebase-php\BotClass.php');
include('Plugins\Settings\SM_class.php');
// set_include_path to include the Zend.
set_include_path(get_include_path() . PATH_SEPARATOR . "codebase-php");
$bot = new Bot();
date_default_timezone_set($bot->GetParamByName("sTimeZone"));
require_once 'Zend\Translate.php';
require_once 'Zend\Log.php';
require_once 'Zend\Log\Writer\Stream.php';

AutoStart($argv);

$sm = new Settings();
$sm->LoadCfg();

if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $sm->GetForm($getP);
    }
    if ($getP['action'] == 'save') {
        $sm->Update($postdata);
        SendApi('BotApi.ReloadSettings');
    }

}

?>
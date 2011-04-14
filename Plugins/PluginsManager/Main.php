<?php
include('codebase-php\bot_api.php');
include('codebase-php\Utils.php');
include('Plugins\PluginsManager\PM_class.php');

AutoStart($argv);


$pm = new PluginManager();
$pm->GetConfiguration();
if (isset($getP['action'])) {
    if ($getP['action'] == 'refresh') {
        $pm->GetForm();
    }
    if ($getP['action'] == 'get_el') {
        if (isset($getP['name'])) {
            $pm->GetEl($getP['name']);
        }
    }
    if ($getP['action'] == 'activate') {
        if (isset($getP['name'])) {
            $pm->ActivatePlugin($getP['name'], $getP['state']);
        }
    }
}

?>

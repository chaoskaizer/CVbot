<?php
include 'bot_api.php';
include('Plugins/PluginsManager/PM_class.php');
include('codebase-php\Utils.php');

AutoStart($argv);

$pm = new PluginManager();
$pm->GetConfiguration();
$pm->OpenTabForPlugin('Settings');
?>
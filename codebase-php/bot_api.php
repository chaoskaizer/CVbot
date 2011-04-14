<?php
//to use bot api functions you need include this file
//like this include('codebase-php\bot_api.php');

//right now you can use
//SendApi('BotApi.ClearLogs'); //clear logs
//SendApi('BotApi.MainWindowLoadUrl=http://www.google.com'); //load url in main bot window...so you can load
//SendApi('BotApi.RefreshPlByName=Test|http://www.mail1.ru'); //refresh plugin tab

//SendApi('BotApi.RUNNOW'); //press Restart button
//SendApi('BotApi.ShowTrayMsg=message'); //show msg in tray
//SendApi('BotApi.HideTabByName=TabCaption'); //hide plugin tab by name
//SendApi('BotApi.ReloadSettings'); //reload settings from options.txt
//SendApi('BotApi.CreateNewTab=Test'); //create new plugin tab by name
//SendApi('BotApi.ActivateTabByName=TabCaption'); //activate plugin tab by name
//SendApi('BotApi.SetUserId=1234566'); //set current user id





function SendApi($msg)
{
    global $udpport;
   //$fltmp = file('./tmp_dir/ports.txt');
   //$udpsock = fsockopen ("udp://127.0.0.1", trim($fltmp[0])+2, $errno, $errstr, 30);
    $udpsock = fsockopen ("udp://127.0.0.1", $udpport+2, $errno, $errstr, 30);
   fputs ($udpsock, $msg . "\n");
   fclose($udpsock);
//echo $msg;
}
?>
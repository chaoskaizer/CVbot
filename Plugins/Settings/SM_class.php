<?php

class Settings {

    var $values;
    var $bot;
    var $translate;

    // ==========================================================================
    function Settings() {
    $bot = new Bot();
     $LangPath = 'Plugins/Settings/lang/';
     $disableNotices = FALSE; // TRUE for production, so no errors are shown.
     $LangRoute = array( 'de' => 'en', 'es' => 'en', 'fr' => 'en', 'tr' => 'en', 'it' => 'en', 'pt' => 'en', 'nl' => 'en', 'da' => 'en', 'nb' => 'en', 'pl' => 'en', 'ru' => 'en', 'el' => 'en' );
     $this->translate = new Zend_Translate( array('adapter' => 'tmx', 'content' => $LangPath, 'locale'  => 'en', 'route' => $LangRoute , 'disableNotices' => $disableNotices));

     //$this->translate = new Zend_Translate( array('adapter' => 'tmx', 'content' => $LangPath, 'locale'  => 'en' ));
     if (!$this->translate->isAvailable($bot->GetParamByName("language"))) {  $this->translate->setLocale('en');}   // not available languages are rerouted to another language
     $this->translate->setLocale($bot->GetParamByName("language"));
     // Create a log instance
     $writer = new Zend_Log_Writer_Stream('tmp_dir/Lang_Log/Settings_Lang.log');
     $log    = new Zend_Log($writer);
     $this->translate->setOptions( array('log' => $log, 'logUntranslated' => true ));

    }
    function t($trans)
    {
      $trans = htmlentities($this->translate->_($trans), ENT_QUOTES, "UTF-8");
      return $trans;
    }
    // ==========================================================================
    function LoadCfg() {
        $fl = file('options.txt');
        foreach ($fl as $line)
        {
            $pos = strpos($line, '=');
            $name = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1, strlen($line)));
            $this->values[$name]['name'] = $name;
            $this->values[$name]['value'] = $val;
        }
        if (!isset($this->values['language']))
        {
            $this->values['language']['name'] = 'language';
            $this->values['language']['value'] = 'en';
        }
        //steakbonus
        if (!isset($this->values['steakbonus']))
        {
            $this->values['steakbonus']['name'] = 'steakbonus';
            $this->values['steakbonus']['value'] = 1;
        }

    }

    // ==========================================================================
    function Update($params) {
        //TrayMsg===============================================================
        if (isset($params->TrayMsg)) {
            if ($params->TrayMsg == 'on') {
                $this->values['TrayMsg']['value'] = 1;
            }
            else {
                $this->values['TrayMsg']['value'] = 0;
            }
        } 
        //Time2Log===============================================================
        if (isset($params->Time2Log)) {
            if ($params->Time2Log == 'on') {
                $this->values['Time2Log']['value'] = 1;
            }
            else {
                $this->values['Time2Log']['value'] = 0;
            }
        }

        //iShowApiInLog===============================================================
        if (isset($params->iShowApiInLog)) {
            if ($params->iShowApiInLog == 'on') {
                $this->values['iShowApiInLog']['value'] = 1;
            }
             else {
                $this->values['iShowApiInLog']['value'] = 0;
            }
        }
        //sLoadUrl===============================================================
        if (isset($params->sLoadUrl)) {
            $this->values['sLoadUrl']['value'] = $params->sLoadUrl;
        }
        //  language  ==========================================================<<<<<<<<<=====
        if (isset($params->language)) {
            $this->values['language']['value'] = $params->language;
        } else {$this->values['language']['value'] = 'en';}
        //  steakbonus  ==========================================================<<<<<<<<<=====
        if (isset($params->steakbonus)) {
            $this->values['steakbonus']['value'] = $params->steakbonus;
        } else {$this->values['steakbonus']['value'] = 1;}
        //sTimeZone===============================================================
        if (isset($params->sTimeZone)) {
            $this->values['sTimeZone']['value'] = $params->sTimeZone;
        }
        
        //iRestartTimeSec===============================================================
        if (isset($params->iRestartTimeSec)) {
            $this->values['iRestartTimeSec']['value'] = $params->iRestartTimeSec;
        }
        //sProxyUser===============================================================
        if (isset($params->sProxyUser)) {
            $this->values['sProxyUser']['value'] = $params->sProxyUser;
        }
        //sProxyHost===============================================================
        if (isset($params->sProxyHost)) {
            $this->values['sProxyHost']['value'] = $params->sProxyHost;
        }
        //sProxyPass===============================================================
        if (isset($params->sProxyPass)) {
            $this->values['sProxyPass']['value'] = $params->sProxyPass;
        }
        //iProxyPort===============================================================
        if (isset($params->iProxyPort)) {
            $this->values['iProxyPort']['value'] = $params->iProxyPort;
        }
        //iProxyUse===============================================================
        if (isset($params->iProxyUse)) {
            if ($params->iProxyUse == 'on') {
                $this->values['iProxyUse']['value'] = 1;
            }
            else {
                $this->values['iProxyUse']['value'] = 0;
            }
        } 



        $fl = fopen('options.txt', 'w');
        foreach ($this->values as $par) {
            fwrite($fl, trim($par['name']) . '=' . trim($par['value']) . "\n");
        }
        fclose($fl);

        $this->GetForm("");
    }

    // ==========================================================================
    function ShowSettings() {
//print_r($this->values);
//exit;                                                    '.$translate->_("Settings").'


        return $val;
    }

    // ==========================================================================
    function GetForm($Server) {

        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"  lang="en">
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"'."\n\n".' />
<meta http-equiv="Content-language" content="en" />

        <title>Settings</title>
                <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        
        <style>
            body{ font-family: Arial, Helvetica, sans-serif;
            background-color: rgb(74, 154, 74);  font-size: 10pt; }

            #maind{ font-size: 10pt;   border: 1px solid black;  width: 600px;
                  background-color: rgb(255, 249, 200); padding: 0px;     }
            #zag{  font-size: 15pt;     font-weight: bold;    }
            #nsp{  width: "200";  }
            .ps{   font-size: 12pt; }
        </style>
                <script>
                $(document).ready(function(){
                    $("#btn").click(function(){
                        var settings=new Object();
                        if ($("#TrayMsg").attr("checked")==true){
                            settings.TrayMsg="on";
                        }
                        else{
                            settings.TrayMsg="off";
                        }
                        settings.sLoadUrl=$("#sLoadUrl").val();
                        settings.iRestartTimeSec=$("#iRestartTimeSec").val();
                        settings.sTimeZone=$("#sTimeZone option:selected").val();
                        settings.language=$("#language option:selected").val();
                        settings.steakbonus=$("#steakbonus option:selected").val();

                        //settings.iShowApiInLog=$("#iShowApiInLog").val();
                        if ($("#iShowApiInLog").attr("checked")==true){
                            settings.iShowApiInLog="on";
                        }
                        else{
                            settings.iShowApiInLog="off";
                        }
                        if ($("#Time2Log").attr("checked")==true){
                            settings.Time2Log="on";
                        }
                        else{
                            settings.Time2Log="off";
                        }
           
                        //settings.iProxyUse=$("#iProxyUse").val();
                        if ($("#iProxyUse").attr("checked")==true){
                            settings.iProxyUse="on";
                        }
                        else{
                            settings.iProxyUse="off";
                        }

                        settings.sProxyHost=$("#sProxyHost").val();
                        settings.iProxyPort=$("#iProxyPort").val();
                        settings.sProxyUser=$("#sProxyUser").val();
                        settings.sProxyPass=$("#sProxyPass").val();
                        var req=$.toJSON(settings);
                        var l=window.location.toString();
            var indx=l.indexOf(\'?\');
            var nurl=l.slice(0, indx)+"?action=save&tmp="+Math.random();
                        $.post(nurl, req);

                        setTimeout("window.location.reload()",1500);
                        return false;
                    });
                });
                </script>
    </head>
    <body>';
//include('codebase-php\language.php');





echo'        <form >
        <div >
           <div  >
            <div  id="maind">
            <form>
            <table border="0" width="600" >
                <tr id="zag" height="30"><td align="center">'.$this->t("Settings").'</td></tr>
                <tr><td valign="top">';
echo '<table border="0">';
echo  '<tr><td >'.$this->t("Version").': </td><td>' . $this->values["version"]['value'] . '</td></tr>';
echo  '<tr><td>'.$this->t("GameUrl").': </td><td><input id="sLoadUrl" name="sLoadUrl" type="text" size="60" value="' . $this->values["sLoadUrl"]['value'] . '"></td></tr>';
        $tmp = '';
        if (trim($this->values["TrayMsg"]['value']) == '1') {
            $tmp = 'checked';
        }
echo  '<tr><td>'.$this->t("Show tray msg").': </td><td><input id="TrayMsg" name="TrayMsg" type="checkbox" ' . $tmp . '></td></tr>';
        $tmp = '';
        if (trim($this->values["Time2Log"]['value']) == '1') {
            $tmp = 'checked';
        }
echo  '<tr><td>'.$this->t("Show time in logs").': </td><td><input id="Time2Log" name="Time2Log" type="checkbox" ' . $tmp . '></td></tr>';
echo  '<tr><td>'.$this->t("Bot restart interval (sec)").': </td><td><input id="iRestartTimeSec" name="iRestartTimeSec" type="text" size="60" value="' . $this->values["iRestartTimeSec"]['value'] . '"></td></tr>';
        $tmp = '';
        if (trim($this->values["iShowApiInLog"]['value']) == '1') {
            $tmp = 'checked';
        }
echo  '<tr><td>'.$this->t("Show api exec in log").': </td><td><input id="iShowApiInLog" name="iShowApiInLog" type="checkbox" ' . $tmp . '></td></tr>';
echo  '<tr><td>'.$this->t("Choose timezone").'</td><td><select id="sTimeZone" style="border-width:1;border-style:solid">';
                                
            foreach(timezone_identifiers_list() as $zone)
            {
                if($zone!="UTC")
                {
                    if($this->values["sTimeZone"]['value']==$zone){
                      echo  '<option value="'.$zone.'" selected>'.str_replace('_', ' ', $zone)."</option>";
                    }else{
                      echo  '<option value="'.$zone.'">'.str_replace('_', ' ', $zone)."</option>";
                    }
                }
            }
                            
                            
echo  '</select></td></tr>';
echo  '<tr><td colspan="2"><hr></td></tr>';
$lang3 = "";
$langAbv = array( 'de', 'en', 'es', 'fr', 'tr', 'it', 'pt', 'nl', 'da', 'nb', 'pl', 'ru', 'el_GR' );
echo  '<tr><td>'.$this->t("Choose language").'</td><td><select id="language" style="border-width:1;border-style:solid" >';
            foreach($langAbv as $langCode )
            {
              if ($this->values["language"]['value'] == $langCode) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="'.$langCode.'" '.$tmp.'>'. $this->t($langCode)."</option>\n";
            }
echo  '</select></td></tr>';
echo  '<tr><td colspan="2"><hr></td></tr>';
echo  '<tr><td>'.$this->t("Streak Bonus").'</td><td><select id="steakbonus" style="border-width:1;border-style:solid" >';
              if ($this->values["steakbonus"]['value'] == 1) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="1" '.$tmp.">".$this->t("Maximum")."  </option>\n";
              if ($this->values["steakbonus"]['value'] == 4) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="4" '.$tmp."> ".$this->t("Medium")."  </option>\n";
              if ($this->values["steakbonus"]['value'] == 8) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="8" '.$tmp."> ".$this->t("Average")."  </option>\n";
              if ($this->values["steakbonus"]['value'] == 32) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="32" '.$tmp."> ".$this->t("Minimum")." </option>\n";
              if ($this->values["steakbonus"]['value'] == 0) { $tmp = ' selected'; }else{$tmp = '';}
              echo  '<option value="0" '.$tmp."> ".$this->t("Do not collect")." </option>\n";
echo  '</select></td></tr>';
echo  '<tr><td colspan="2"><hr></td></tr></tr>';
echo  '<tr><td height="100" valign="top">'.$this->t("Proxy settings").': </td><td valign="top"><table border="0">';
        $tmp = '';
        if (trim($this->values["iProxyUse"]['value']) == '1') {
            $tmp = 'checked';
        }
echo  '<tr><td>'.$this->t("Use proxy").':</td><td><input id="iProxyUse" name="iProxyUse" type="checkbox" ' . $tmp . '></td></tr>';
echo  '<tr><td  >'.$this->t("Host").':</td><td><input id="sProxyHost" name="sProxyHost" type="text" size="20" value="' . $this->values["sProxyHost"]['value'] . '"></td></tr>';
echo  '<tr><td  >'.$this->t("Port").':</td><td><input id="iProxyPort" name="iProxyPort" type="text" size="20" value="' . $this->values["iProxyPort"]['value'] . '"></td></tr>';
echo  '<tr><td  >'.$this->t("User").':</td><td><input id="sProxyUser" name="sProxyUser" type="text" size="20" value="' . $this->values["sProxyUser"]['value'] . '"></td></tr>';
echo  '<tr><td  >'.$this->t("Password").':</td><td><input id="sProxyPass" name="sProxyPass" type="text" size="20" value="' . $this->values["sProxyPass"]['value'] . '"></td></tr>';
echo  '</table></tr><tr><td colspan="2"><hr></td></tr>';
echo  '</table>';
echo  '</td></tr>
                <tr height="30"><td align="center">
                <button id="btn" type="submit" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">
                '.$this->t("Save settings").'</button></td></tr>
            </table>
            </form>
            </div>
        </div>
        </form>';
echo '<i>This page is availible in: ';
foreach($this->translate->getList() as $code )
 {
//  if($language == $code ){$selected = " SELECTED";}else{$selected = " ";}
  echo $this->t($code).' ';
 }
echo '</i><br>';

    echo'    </body>
</html>
';
    }

    // ==========================================================================
}
?>
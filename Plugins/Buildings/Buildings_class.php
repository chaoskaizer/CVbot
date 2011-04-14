<?php

class BuildingsPlugin {
    var $ld;
    var $xmlsOb;
    var $bot;
    var $translate;
    var $LangNotice;
    var $XMLtransl;

    function BuildingsPlugin() {
     $this->ld = new LocalData();
     $bot = new Bot();
     $SetLangauge = $bot->GetParamByName("language");

     $LangPath = 'Plugins/Buildings/lang/';
     $disableNotices = TRUE; //FALSE; // TRUE for production, so no errors are shown.
     $LangRoute = array( 'de' => 'en', 'es' => 'en', 'fr' => 'en', 'tr' => 'en', 'it' => 'en', 'pt' => 'en', 'nl' => 'en', 'da' => 'en', 'nb' => 'en', 'pl' => 'en', 'ru' => 'en' );
     $this->translate = new Zend_Translate( array('adapter' => 'tmx', 'content' => $LangPath, 'locale'  => 'en', 'route' => $LangRoute , 'disableNotices' => $disableNotices));
     $this->translate->setLocale($SetLangauge);

     if (!$this->translate->isAvailable($SetLangauge))
       { // not available languages are rerouted to english
         $this->LangNotice = "Your language is not (yet) available. We use English where needed.<br>";
         $this->translate->setLocale('en');
       } else {$this->LangNotice = "";}

     // Create a log instance
     $writer = new Zend_Log_Writer_Stream('tmp_dir/Lang_Log/Buildings_Lang.log');
     $log    = new Zend_Log($writer);
     $this->translate->setOptions( array('log' => $log, 'logUntranslated' => true ));
     //now load the langauge from the game.xml
     $this->XMLtransl = new transl($SetLangauge);
      ///  $XMLtransl->langXML


    }
    function t($trans)
    {
      // look for translation in XML





      if ($this->translate->isAvailable($trans))
       {
         return  htmlentities($this->translate->_($trans), ENT_QUOTES, "UTF-8");
       }
       else
       {
         if(array_key_exists($trans, $this->XMLtransl->langXML)) return $this->XMLtransl->langXML[$trans];
         //_friendlyName
         $trans2 = $trans . "_friendlyName";
         if(array_key_exists($trans2, $this->XMLtransl->langXML)) return $this->XMLtransl->langXML[$trans2];

       }



      return  htmlentities($this->translate->_($trans), ENT_QUOTES, "UTF-8");
    }
    // ==========================================================================
    function GetOptionsByTypeName($name)
    {   global $imagePath;
        $xmlsOb=new xmlsOb();
        $res=""; //"<option>any</option>";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == $name)
            {
              if($name == "train")
                {
                   //$res=$res.'<option value="'.(string)$item['name']."|".(string)$item->trainStorage.'" >'.(string)$item['name']."</option>";
                   $res=$res.'<option value="'.(string)$item['name']."|".(string)$item->trainStorage.'" >'.$this->t((string)$item['name'])."</option>";
                }
                else
                {
                  $icon = $imagePath.$this->ld->GetIconByItemName((string)$item['name']);
                  $res=$res."<option value=\"".$icon."\" >".(string)$item['name']."</option>";
                }
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetOptionsByTypeName3($name)
    {
        $xmlsOb=new xmlsOb();
        $res="";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == $name){
                $res=$res."<option>".(string)$item['name']."</option>";
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetShips()
    {
      $ret = '<table class="table1">';
      $ret .= "<tr><td>Ship id</td><td>Route</td><td>Started</td><td>Returns</td><td>State</td><td></td><td></td></tr>";
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            if ($obj['className'] == "Ship")
            {
              if((string)$obj['state'] == "grown") { $state = "Ready";}else{ $state = "Underway";}
                                          //  String  rio   Number  1292943824000                                                                                     String Reference  grown
             $ret .= "<tr><td>".$obj['id']."</td><td>".$obj['contractName']."</td><td>".$this->ld->nicetime($obj['plantTime']/1000)."</td><td>Returns</td><td>".$state."</td><td></td><td></td></tr>";

            }
        }
      $ret .= "</table>";
      return $ret;

    }
    // ==========================================================================
    function GetOptionsByTypeName2($name)
    {
        $xmlsOb=new xmlsOb();
        $res=""; //"<option>any</option>";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == $name){
                $totCoins = $item->trainBuyPrice * $item->trainStorage ;
                $notUse = "&nbsp;";
                if($item['name'] == "train") { $notUse = "DO NOT USE THIS.";}
                if($item['name'] == "handcar_train") { $notUse = "DO NOT USE THIS.";}
                if($item['name'] == "bullet_train") { $notUse = "DO NOT USE THIS.";}
                $res=$res."<tr><td>".(string)$item['name']. " </td><td> " . (string)$item->trainTripTime ." Sec </td><td> " . (string)$item->trainStorage ." Goods </td><td> " . (string)$item->trainBuyPrice ." Coins </td><td> Total: " . $totCoins . " Coins</td><td>".(string)$item->requiredQuestFlag." ". $notUse."</td></tr>";
            }
        }
        return $res;
    }

    // ==========================================================================
    function GetXMLtime($time)
    {
      $tmptime = (string)$time*(23/24); // now the time is in days.
      $tmptime = $tmptime * 24 * 60 ; // now time is in min.
      if($tmptime >= 0 && $tmptime < 60){ $tmptime= round($tmptime, 1)." Min."; }
      if($tmptime >= 60 && $tmptime < 3600){ $tmptime= round($tmptime/60, 1)." Hour"; }
      if($tmptime >= 3660 && $tmptime < 86400){ $tmptime= round($tmptime/60/24, 1)." Day"; }
      $nicetime = $tmptime;
      return $nicetime;
    }
    // ==========================================================================
    function GetOptionsShip($name)
    {
        $Table = array();
        $xmlsOb=new xmlsOb();
        foreach ($xmlsOb->gsXML->randomModifierTables->randomModifierTable as $tmp)
          {
           //echo "type ".  $tmp['type'] ."  \n";
           //$Table[$tmp['name']]['type']   = $tmp['type'];
           //$Table[$tmp['name']]['amount'] = $tmp->roll->goods ;
          }
        //var_dump($Table);
        $res=""; //"<option>any</option>";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == $name){
                $totCoins = $item->trainBuyPrice * $item->trainStorage ;
                $notUse = "&nbsp;";

                foreach($item->randomModifiers as $modifier )
                  {
                    if($modifier->modifier['type'] == "goods") {$TableName = $modifier->modifier['tableName'];}
                  }
                  $tmpamount = "Unknown";
                foreach ($xmlsOb->gsXML->randomModifierTables->randomModifierTable as $tmp)
                  {
                    //echo $tmp['name'] . "<br>\n";
                    if($tmp['name'] == (string)$TableName) {  $tmpamount = $tmp->roll->goods['amount'];}
                  }
                $amountOfGoods = $tmpamount;
                $time = $this->GetXMLtime($item->growTime);
                $CoinsPgood = round((string)$item->cost / $amountOfGoods, 2);
                $res=$res."<tr><td>".(string)$item['name']. " </td><td>" .$time." </td><td> " . $amountOfGoods ." Goods </td><td> " . $CoinsPgood ." Coins </td><td> Total: " . (string)$item->cost . " Coins</td><td>".(string)$item->requiredQuestFlag." </td></tr>";
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetForm() {
global $imagePath;
$imagePath = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "/"));
    $PlVersion = (array)$this->ld->GetPlVersion('buildings');
$settings = (array) $this->ld->GetPlSettings('buildings');


        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
        <title>Buildings</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
 <link href="helpers/plugin.css" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">
          <!--
          var NS4 = (navigator.appName == "Netscape" && parseInt(navigator.appVersion) < 5);
          function addOption(theSel, theText, theValue)
          {
            var newOpt = new Option(theText, theValue);
            var selLength = theSel.length;
            theSel.options[selLength] = newOpt;
          }

          function deleteOption(theSel, theIndex)
          { 
            var selLength = theSel.length;
            if(selLength>0)
            {
              theSel.options[theIndex] = null;
            }
          }

          function moveOptions(theSelFrom, theSelTo)
          {
            var selLength = theSelFrom.length;
            var selectedText = new Array();
            var selectedValues = new Array();
            var selectedCount = 0;
            var i;
            // Find the selected Options in reverse order and delete them from the from Select.
            for(i=selLength-1; i>=0; i--)
            {
              if(theSelFrom.options[i].selected)
              {
                selectedText[selectedCount] = theSelFrom.options[i].text;
                selectedValues[selectedCount] = theSelFrom.options[i].value;
                deleteOption(theSelFrom, i);
                selectedCount++;
              }
            }
            
            // Add the selected text/values in reverse order.
            // This will add the Options to the TO Select
            // in the same order as they were in the FROM Select.
            for(i=selectedCount-1; i>=0; i--)
            {
              addOption(theSelTo, selectedText[i], selectedValues[i]);
            }
            
            if(NS4) history.go(0);
          }
          //-->
</script>


 <script language="javascript">
<!-- For select images box
function showimage() { if (!document.images) return
document.images.pictures.src=document.mygallery.picture.options[document.mygallery.picture.selectedIndex].value }
//-->
</script>

        <script>
        //==============================================================
        window.reload = function(){
                var l=window.location.toString();
                var indx=l.indexOf(\'?\');
                window.location=l.slice(0, indx)+"?action=refresh&tmp="+Math.random();
        }
        //==============================================================
            $(document).ready(function(){
                                window.settings=eval(' . json_encode($this->ld->GetPlSettings('buildings')) . ');
                                if ((window.settings!==null) && (window.settings!==undefined)){
                                $("#trainMissionIN").val(window.settings.trainMissionIN);
                                $("#shipMissionIN").val(window.settings.shipMissionIN);

                                 $(":checkbox").each(function(){
                                           if(window.settings[$(this).attr("id")]==true){
                                                $(this).attr("checked", true);
                                            }
                                            else{
                                                $(this).attr("checked", false);
                                            }
                                        });
                                }
                //==============================================================
                $("#btn_save").click(function(){
                var req=new Object();
                                        $(":checkbox").each(function(){
                                            var par=$(this).attr("id");
                                            req[par]=$(this).attr("checked");
                                        });
                    req["trainMissionIN"]=$("#trainMissionIN").val();
                    req["shipMissionIN"]=$("#shipMissionIN  :selected").text();
                    //req["collectbbIN"]=$("#collectbbIN  :selected").text();

                    req.collectbbIN=new Array();
                    $("#collectbbIN option").each(function(){
                          req.collectbbIN.push($(this).attr("id")+""+$(this).text());
                     });
					 //req["collectbbIN"]=$("#collectRentIN  :selected").text();
					req.collectRentIN=new Array();
                    $("#collectRentIN option").each(function(){
                          req.collectRentIN.push($(this).attr("id")+""+$(this).text());
                      });


                    data=$.toJSON(req);
                    var l=window.location.toString();
                    var indx=l.indexOf(\'?\');
                    var nurl=l.slice(0, indx)+"?action=save&tmp="+Math.random();
                    $.post(nurl, data);
                    
                    return false;
                });
            });
        </script>

    </head>
    <body>
        <form name="mygallery">
         <div ><span class="error">'.$this->LangNotice.'</span>';
      //Announcement03_title
      //var_dump($this->XMLtransl->langXML);
      //echo 'test:  '.$this->t("Announcement03_title").'   <br>';
      //echo 'test:  '.$this->t("blabla").'   <br><br>';
      //var_dump($this->t("Announcement03_title"));
      //echo ' <br><br><br>';
	  //Collect rent modified by phreaker

echo'    <h1>&nbsp;'.$this->t("Buildings").'&nbsp;'.$this->t("Version").':&nbsp;'.$PlVersion['version'].' ('.$PlVersion['date'].') </h1><hr></div>
                        <input type="checkbox" id="collectrent">'.$this->t("Collect rent").'?<br>
						                        Select the rent you like to collect (to save energy).<br>';
 if(!isset( $settings['collectRentIN'])) $settings['collectRentIN'] = array();
        $xmlsOb=new xmlsOb();
        $resNOT="";
        $resIN="";
        foreach ($xmlsOb->gsXML->items->item as $item) {
          if ((string)$item['type'] == "residence")
            {
              $found = "N";
              foreach($settings['collectRentIN'] as $item2) { if((string)$item['name'] == $item2) {$found = "Y";} }
              if($found == "Y" )
                { $resIN .= "<option>".(string)$item['name']."</option>"; }
                else
                { $resNOT .= "<option>".(string)$item['name']."</option>";}
            }
        }
echo  '&nbsp;&nbsp;      <table class="table0">
                         <th>Do NOT collect</th><th></th><th>Do collect</th></tr>
                          <tr>
                            <td>
                              <select name="sell1" size="10" multiple="multiple">';
echo $resNOT;
echo '                        </select>
                            </td>
                            <td align="center" valign="middle">
                              <input type="button" value="--&gt;"
                               onclick="moveOptions(this.form.sell1, this.form.sell2);" /><br />
                              <input type="button" value="&lt;--"
                               onclick="moveOptions(this.form.sell2, this.form.sell1);" />
                            </td>
                            <td>
                              <select name="sell2" size="10" multiple="multiple" id="collectRentIN">';
echo $resIN;
echo '                        </select>
                            </td>
                          </tr>
                        </table>';

   //    echo  '   &nbsp;<select size="12" multiple id="collectbbIN">'.$this->GetOptionsByTypeName3("business").'</select>';



echo '                  <hr>

                        <hr>
                        <input type="checkbox" id="collectbb">'.$this->t("Collect business buildings").'?<br>
                        Select the business you like to collect (to save energy).<br>';
 if(!isset( $settings['collectbbIN'])) $settings['collectbbIN'] = array();
        $xmlsOb=new xmlsOb();
        $resNOT="";
        $resIN="";
        foreach ($xmlsOb->gsXML->items->item as $item) {
          if ((string)$item['type'] == "business")
            {
              $found = "N";
              foreach($settings['collectbbIN'] as $item2) { if((string)$item['name'] == $item2) {$found = "Y";} }
              if($found == "Y" )
                { $resIN .= "<option>".(string)$item['name']."</option>"; }
                else
                { $resNOT .= "<option>".(string)$item['name']."</option>";}
            }
        }
echo  '&nbsp;&nbsp;      <table class="table0">
                         <th>Do NOT collect</th><th></th><th>Do collect</th></tr>
                          <tr>
                            <td>
                              <select name="sel1" size="10" multiple="multiple">';
echo $resNOT;
echo '                        </select>
                            </td>
                            <td align="center" valign="middle">
                              <input type="button" value="--&gt;"
                               onclick="moveOptions(this.form.sel1, this.form.sel2);" /><br />
                              <input type="button" value="&lt;--"
                               onclick="moveOptions(this.form.sel2, this.form.sel1);" />
                            </td>
                            <td>
                              <select name="sel2" size="10" multiple="multiple" id="collectbbIN">';
echo $resIN;
echo '                        </select>
                            </td>
                          </tr>
                        </table>';

   //    echo  '   &nbsp;<select size="12" multiple id="collectbbIN">'.$this->GetOptionsByTypeName3("business").'</select>';



echo '                  <hr>
                        <input type="checkbox" id="supplybb">'.$this->t("Supply business buildings").'?<br>
                        <input type="checkbox" id="supplybbplus">'.$this->t("Make business building instant ready").'<br>
                        <input type="checkbox" id="collectLM">'.$this->t("Collect Landmarks").'?<br>
                        <input type="checkbox" id="collectMu">'.$this->t("Collect Municipal Buildings").'?<br>
                        <hr>
                        <input type="checkbox" id="ClearWildernes">'.$this->t("Clear Wildernes").'?<br>
                        <hr>
         <div ><h2>'.$this->t("DailyBonusCall").'</h2></div>
                        <input type="checkbox" id="AcceptDayBonus">'.$this->t("Accept daily bonus").'<br>
                        <hr>
         <div ><h2>'.$this->t("Trains").'</h2></div>
         <table>
         <tr><td>'.$this->t("Trains_helptext").'
                        <br>
                        <input type="checkbox" id="NoTrainIfFull">'.$this->t("Stop collecting trains").' <br>
                        <input type="checkbox" id="receiveTrain">'.$this->t("Handle incoming trains").'<br>
                        <input type="checkbox" id="sentTrain">'.$this->t("Send train").'
                        &nbsp;'.$this->t("TrainUI_SendTrain").':&nbsp;<select id="trainMissionIN">'.$this->GetOptionsByTypeName("train").'</select> <br>
           </td>
           <td ><img src="'.$imagePath.'assets/NPC/Car_train/trainBuySell_train_locomotive.png" name="pictures" ></td>
           </tr></table>
                        <hr>

         <div ><h2>'.$this->t("ships_menu").'</h2></div>
         <table><tr><td>'.$this->t("ships_helptext").'<br>
                        <input type="checkbox" id="NoShipIfFull">'.$this->t("Stop collecting ships").'<br>
                        <input type="checkbox" id="receiveShip">'.$this->t("Handle incoming ships").'<br>
                        <input type="checkbox" id="sentShip">'.$this->t("Send ships").'
                        &nbsp;'.$this->t("ship_contract_menu").':&nbsp;
                        <select name="picture" id="shipMissionIN"  onChange="showimage()">'.$this->GetOptionsByTypeName("ship_contract").'</select> <br></td>';
echo '<td width="100%"><img src="'.$imagePath.'assets/goods/goods_ships/ship_smallboat/smallboat_icon.png" name="pictures" ></td>';
echo '</tr></table><br> ';
echo '    <div width="100%" align="center"><br><br>
                        <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp;'.$this->t("Save").'&nbsp;</button>
                        </div>';

echo '    <hr><hr>Below here you have some more information on your ships & boats.<br>';
echo '    <h2>Here the current ship contracts</h2><br> ';
   echo $this->GetShips();
echo '    <br><h2>Posible Train Missions</h2><br>
                        <table class="table1">
                        <tr><td>Mission name:</td><td>Trip Time (Sec)</td><td>Train Storage (Goods)</td><td>Buy Price / goods (Coins)</td><td>Total Price (Coins)</td><td>Need Quest to unlock</td></tr>
                        '.$this->GetOptionsByTypeName2("train").'
                        </table>
                        <br>';
echo '  <h2>  Ship mission. (Work in progress.)</h2><br>
                        <table class="table1">
                        <tr><td>Mission name:</td><td>Trip Time</td><td>Ship Storage (Goods)</td><td>Buy Price / goods (Coins)</td><td>Total Price (Coins)</td><td>Need Quest to unlock</td></tr>
                        '.$this->GetOptionsShip("ship_contract").'
                        </table>        </form>';
echo '<i>This page is available in: ';
//var_dump($this->translate->getList());
foreach($this->translate->getList() as $code )
 {
//  if($language == $code ){$selected = " SELECTED";}else{$selected = " ";}
  echo $this->t($code).' ';
 }
echo '</i><br>';
echo '    </body>
</html>
        ';
    }

}

?>
<?php
class MultiPlugin {
    var $ld;
    var $bot;
    var $np;
    var $XMLconfig;


    // ==========================================================================
    function MultiPlugin()
    {
        $this->ld = new LocalData();
        $this->bot = new Bot();
        //$np = new MultiPlugin();

    }
    // ==========================================================================
    function GetOptionsByClassName($name)
    {
        $xmlsOb=new xmlsOb();
        $res="<option>any</option>";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['className'] == $name){
                $res=$res."<option>".(string)$item['name']."</option>";
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetCollectionInfo()
    {
        $xmlsOb=new xmlsOb();
        $res = array();
        foreach ($xmlsOb->gsXML->collections->collection as $collection)
        {
          $coll = (array)$collection;
          $collname = (string)$collection['name'];
          foreach($coll['collectables'] as $collectables )
            {
               $res[$collname]['collectables'][] = (string)$collectables['name'];

            }
          $res[$collname]['tradeInReward']['item'] = " ";
          $res[$collname]['tradeInReward']['xp'] = " ";
          $res[$collname]['tradeInReward']['coin'] = " ";
          $res[$collname]['tradeInReward']['energy'] = " ";
          $res[$collname]['tradeInReward']['goods'] = " ";
          foreach($coll['tradeInReward'] as $type => $tradeInReward)
            {  $tr = (array)$tradeInReward  ;
               if($type == "item" )   {$res[$collname]['tradeInReward']['item'] = $tr['@attributes']['name']; }
               if($type == "xp" )     {$res[$collname]['tradeInReward']['xp'] = $tr['@attributes']['amount']; }
               if($type == "coin" )   {$res[$collname]['tradeInReward']['coin'] = $tr['@attributes']['amount']; }
               if($type == "energy" ) {$res[$collname]['tradeInReward']['energy'] = $tr['@attributes']['amount']; }
               if($type == "goods" )  {$res[$collname]['tradeInReward']['goods'] = $tr['@attributes']['amount']; }
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetOptionsByTypeName($name)
    {
        $xmlsOb=new xmlsOb();
        $res="<option>any</option>";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == $name){
                $res=$res."<option>".(string)$item['name']."</option>";
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetOptionsByGiftable()
    {
        $xmlsOb=new xmlsOb();
        $res="";
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['giftable'] == "true"){
                $res=$res."<tr><td>".(string)$item['name'].'</td><td><input type="checkbox" id="'.(string)$item['name'].'Gift"></td></tr>';
            }
        }
        return $res;
    }
    // ==========================================================================
    function NlistCheck() {
        $is = false;
        $res = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Multi"'));
        
        if ($res[0]["cnt"]==1) {
            $is = true;
        }
        return $is;
    }
    // ==========================================================================
    function GetNList() {
        $res = "";
        $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="neighbors"'));
        if ($sel[0]["cnt"] > 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM neighbors'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT value FROM neighbors'));
                foreach ($sel as $n){
                    $tmp=array();
                    $tmp=unserialize(base64_decode($n['value']));
                    $res[]= array( 'uid'=>$tmp['uid'], 'cityname'=>$tmp['cityname'] ) ; //unserialize($n['value']);
                    //$res[]= array( 'uid'=>$n['value'], 'worldName'=>$n['value'] ) ; //unserialize($n['value']);
                }
            }
        }
        return $res;
    }
    // ==========================================================================
    function GetMulti() {
        $res = "";
        $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Multi"'));
        if ($sel[0]["cnt"] == 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM Multi'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT * FROM Multi'));
                foreach ($sel as $n)
                {
                  $res[$n['Item']]['Number']= $n['Number'];
                  $res[$n['Item']]['Keep']= $n['Keep']     ;
                  $res[$n['Item']]['Use']= $n['Use']     ;
                  $res[$n['Item']]['Item']= $n['Item']     ;
                }
                //    var_dump($res);   echo "<br><hr>";

            }
        }
        return $res;
    }
    // ==========================================================================


    function GetForm($server)
    {
    $franchise = (array)$this->ld->GetPlSettings('Franchise');
    $MultiEnergy = (array)$this->ld->GetPlSettings('MultiEnergy');
    $PlVersion = (array)$this->ld->GetPlVersion('Multi');

         echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
 <head>
 <title>Multi helper</title>
 <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
 <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
 <link href="helpers/menu.css" rel="stylesheet" type="text/css">
 <link href="helpers/plugin.css" rel="stylesheet" type="text/css">
 <script type="text/javascript" src="helpers/menu.js"></script>
<!--[if lte IE 7]>
<style>
#menuwrapper, #p7menubar ul a {height: 1%;}
a:active {width: auto;}
</style>
<![endif]-->

<script language="javascript">
<!--
function showimage() { if (!document.images) return
document.images.pictures.src=document.mygallery.picture.options[document.mygallery.picture.selectedIndex].value }
//-->
</script>


 <script>
 //======================================================================
  function changeText(){
                var boldtext = "<font style=\'font-weight:bold;font-size:13px;\'>"
      for(var key in window.nlist) {
          if(window.nlist[key]["uid"]==$("#nsp option:selected").attr("id")){
              var nbobj=window.nlist[key];
          }
      }
   
   if(nbobj!==undefined){
      var newDate = new Date();
      newDate.setTime(nbobj["lastplayed"]*1000);

      var sdiv="";
      sdiv="<br><br>"
      sdiv=sdiv+ "Name: &nbsp;&nbsp;" +boldtext+  nbobj["worldName"]+ "&nbsp( " + nbobj["uid"] + " )&nbsp </font><br>";

      $("#rcontent").html(sdiv);
                    }
  }
 //======================================================================
 $(document).ready(function(){';
if($server['click'] == "wishlist")
   {
    echo '
     window.setWlist=eval(' .  json_encode($this->ld->GetPlSettings('wishlist')) . ');
          //==============================================================
     if ((window.setWlist!==null) && (window.setWlist!==undefined)){
         $(":checkbox").each(function(){
           if(window.setWlist[$(this).attr("id")]==true){ $(this).attr("checked", true); }else{ $(this).attr("checked", false);}
          });
      }';
   }
if($server['click'] == "neighborLot")
   {
    echo '     window.lotsite=eval(' .   json_encode($this->ld->GetPlSettings('neighborspluginLotsite')) . ');
        //==============================================================
        if ((window.lotsite!==null) && (window.lotsite!==undefined)){
           for(var key in window.lotsite) {
              $("#lotsite").append("&nbsp;<a href=\"http://apps.facebook.com/cityville/flash.php?startupType=emptyLot:"+window.lotsite[key]["uid"]+"\" target=\"_blank\">"+window.lotsite[key]["worldName"]+"</a>&nbsp;"+window.lotsite[key]["itemName"]+"&nbsp;<br>");
           }
          }';
   }
if($server['click'] == "maxgoods")
 {

 }


echo '//==============================================================
     $("#btn_MultiEnergy").click(function(){
       var req=new Object();
       $("input:text").each(function(){
             req[$(this).attr("id")]=$(this).val();
         });

       $(":checkbox").each(function(){
            var par=$(this).attr("id");
            req[par]=$(this).attr("checked");
         });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=MultiEnergy&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================
     $("#btn_save_wlist").click(function(){
       var req=new Object();

       $(":checkbox").each(function(){
            var par=$(this).attr("id");
            req[par]=$(this).attr("checked");
         });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=savewlist&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================
     $("#btn_save_HQ").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=btn_save_HQ&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================
     $("#btn_save_MaxGoods").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       req["MultiMaxGoodsFillAfter"]=$("#MultiMaxGoodsFillAfter").val();
       req["MultiMaxGoodsFill"]=$("#MultiMaxGoodsFill").val();
       req["cropsIN"]=$("#cropsIN  :selected").text();

       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=btn_save_MaxGoods&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================
     $("#MultiCollection").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       req["KeepCollection"]=$("#KeepCollection").val();

       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=MultiCollection&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================
     $("#btn_rst_image").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
                    req["reset"]=$("#reset").val();

       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=btn_rst_image&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //==============================================================btn_send_inv
     $("#btn_send_inv").click(function(){
       var req=new Object();
                    req["url"]=$("#url").val();

       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=invOver&tmp="+Math.random();
             $.post(nurl, data);

             return false;
        });
     //============================================================== option:selected
     $("#btn_SendGift").click(function(){
       var req=new Object();
       $(":input").each(function(){
             req[$(this).attr("id")]=$(this).val();
         });
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SendGift&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //============================================================== option:selected
     $("#btn_CityName").click(function(){
       var req=new Object();
       $(":input").each(function(){
             req[$(this).attr("id")]=$(this).val();
         });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=CityName&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //==============================================================
            }); // end document ready
        //======================================================================
        </script>

</head>
<body >

<h1>Multi (by 12Christiaan) Version '.$PlVersion['version'].' ('.$PlVersion['date'].') </h1>
<div id="menuwrapper">
<ul id="p7menubar">
    <li><a href="/Plugins/Multi/Main.php?action=menu&click=home">Home</a></li>
    <li><a class="trigger" href="#">General</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=userinfo">User Information</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=invOver">Inventory Overview</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=energy">Inventory Energy</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=SendGift">Inventory Sending Gifts</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=maxgoods">Manage max goods</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=collection">Collections</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=images">Images settings</a></li>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=SetName">Set City Name</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Wish List</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=wishlist">Wish list settings</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Franchise</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=franchise">Franchise settings</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Neighbor</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=neighborLot">Neighbors Lot site</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Game Info</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=lastimage">The latest items</a></li>
      </ul>
    <li><a class="trigger" href="#">Debug</a>
      <ul>
        <li><a href="/Plugins/Multi/Main.php?action=menu&click=debug">Debug for developer</a></li>
      </ul>
    </li>
</ul>
<br class="clearit">
        <script language="javascript">
        P7_ExpMenu()
        </script>

</div>';
$imagePath = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "/"));


if($server['click'] == "lastimage")
 {
    echo 'This page shows the latast images from the XML file.<br>';
    echo 'The latest images are on the top.<br>';
    echo 'This information is stored in the database, so if you just installed, the information will need to be builtup.<br>';
    echo '<br>';
    echo '<hr>';
        $DBimage = $this->ld->GetSelect("SELECT * FROM Images WHERE url != '' AND download = 'Y' ORDER BY GameVersion  DESC LIMIT 20 ");
        foreach ($DBimage as $images)
        {
          echo '<div  class="ImageItem">';
          echo '<span class="ImageName">'.$images['ItemName'].'</span>';
          echo '<img src="'.$imagePath.$images['url'].'">';
          echo '<span class="ImageVersion">Game Version:'.$images['GameVersion'].'</span>';
          echo '<span class="ImageDown">Downloaded '.$this->ld->nicetime($images['DownloadTime']).' </span>';
          echo '</div>';
        }
    echo 'end<br>';
    echo '<hr>';


 }


 if($server['click'] == "SetName")
 {
    echo 'Here you can change your city name.<br>';
    echo '<br>';
    echo '<hr>';
    echo '<br>';
    echo '<br><form ><input id="reset" type="hidden" value="10" size="4">';
    echo '&nbsp;The new name. Please do not use special signs this could break you city.. <br>
          <input id="CityName" type="text" value="" size="25">';
    echo'   </div>
                <div width="100%" align="center"><br>
                <button id="btn_CityName" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save Name &nbsp;</button>
                </div>
        </form>';

    echo '<hr>';


 }


if($server['click'] == "home")
 {
    echo 'Welcome to the Multi plugin for City ville<br>';
    echo 'Made by 12 christiaan<br>';
    echo '<br>';
    echo '<br>';
    echo '<hr>';
 }

 //whichlist
if($server['click'] == "wishlist")
 {                                    //wishlist
    echo 'You can sellect what items to keep on you wishlist.<br>';
    echo '<hr>';
    echo 'Normaly every time a person give you something, that item disapears from your wishlist.<br>';
    echo 'This plugin will put the sellected items back on the wishlist<br>';
    echo 'You can not have more than 5 items on your wishlist, so the plug-in is only able to add items if there are no more than 5.<br>';
    echo '<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th> Item name</th><th> on list?</th><th>image</th><tr>';

        $xmlsOb=new xmlsOb();
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['giftable'] == "true")
            {
               echo "<tr><td>".(string)$item['name'].'</td>';
               echo '<td><input type="checkbox" id="'.(string)$item['name'].'Gift"></td>';
               //
               $icon = $imagePath.$this->ld->GetIconByItemName((string)$item['name']);
               echo '<td><img src="'.$icon.'" ></td>';
               echo '</tr>';
            }
        }



//    echo $this->GetOptionsByGiftable();
    echo '</table>';
    echo '<br>';
       echo'   </div>
                <div width="100%" align="center"><br>
                <button id="btn_save_wlist" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                </div>
        </form>';

 }
 //whichlist
if($server['click'] == "images")
 {
    echo 'Images.<br>';
    echo '<hr>';
    echo 'There are now images availible to be shown in the plugins.<br>';
    echo 'Normaly the images are downloaded automaticly, but i can happen that there is a error.<br>';
    echo 'If you do not see images where thay should be, than use the reset button below.<br>';
    echo 'When the reset button is pressed, the next time the bot does work, it will check again all the images.<br>';
    echo 'You only have to press 1 time<br>';
    echo '<br>';
    echo '<br><form ><input id="reset" type="hidden" value="10" size="4">';
       echo'   </div>
                <div width="100%" align="center"><br>
                <button id="btn_rst_image" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Reset Images &nbsp;</button>
                </div>
        </form>';

 }
if($server['click'] == "debug")
 {


      var_dump($server);
    echo '<br>';
    echo '<hr>';
     var_dump($_SERVER);
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';

}


if($server['click'] == "userinfo")
 {
echo ' <div style="margin-left: 20px"><h1>User information. </h1><hr><br>';
echo '<table class="table1">';
        $this->rep=Array();
        $res=$this->ld->GetSelect("select * from userInfo");
        foreach ($res as $val){
            if ($val["name"]=="worldName")      echo "<tr><td>World Name</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="currentBonusDay")      echo "<tr><td>Current Bonus Day</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="expansionsPurchased")      echo "<tr><td>Expansions Purchased</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="MaxGoods")      echo "<tr><td>Maximum amount of goods</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="Goods")         echo "<tr><td>Current amount goods</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="gold")          echo "<tr><td>Coins</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="freeGold")      echo "<tr><td>Free Gold</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="cash")          echo "<tr><td>Cash</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="freeCash")      echo "<tr><td>Free Cash</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="level")         echo "<tr><td>Level </td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="xp")            echo "<tr><td>XP</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="energyMax")     echo "<tr><td>Maximum Energy.</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="energy")        echo "<tr><td>Current energy</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="gold_spent")    echo "<tr><td>Coins spent</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="socialLevel")   echo "<tr><td>Social Level</td><td>". $val["value"] . "</td></tr>";
            if ($val["name"]=="socialXp")      echo "<tr><td>Social XP</td><td>". $val["value"] . "</td></tr>";
            //if ($val["name"]=="homeIslandSize")echo "<tr><td></td><td>". $val["value"] . "</td></tr>";
        }
echo '</table></div>';

}

if($server['click'] == "collection")
 {
   $MultiCollection = (array)$this->ld->GetPlSettings('MultiCollection');
echo ' <div style="margin-left: 20px">Collections.<hr><br>';
if($MultiCollection['TradeCollection'] ) {$checked = "checked";}else {$checked = "";}
if($MultiCollection['KeepCollection'] ) {$KeepCol = $MultiCollection['KeepCollection'];}else {$KeepCol = "0";}
echo '<input type="checkbox" id="TradeCollection" '.$checked.'>Trade-in the collections.<br>';
echo '<input id="KeepCollection" type="text" value="'.$KeepCol.'" size="4">&nbsp;How much of each collection to keep. <br>';
echo '<i>If you set Keep to 5, we will keep 5 of each collection, the rest will be trade-in.</i><br>';
echo' <div width="100%" align="center"><br>
        <button id="MultiCollection" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
       </div> </div>
    </form>';
echo '<hr>';
echo 'The collections you have. <br>';
   //Get collection info from xml
   $col = $this->GetCollectionInfo();
   //Get complete collections
   $CollCompl = array();
   $res = ($this->ld->GetSelect("SELECT count(*) as cnt FROM collection WHERE completed >0"));
   $kk = $res[0]['cnt'];
   if ( $kk > 0)
   {
    $res1 = ($this->ld->GetSelect("SELECT * FROM collection WHERE completed >0"));
    foreach($res1 as $res2)
    {
       $CollCompl[$res2['collectionType']] =  $res2['completed'];
    }
   }
   //var_dump($col);
   // Get items from collections
   $Collection2 = array();
   $res = ($this->ld->GetSelect("SELECT count(*) as cnt FROM collectionItems "));
   $kk = $res[0]['cnt'];
   if ( $kk > 0)
   { $res = ($this->ld->GetSelect("SELECT * FROM collectionItems "));
     foreach($res as $CollectionName)
       {  $Collection2[$CollectionName['collection']][$CollectionName['item']] = $CollectionName['amount']; }
   }
   $CollectionText = "The collections you have. \r\n";

   foreach($col as $name => $collection)
    {
        echo '<table  class="table3">'; //width="100%"
                                          
        echo '<tr><td align="center"><b>Collections Name</b></td><td align="center"><b>Completed</b></td>
            <td>Reward Item:</td><td>XP</td><td>Coin</td><td>Energy</td><td>Goods</td></tr>';
        echo '<tr>';
        echo '<td align = "center">'.$name.'</td>';
        echo '<td align = "center">'.$CollCompl[$name].'</td>';
        echo '<td align = "center">'.$col[$name]['tradeInReward']['item'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$name]['tradeInReward']['xp'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$name]['tradeInReward']['coin'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$name]['tradeInReward']['energy'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$name]['tradeInReward']['goods'].'&nbsp;</td>';
        echo '</tr>';
       $CollectionText .= "\r\n" . $name . ' Complete: ' .$CollCompl[$name]." \r\n";

        echo '<tr><td colspan="7" >';
        echo '<table  class="table4"><tr>';
      foreach($collection['collectables'] as $item => $collectables )
      {
       if(isset($Collection2[$name][$collectables])) {$amount = $Collection2[$name][$collectables];}else{$amount = "0";}
       $icon = $imagePath.$this->ld->GetIconByItemName((string)$collectables);
       echo '<td align = "center">'.$collectables.'<br>You have: '.$amount.'<br><img src="'.$icon.'" ></td>';
       $CollectionText .= $name . ' Item: ' .$collectables.' Amount: ' .$amount ." \r\n";
      }
        echo '</tr></table><td></tr>';
        echo '</table><br>';
    }

// show the old table.
echo '<table class="table1">';
echo '<tr><td width="340"  align="center"><b>Collections Name</b></td><td width="60" align="center"><b>Completed</b></td>
      <td>Reward Item:</td><td>XP</td><td>Coin</td><td>Energy</td><td>Goods</td></tr>';
   //$col = $this->GetCollectionInfo();
   $res = ($this->ld->GetSelect("SELECT count(*) as cnt FROM collection WHERE completed >0"));
   $kk = $res[0]['cnt'];
   if ( $kk > 0)
   {
    $res = ($this->ld->GetSelect("SELECT * FROM collection WHERE completed >0"));
    for ($i=0; $i<$kk; $i++)
      {
        echo '<tr>';
        echo '<td align = "center">'.$res[$i]["collectionType"].'</td>';
        echo '<td align = "center">'.$res[$i]["completed"].'</td>';
        echo '<td align = "center">'.$col[$res[$i]["collectionType"]]['tradeInReward']['item'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$res[$i]["collectionType"]]['tradeInReward']['xp'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$res[$i]["collectionType"]]['tradeInReward']['coin'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$res[$i]["collectionType"]]['tradeInReward']['energy'].'&nbsp;</td>';
        echo '<td align = "center">'.$col[$res[$i]["collectionType"]]['tradeInReward']['goods'].'&nbsp;</td>';
        echo '</tr>';
      }
   }else{
            echo '<tr><td align ="center" colspan ="2" height = "100">No Collection found !</td></tr>';
        }
echo '</table></div>';

   $ColFile = 'tmp_dir/'.$this->ld->userId . '-Collection.txt';
        $fl = fopen($ColFile, 'w');
        fwrite($fl, $CollectionText);
        fclose($fl);
}


if($server['click'] == "invOver")
 {
echo ' <div style="margin-left: 20px"> Inventory Overview. <hr><br>';
echo ' <form >';
echo '<table class="table1">';
echo '<tr><td><b>Item Name</b></td><td><b>You have:</b></td></tr>';
$Sett = (array) $this->ld->GetPlSettings('Multiplugin');
$sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Inventory"'));
$InvFileTot = array();
        if ($sel[0]["cnt"] == 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM Inventory'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT * FROM Inventory'));
                foreach ($sel as $n)
                {
                  $ItemName = (string)$n['Item'];
                  if($ItemName == "-"){continue;}
                  $ItemNumber = $n['Number'];
                  $ItemKeep = $n['Keep'];
                  $ItemUse = $n['Use'];
                  $ItemNameKeep = $ItemName . "Keep";

                 // $inputKeep = '<input id="'.$ItemName.'Keep" type="text" value="'.$Sett[$ItemNameKeep].'" size="4">';
                 // $inputUse = '<input type="checkbox" id="'.$ItemName.'Use">';
                  $inputDebug =  strpos($ItemName, "energy");
                  echo '<tr><td>'.$ItemName.'</td><td>'.$ItemNumber."</td>\n";
                  $InvFileTot[$ItemName] = $ItemNumber;
                 // echo '<td>'.$inputKeep.'</td>';
                 // echo '<td>'.$inputUse.'</td>';
                 //echo '<td></td><td></td><td></td><td></td><td></td></tr>';
                  echo '</tr>';


                }
            }
        }
   echo'</table >';
   echo 'user: '.$this->ld->userId . "<br>";
   echo  "<br>";


   //save file
   $InvFile = 'tmp_dir/'.$this->ld->userId . '-Inventory.txt';
        $fl = fopen($InvFile, 'w');
        foreach ($InvFileTot as $name => $amount) {
            fwrite($fl, $name . ' = ' . $amount . "\r\n");
        }
        fclose($fl);


   echo'   </div>';
        //        <div width="100%" align="center"><br>
        //        <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
        //        </div>
   echo' </form>';
}


//  ================================================ SendGift =================
if($server['click'] == "SendGift")
 {
    $XMLconfigFile =  $imagePath."Profiles\\".$this->ld->userId . '__Zy.xml' ;
    $MultiSendGift = (array)$this->ld->GetPlSettings('MultiSendGift');
    $SendGiftKeep = array();
    $SendGiftNeig = array();
    foreach($MultiSendGift as $Item => $Action)
    {
      if(substr($Item,0,5) == 'Item_') { $ItemName = substr($Item, 5); $SendGiftKeep[$ItemName] = $Action; }
      if(substr($Item,0,5) == 'Neig_') { $ItemName = substr($Item, 5); $SendGiftNeig[$ItemName] = $Action; }
    }
//var_dump($SendGiftKeep);
//        echo "<br><hr>";
//var_dump($SendGiftNeig);
//        echo "<br><hr>";
        // get Neighbor list.
        $NoptionList = '<option value="-1" selected="selected">Nobody</option>';
        $Ns = $this->GetNList();

        echo "<br>";
        echo '<div style="margin-left: 20px">Sending inventory as gift to others. <hr><br>';
        echo '<form><input id="XMLconfigFile" type="hidden" value="'.$XMLconfigFile.'" >';
        echo '<b>Step 1</b>&nbsp;&nbsp; Select from each Items how manny you like to KEEP (the rest will be send to others).<br>';
        echo '<b>Step 2</b>&nbsp;&nbsp; Select a Neighbor to send the Item to. &nbsp;&nbsp;&nbsp;When you select Samantha or Nobody, you just lose the gift.<br><br>';

echo '<table class="table1">';
echo '<tr><td><b>Item Name</b></td><td><b>You have</b></td><td>Keep</td><td><b>Send to.</b></td></tr>';
//$Sett = (array) $this->ld->GetPlSettings('Multiplugin');
$sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Inventory"'));
$InvFileTot = array();
        if ($sel[0]["cnt"] == 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM Inventory'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT * FROM Inventory'));
                foreach ($sel as $n)
                {
                  $ItemName = (string)$n['Item'];
                  if($ItemName == "-"){continue;}
                  $ItemNumber = $n['Number'];

                  echo '<tr><td>'.$ItemName.'</td><td>'.$ItemNumber."</td>\n";
                  echo '<td>';
                  if(array_key_exists($ItemName, $SendGiftKeep))
                    { $value = $SendGiftKeep[$ItemName]; } else {$value = 9999;}
                  echo '<input id="Item_'.$ItemName.'" type="text" value="'.$value.'" size="4">';
                  echo '</td><td>';
                  $NoptionList2 = $NoptionList;
                    foreach($Ns as $N)
                      { if(array_key_exists($ItemName, $SendGiftNeig))
                          {  if((string)$SendGiftNeig[$ItemName] == (string)$N["uid"]){$selected = "SELECTED";} else {$selected = "";}
                          }  else {$selected = "";}
                        $NoptionList2 .= '<option value="'. $N["uid"] . '" '.$selected.' >'. $N["cityname"] .'</option>';
                      }
                  echo '<select id="Neig_'.$ItemName.'" >'.  $NoptionList2 . '</select>';
                  echo '</td></tr>';
                }
            }
        }

   echo'</table ><br><br>';
   echo '<b>Step 3</b>&nbsp;Press Save settings. <br>';
   echo'<hr>';
   if($MultiSendGift['ErrorLog'] ) {$checked = "checked";}else {$checked = "";}
   echo '<input type="checkbox" id="ErrorLog" '.$checked.'>Enable Error Log. Enable this when you have problem. Send the log files to 12christiaan for debuging. ( tmp_dir/SendGift/ )<br>';
   echo'<hr>';
   echo' <div width="100%" align="center"><br>
        <button id="btn_SendGift" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
       </div> </div>
    </form>';
   echo  "<br>Please, read the following remarks:<br>";
   echo  "&nbsp;-&nbsp;You can NOT send items that you do not have.<br>";
   echo  "&nbsp;-&nbsp;If you have 0, you can send 0.<br>";
   echo  "&nbsp;-&nbsp;I can not guarantee that the gift will be recieved on the other end.<br>";
   echo  "&nbsp;-&nbsp;When the gift is gone, it is gone.<br>";
   echo  "&nbsp;-&nbsp;The amount of items in the list are only updated after a BOT cycle.<br>";
}

if($server['click'] == "maxgoods")
 {
   $MultiMaxGoods = (array)$this->ld->GetPlSettings('MultiMaxGoods');
   $res=$this->ld->GetSelect("select * from userInfo");
   foreach ($res as $val){ if ($val["name"]=="MaxGoods") $MaxGoods=$val["value"];  }


echo ' <div style="margin-left: 20px">Maximum goods all the time. <hr><br>';
echo 'Setting this option, we will fill your goods (it will cost coins).<br>';
echo ' <form name="mygallery">';
echo ' <hr>';
echo '<b>Before work</b> <br>';
echo '<i>Fill the goods before the filling of buisniss buildings start, so that you have plenty to do so.</i> <br>';
echo '<i></i> <br>';
if($MultiMaxGoods['MultiMaxGoods'] ) {$checked = "checked";}else {$checked = "";}
if($MultiMaxGoods['MultiMaxGoodsFill'] ) {$MultiMaxGoodsFill = $MultiMaxGoods['MultiMaxGoodsFill'];}else {$MultiMaxGoodsFill = 0;}
echo '<input type="checkbox" id="MultiMaxGoods" '.$checked.'>Fill goods before doing work.<br>';
echo '<input id="MultiMaxGoodsFill" type="text" value="'.$MultiMaxGoodsFill.'" size="4">&nbsp;How much Good to fill up. <br>';
echo '<i>Your maximum goods is ' . $MaxGoods . ' (advice to put this at: 50% = '.round(($MaxGoods*0.5), 0) .')</i><br>' ;
echo ' <hr>';
echo '<b>After work</b> <br>';
echo '<i>Fill the goods after all work is done. So if you start the game yourself you have full storage.</i> <br>';
echo '<i></i> <br>';
if($MultiMaxGoods['MultiMaxGoodsAfter'] ) {$checked = "checked";}else {$checked = "";}
if($MultiMaxGoods['MultiMaxGoodsFillAfter'] ) {$MultiMaxGoodsFillAfter = $MultiMaxGoods['MultiMaxGoodsFillAfter'];}else {$MultiMaxGoodsFillAfter = 0;}
echo '<input type="checkbox" id="MultiMaxGoodsAfter" '.$checked.'>Fill goods After doing work.<br>';
echo '<input id="MultiMaxGoodsFillAfter" type="text" value="'.$MultiMaxGoodsFillAfter.'" size="4">&nbsp;How much Good to fill up. <br>';
echo '<i>Your maximum goods is ' . $MaxGoods . ' (advice to put this at: 90% = '.round(($MaxGoods*0.9), 0) .')</i><br>' ;
echo ' <hr>';
echo '<b>What crop to be used.</b> <br>';
echo '<i>Filling the goods is based on harvest crops, so you can leave this default or pick your own.</i> <br>';
echo '<i>If you pick your own, this could be intressting if you like to collect a special collection.</i> <br>';
echo 'Crop:&nbsp;<select name="picture" id="cropsIN" onChange="showimage()">';

echo '<option value="'.$imagePath.'assets/crops/plowed/plot_icon.png">default</option>';
        $xmlsOb=new xmlsOb();
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['type'] == "plot_contract")
            { if($MultiMaxGoods['cropsIN'] == (string)$item['name']) {$sel = 'selected="selected"';}else{$sel ='';}
              foreach($item->image as $icon) {if($icon['name'] == "icon") $imagename = $imagePath ."". $icon['url'];}
              echo "<option $sel value=\"".$imagename."\" >".(string)$item['name']."</option>";
            }
        }
echo '<td width="100%"><p align="center"><img src="'.$imagePath.'assets/crops/plowed/plot_icon.png" name="pictures" ></td>';
echo' <div width="100%" align="center"><br>
        <button id="btn_save_MaxGoods" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
       </div> </div>
    </form>';

}

if($server['click'] == "franchise")
 {
echo ' <div style="margin-left: 20px">Franchise settings & overview. </div><hr><br>';
echo 'Automatically handling of accepting the daily bonus.<br>';
echo ' <form >';
if($franchise['AcceptDayBonusHQ'] ) {$checked = "checked";}else {$checked = "";}
echo '<input type="checkbox" id="AcceptDayBonusHQ" '.$checked.'>Accept daily bonus from Franchise HQ.';
       echo' <div width="100%" align="center"><br>
                <button id="btn_save_HQ" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                </div>
        </form>';


echo 'Franchise owned by you in neighbor city.';
echo '<table class="table1">';
echo '<tr><td>Type:</td><td>Franchise name:</td><td>Where:</td><td>Stars:</td><td>Franchise name:</td><td>Last collected</td><td>Last operated</td><td></td><td></td><td></td><td></td><td></td></tr>';
        $res=$this->ld->GetSelect("select * from franchises");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            $name =$obj['name'];
            $franchise_name      = $obj['franchise_name'];//  Kara's Bike Shop
            $time_last_collected = $obj['time_last_collected'];//  time_last_collected  Number  1293056411
                  echo '<tr>';
                  echo '<td>'.$name.'</td>';
                  echo '<td>'.$franchise_name.'&nbsp;</td>';
                  echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                  echo '</tr>';
            if(isset($obj['locations']))
              {
               foreach($obj['locations'] as $uid => $location)
                {
                  echo '<tr>';
                  echo '<td>'.$name.'</td>';
                  echo '<td>'.$franchise_name.'</td>';
                  echo '<td>'.$uid.'</td>';
                  echo '<td>'.$location['star_rating'].'</td>';//star_rating  Integer  1
                  echo '<td>'.$location['franchise_name'].'</td>'; //franchise_name  String Reference  Kara's Bike Shop
                  echo '<td>'.$this->ld->nicetime($location['time_last_collected']).'</td>';   //time_last_collected  Number  1293056411
                  echo '<td>'.$this->ld->nicetime($location['time_last_operated']).'</td>'; //time_last_operated  Number  1293057003
                  echo '</tr>';
                }
              }
            //$this->rep["classes"][$obj['className']][$obj['itemName']][]=1;
        }
echo '</table ><br><hr>';


echo 'Franchise in you city.';
echo '<table class="table1">';
echo '<tr><td>Id</td><td>Building type:</td><td>State:</td><td>Name:</td><td>Last collected</td><td>Stars:</td><td></td><td></td><td></td><td></td><td></td></tr>';
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            if(isset($obj['franchise_info']))
            {
              echo "<tr><td> " . $obj['id'] . "</td>";
              echo "<td> " . $obj['itemName'] . "</td>";  //itemName  String  bus_shoestore
              echo "<td> " . $obj['state'] . "</td>";  //state  String Reference  closed
              echo "<td> " . $obj['franchise_info']['franchise_name'] . "</td>";  //franchise_name  String  Christiaan's Shoe Store
              echo "<td> " . $this->ld->nicetime($obj['franchise_info']['time_last_collected']) . "</td>";  //time_last_collected  Number  1292754945
              echo "<td> " . $obj['franchise_info']['balls_tossed'] . "</td>";  //balls_tossed  Integer  5
              echo "</tr>";
            }

            //$this->rep["classes"][$obj['className']][$obj['itemName']][]=1;
        }
        echo '</table >';

echo 'Franchise Headquarter in you city.';
echo '<table class="table1">';
echo '<tr><td>Id</td><td>Building type:</td><td>State:</td><td>Name:</td><td>Last collected</td><td>Stars:</td><td></td><td></td><td></td><td></td><td></td></tr>';
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            if($obj['className'] == "Headquarter")
            {
              echo "<tr><td> " . $obj['id'] . "</td>";
              echo "<td> " . $obj['itemName'] . "</td>";  //itemName  String  bus_shoestore
              echo "<td> " . $obj['state'] . "</td>";  //state  String Reference  closed
              echo "<td> " . $obj['builtFloorCount'] . "</td>";  //builtFloorCount
              echo "</tr>";
            }
        }
        echo '</table >';

}



if($server['click'] == "energy")
 {
echo ' <div style="margin-left: 20px"> Inventory Energy Overview. <hr><br>';
echo 'For now the Inventory handling is <b>limmited to the enery</b>.<br>';
echo '<br>';
echo 'Sellect how many of the items you like to KEEP in your Inventory. (the rest will be used)<br>';
echo 'Switch on the action (use), to activate the usage of the item.<br>';
echo '<br> <form >';
echo '<table class="table2">';
echo '<tr><th>Item Name</th><th>You have:</th><th>Keep in Inventory</th><th>Use: Y/N</th></tr>';
$sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Inventory"'));

        if ($sel[0]["cnt"] == 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM Inventory'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT * FROM Inventory'));
                foreach ($sel as $n)
                {
                  $ItemName = (string)$n['Item'];
                  $ItemNumber = $n['Number'];
                  $ItemKeep = $n['Keep'];
                  $ItemUse = $n['Use'];
                  $ItemNameKeep = $ItemName . "Keep";

                  if(strpos($ItemName, "nergy") != 0)
                     {
                       $inputKeep = '<input id="'.$ItemName.'Keep" type="text" value="'.$MultiEnergy[$ItemNameKeep].'" size="4">';
                       if($MultiEnergy[$ItemName.'Use'] ) {$checked = "checked";}else {$checked = "";}
                       $inputUse = '<input type="checkbox" id="'.$ItemName.'Use" '.$checked.'>';
                  $inputDebug =  strpos($ItemName, "energy");
                  echo '<tr><td>'.$ItemName.'</td><td>'.$ItemNumber."</td>\n";
                  echo '<td>'.$inputKeep.'</td>';
                  echo '<td>'.$inputUse.'</td>';
                  //echo '<td></td>';
                  //echo '<td></td>';
                  //echo '<td>'.$inputDebug.'</td>';
                  echo '</tr>';
                     }

                  //<input id="constr" type="text" value="0" size="4">

                }
            }
        }
        echo '</table >';
       echo'   </div>
                <div width="100%" align="center"><br>
                <button id="btn_MultiEnergy" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                </div>
        </form>';
}



if($server['click'] == "neighborLot")
 {
echo ' <div style="margin-left: 20px"> Neighbors lot site. <hr><br>';
echo '<br>';
echo'       <div class="zag" >Neighbors that have open space for your busniss<br><hr><br>
                This list is filled after you have visited your neighbors. It will stay for 24 hours in the list, before to be deleted from the list.
                </div>
                <div style="margin-left: 20px">
                    <table id="maint" class="table2">
                       <tr id="up">
                       <td  valign="top"><span id="lotsite"></span></td>
                       </tr>
                    </table>
                </div>';
}








// footer for every page.
echo'<hr>If you like my work on these plugins, please concider a donation.<br>
     <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=R64L7E9DBEKTY" class="postlink" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" alt="Image" ></a>
     <hr>';
     //var_dump($server);
if($server['action'] == "refresh")
  { echo'     <br><br><br><br><br>';
    echo '  <script  src="http://tag.contextweb.com/TagPublish/getjs.aspx?action=VIEWAD&cwrun=200&cwadformat=728X90&cwpid=531205&cwwidth=728&cwheight=90&cwpnet=1&cwtagid=99476"></script> ';
  }

echo ' </body></html>';
}
}
?>
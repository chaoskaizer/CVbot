<?php

class WallPlugin {
    var $ld;
    var $DB;

    function WallPlugin() {
        $this->ld = new LocalData();
        $this->DB = new LocalDB();
        $this->DB->ConnectTo("User", "Wall");
        $this->DB->InitDB();
    }
    
    function Display(){
        $res = ($this->ld->GetSelect('SELECT * FROM objects where ClassName = "LotSite"'));
        $count = count($res);
        $coin = number_format($count * 100000);
        If ($count > "0"){
            echo '<tr><th scope="col">How many cycle you want to run ? </th></tr>';
            echo '<tr><td class="row" align="center"><input type="text" id="cycle" value="0" size="5"></tr>';
            echo '<tr><td class="row" align="center">Speed X' .$count . '&nbsp;&nbsp;$' . $coin . ' coins/cycle</tr>';
            echo '<tr><td align="center"><button id="save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp;Save &nbsp;</button></td></tr>';
        }else{
            echo '<tr><td height="200" align="center"><font color="red"><b>No empty business lot detected !!! </b></font></td></tr>';
        }
    }
           
    function GetForm($server) {
$WallSet = array();
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

<style>
body                {  }
.Wall1              { font-family: "lucida grande",tahoma,verdana,arial,sans-serif; font-size: 11px; color: #333; text-align: left; direction: ltr;
                        margin-top:6px;overflow:hidden;padding-right:10px;margin-bottom:5px}
.Wall1_Media        {float:left;overflow:hidden;padding-right:10px}
.Wall1_MediaSingle  {padding-right:10px}
.Wall1_Title        {font-weight:bold;padding-top:3px}
.Wall1_Info         {display:table}
.Wall1_Caption      {color:#808080;padding-top:3px}
.Wall1_Caption a    {color: #6D84B4; font-weight:bold;}
.Wall3 img          {display:block}
</style>



<script>
    $(document).ready(function(){                                                                                  

                            window.settings=eval(' . json_encode($this->ld->GetPlSettings('Wall')) . ');

                              if ((window.settings!==null) && (window.settings!==undefined)){
                                $("#RunTime").val(window.settings.RunTime);

                                if ((window.settings.Run!==null) && (window.settings.Run!==undefined)){
                                    if(window.settings["Run"]==1){
                                        $("#Run").attr("checked", true);
                                    }
                                }

                            }

     //==============================================================
     $("#btn_youget").click(function(){
       var req=new Object();
      // $(":input").each(function(){
      //       req[$(this).attr("id")]=$(this).val();
      //   });
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });

       data=$.toJSON(req);
       //alert(data);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SavePost&What=youget&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //==============================================================
     $("#btn_othergetxp").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SavePost&What=othergetxp&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //==============================================================
     $("#btn_othergetcoin").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SavePost&What=othergetcoin&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //==============================================================
     $("#btn_othergetgood").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SavePost&What=othergetgood&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
     //==============================================================
     $("#btn_othergetitem").click(function(){
       var req=new Object();
       $(":checkbox").each(function(){  var par=$(this).attr("id");  req[par]=$(this).attr("checked");   });
       data=$.toJSON(req);
             var l=window.location.toString();
             var indx=l.indexOf(\'?\');
             var nurl=l.slice(0, indx)+"?action=SavePost&What=othergetitem&tmp="+Math.random();
             $.post(nurl, data);
             return false;
        });
      //==============================================================



                            $("#btn_save").click(function(){
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
                                    var nurl=l.slice(0, indx)+"?action=save&tmp="+Math.random();
                                    $.post(nurl, data);

                                    return false;
                            });
    });
        </script>

</head>
<body >
               <H1>Wall ver 0.1 by 12christiaan</H1>';
echo'<div id="menuwrapper">
<ul id="p7menubar">
    <li><a href="/Plugins/Wall/Main.php?action=menu&click=home">Home</a></li>
    <li><a class="trigger" href="/Plugins/Wall/Main.php?action=menu&click=setting">Settings</a>
      <ul>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=setting">Settings</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Wall selector</a>
      <ul>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=youget">You get</a></li>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=othergetxp">The other get XP</a></li>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=othergetcoin">The other get coins</a></li>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=othergetgood">The other get goods</a></li>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=othergetitem">The other get items</a></li>
      </ul>
    </li>
    <li><a class="trigger" href="#">Requests</a>
      <ul>
        <li><a href="/Plugins/Wall/Main.php?action=menu&click=indb">Requests in database</a></li>
      </ul>
    </li>
</ul>
<br class="clearit">
        <script language="javascript">
        P7_ExpMenu()
        </script>

</div>';

$now = time();
$yesterday =$now - (24*60*60);

    $WallSet["Request"] = (array)$this->ld->GetPlSettings('WallRequests');


if($server['click'] == "cleanDB" )
 {
    $now = time();
    $cleantime = $now - ($server['cleantime']*60*60);
    $query = "DELETE FROM Request WHERE time < '".$cleantime."';";
    $this->DB->ExecQuery($query);
    echo 'Cleaning of database done.<br>';


  $server['click'] = "setting";
 }
//============================================================================================================
if($server['click'] == "setting" )
 {
    echo '<br>Settings. <br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<b>Clean the database.</b><br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=36">HERE 36</a> to clean clean request older than 36 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=24">HERE 24</a> to clean clean request older than 24 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=12">HERE 12</a> to clean clean request older than 12 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=6">HERE 6</a> to clean clean request older than 6 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=4">HERE 4</a> to clean clean request older than 4 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=2">HERE 2</a> to clean clean request older than 2 hours.<br>';
    echo 'Click <a href="/Plugins/Wall/Main.php?action=menu&click=cleanDB&cleantime=0">HERE ALL</a> to clean clean ALL request.<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';


}
//============================================================================================================
if($server['click'] == "" || $server['click'] == "home")
 {
    echo '<br>Welcome the the Wall Plugin. <br>';
    echo '<br>';
    echo 'Via this plugin you can generate requests, like you would post them on the facebook wall.<br>';
    echo 'These requests will <b>not be publiched</b> on the facebook wall<br>';
    echo 'A file will be generated, this file you can open in a web browser and click the links.<br>';
    echo '<br>';
    echo 'Note that you can NOT send items to your selfs, so send the file to you frends to click.<br>';
    echo 'OR login with a different username to facebook before clicking the links.<br>';
    echo '<br>';
    echo '<b>How to start.</b><br>';
    echo 'Step 1: In the menu above under "Wall Selector", select for what you like to generate the requests. <br>';
    echo 'Step 2: Select the items you need and press SAVE.<br>';
    echo 'Step 3: Let the bot run. <br>';
    echo 'Step 4: Check the tmp_dir/Wall folder. You will see .html files there.<br>';
    echo '&nbsp;&nbsp; each hour a new file is created.<br>';
    echo '&nbsp;&nbsp; a file contains requests for the last 48 hours. (that is how long a request is valite, after 48h the request is deleted from the Z servers.<br>';
    echo 'Step 5: Open the file with a brouwser (you need to be loged in to facebook). See instruction in that file.<br>';
    echo '&nbsp;&nbsp;<br>';
    echo '&nbsp;&nbsp;<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
 }
//============================================================================================================
if($server['click'] == "youget")
 {
    $WallSet[$server['click']] = (array)$this->ld->GetPlSettings('Wall_'.$server['click']);
    echo '<br>';
    echo 'On this page select the Wall request that will give <b>You</b> items, when the other click them.<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th>Generate?</th><th>Request name</th><th>Wait time (hours)</th><th>You get</th><th>Helper get</th><th>Max. helpers</th><tr>';
    $query = "SELECT * FROM viral WHERE hostRewardsType != ''";
    $res = $this->DB->GetSelect($query) ;
    foreach ($res as $item)
     {    if($WallSet["Request"][$item['name']]) {$checked = "checked";}else {$checked = "";}
          echo '<td><input type="checkbox" id="'.$item['name'].'" '.$checked.'></td>';
          echo '<td>'.$item['name'].'</td>';
          echo '<td>'.$item['timeTillReset'].'</td>';
          echo '<td>'.$item['hostRewardsAmount'].' '.$item['hostRewardsType'].'</td>';
          echo '<td>'.$item['helperRewardsAmount'].' '.$item['helperRewardsType'].'</td>';
          echo '<td>'.$item['rewardmaxHelpers'].'</td>';
          echo '</tr>';
     }
    echo '</table><br></div><div width="100%" align="center"><br>
          <button id="btn_'.$server['click'].'" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
          </div></form>';
 } // end if server click
//============================================================================================================
if($server['click'] == "othergetxp")
 {
    $WallSet[$server['click']] = (array)$this->ld->GetPlSettings('Wall_'.$server['click']);
    echo '<br>';
    echo 'On this page select the Wall request that will give <b>The other who cliks XP</b>, when the other click them.<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th>Generate?</th><th>Request name</th><th>Wait time (hours)</th><th>You get</th><th>Helper get</th><th>Max. helpers</th><tr>';
    $query = "SELECT * FROM viral WHERE helperRewardsType = 'reward_xp'";
    $res = $this->DB->GetSelect($query) ;
    foreach ($res as $item)
     {    if($WallSet["Request"][$item['name']]) {$checked = "checked";}else {$checked = "";}
          echo '<td><input type="checkbox" id="'.$item['name'].'" '.$checked.'></td>';
          echo '<td>'.$item['name'].'</td>';
          echo '<td>'.$item['timeTillReset'].'</td>';
          echo '<td>'.$item['hostRewardsAmount'].' '.$item['hostRewardsType'].'</td>';
          echo '<td>'.$item['helperRewardsAmount'].' '.$item['helperRewardsType'].'</td>';
          echo '<td>'.$item['rewardmaxHelpers'].'</td>';
          echo '</tr>';
     }
    echo '</table><br></div><div width="100%" align="center"><br>
          <button id="btn_'.$server['click'].'" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
          </div></form>';
 } // end if server click
//============================================================================================================
if($server['click'] == "othergetcoin")
 {
    $WallSet[$server['click']] = (array)$this->ld->GetPlSettings('Wall_'.$server['click']);
    echo '<br>';
    echo 'On this page select the Wall request that will give <b>The other who cliks coins</b>, when the other click them.<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th>Generate?</th><th>Request name</th><th>Wait time (hours)</th><th>You get</th><th>Helper get</th><th>Max. helpers</th><tr>';
    $query = "SELECT * FROM viral WHERE helperRewardsType = 'reward_coins'";
    $res = $this->DB->GetSelect($query) ;
    foreach ($res as $item)
     {    if($WallSet["Request"][$item['name']]) {$checked = "checked";}else {$checked = "";}
          echo '<td><input type="checkbox" id="'.$item['name'].'" '.$checked.'></td>';
          echo '<td>'.$item['name'].'</td>';
          echo '<td>'.$item['timeTillReset'].'</td>';
          echo '<td>'.$item['hostRewardsAmount'].' '.$item['hostRewardsType'].'</td>';
          echo '<td>'.$item['helperRewardsAmount'].' '.$item['helperRewardsType'].'</td>';
          echo '<td>'.$item['rewardmaxHelpers'].'</td>';
          echo '</tr>';
     }
    echo '</table><br></div><div width="100%" align="center"><br>
          <button id="btn_'.$server['click'].'" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
          </div></form>';
 } // end if server click
//============================================================================================================
if($server['click'] == "othergetgood")
 {
    $WallSet[$server['click']] = (array)$this->ld->GetPlSettings('Wall_'.$server['click']);
    echo '<br>';
    echo 'On this page select the Wall request that will give <b>The other who cliks GOODS</b>, when the other click them.<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th>Generate?</th><th>Request name</th><th>Wait time (hours)</th><th>You get</th><th>Helper get</th><th>Max. helpers</th><tr>';
    $query = "SELECT * FROM viral WHERE helperRewardsType = 'reward_goods'";
    $res = $this->DB->GetSelect($query) ;
    foreach ($res as $item)
     {    if($WallSet["Request"][$item['name']]) {$checked = "checked";}else {$checked = "";}
          echo '<td><input type="checkbox" id="'.$item['name'].'" '.$checked.'></td>';
          echo '<td>'.$item['name'].'</td>';
          echo '<td>'.$item['timeTillReset'].'</td>';
          echo '<td>'.$item['hostRewardsAmount'].' '.$item['hostRewardsType'].'</td>';
          echo '<td>'.$item['helperRewardsAmount'].' '.$item['helperRewardsType'].'</td>';
          echo '<td>'.$item['rewardmaxHelpers'].'</td>';
          echo '</tr>';
     }
    echo '</table><br></div><div width="100%" align="center"><br>
          <button id="btn_'.$server['click'].'" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
          </div></form>';
 } // end if server click
//============================================================================================================
if($server['click'] == "othergetitem")
 {
    $WallSet[$server['click']] = (array)$this->ld->GetPlSettings('Wall_'.$server['click']);
    echo '<br>';
    echo 'On this page select the Wall request that will give <b>The other who cliks items</b>, when the other click them.<br>';
    echo '<br><form >';
    echo '<table class="table2">';
    echo '<tr><th>Generate?</th><th>Request name</th><th>Wait time (hours)</th><th>You get</th><th>Helper get</th><th>Max. helpers</th><tr>';
    $query = "SELECT * FROM viral WHERE helperRewardsType = 'reward_item'";
    $res = $this->DB->GetSelect($query) ;
    foreach ($res as $item)
     {    if($WallSet["Request"][$item['name']]) {$checked = "checked";}else {$checked = "";}
          echo '<td><input type="checkbox" id="'.$item['name'].'" '.$checked.'></td>';
          echo '<td>'.$item['name'].'</td>';
          echo '<td>'.$item['timeTillReset'].'</td>';
          echo '<td>'.$item['hostRewardsAmount'].' '.$item['hostRewardsType'].'</td>';
          echo '<td>'.$item['helperRewardsAmount'].' '.$item['helperRewardsType'].'</td>';
          echo '<td>'.$item['rewardmaxHelpers'].'</td>';
          echo '</tr>';
     }
    echo '</table><br></div><div width="100%" align="center"><br>
          <button id="btn_'.$server['click'].'" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
          </div></form>';
 } // end if server click
// =============== show help ===========================================================================
if($server['click'] == "youget" || $server['click'] == "othergetxp"|| $server['click'] == "othergetcoin"|| $server['click'] == "othergetgood"|| $server['click'] == "othergetitem")
 {
    echo '<b>Help:</b><br>';
    echo '<table class="table2">';
    echo '<tr><td>Generate?        </td><td>Select this to include them on the wall requests.</td></tr>';
    echo '<tr><td>Request name     </td><td>The name of the request.</td></tr>';
    echo '<tr><td>Wait time (hours)</td><td>You can make this request ever xx hours.</td></tr>';
    echo '<tr><td>You get          </td><td>What YOU will get when the helper clicks this.</td></tr>';
    echo '<tr><td>Helper get       </td><td>What HELPER will get when the helper clicks this.</td></tr>';
    echo '<tr><td>Max. helpers     </td><td>Who many helpers can click/use the request.</td></tr>';
    echo '</table>';
    echo '<hr>';
 }
// =====================================================================================================
// indb
//============================================================================================================
if($server['click'] == "indb")
 {
   $query = "SELECT * FROM Request WHERE result='OK' ORDER BY time desc";
   $res = $this->DB->GetSelect($query) ;
    echo '<br>';
    echo 'On this page you see the requests in the database.<br>';
    foreach ($res as $item)
     {
        echo '<div class="Wall1" ><div class="Wall1_Media Wall1_MediaSingle" ><div class="Wall3">';
        echo ' <img class="img" src="'.$item['image'].'"></div></div>';
        echo '<div class="Wall1_Info "><div class="Wall1_Title">';
        echo  htmlspecialchars_decode($item['title']) ;
        echo '</div><div class="Wall1_Caption">';
        echo  $item['type'] ;
        echo '</div><div class="Wall1_Caption">';
        echo $this->ld->nicetime($item['time']) . '&nbsp; via CityVile&nbsp;<a href="'.$item['butonHref'].'" target="_blank">'.$item['butonText'].'</a>';
        echo '</div>';
        echo '</div></div><hr>';
     }
 } // end if server click


// =====================================================================================================
//                     Database maintanance.
//============================================================================================================
   // check if we need to update the viral table
   // update every 24 hours.
   $query = "SELECT value FROM settings WHERE item='viral_updated'";
   $res = $this->DB->GetSelect($query) ;
      if($res['0']['value'] < $yesterday)
       {
        echo "Generating new DB table (runs every 24 Hours). <br>";
              // generate the viral table.
              $xmlsOb=new xmlsOb();
              foreach ($xmlsOb->gsXML->virals->viral as $item)
              {
                     $name                 = (string)$item['name'];
                     $timeTillReset        = (string)$item->timeTillReset; if($timeTillReset == "")$timeTillReset=0;
                     $maxHostRewards       = (string)$item->reward->expirationTime;
                     $rewardexpirationTime = (string)$item->reward->expirationTime;
                     $rewardmaxHelpers     = (string)$item->reward->maxHelpers;
                     $rewardhelperRewardMessage = (string)$item->reward->helperRewardMessage['key'];
                     $hostRewardsType      = (string)$item->reward->hostRewards->reward['type'];
                     $hostRewardsAmount    = (string)$item->reward->hostRewards->reward->data['amount'];
                     $helperRewardsType    = (string)$item->reward->helperRewards->reward['type'];
                     $helperRewardsAmount  = (string)$item->reward->helperRewards->reward->data['amount'];

               $query = "REPLACE  into viral values ('$name','$timeTillReset','$maxHostRewards','$rewardexpirationTime','$rewardmaxHelpers','$rewardhelperRewardMessage','$hostRewardsType','$hostRewardsAmount','$helperRewardsType','$helperRewardsAmount')";
               $this->DB->ExecQuery($query);
              }
          $query = "REPLACE into settings values ('viral_updated','". time()."')";
          $this->DB->ExecQuery($query);
       }
       else
       {
         echo "DB table is up to date (runs every 24 Hours). <br>";
       }
//============================================================================================================
    $now = time();
    $h48h = $now - (48*60*60);
// clean all entries older than 48 hours.
    $query = "DELETE FROM Request WHERE time < '".$h48h."';";
    $this->DB->ExecQuery($query);
// clean all entries that are not OK.
    $query = "DELETE FROM Request WHERE result != 'OK';";
    $this->DB->ExecQuery($query);
// clean all entries where there is no Href
    $query = "DELETE FROM Request WHERE butonHref = '';";
    $this->DB->ExecQuery($query);


//    echo 'You can sellect what items to generate requests for.<br>';
//    echo '<hr>';
//    echo 'requests.<br>';
//    echo '<br><form >';
//    echo '<table class="table2">';
//    echo '<tr><th>Request name</th><th>time Till Reset</th><th>expiration Time</th><th>max Helpers</th><th>host Rewards</th><th>helper Rewards</th><th>Reward Message</th><tr>';
//    $test1 = array();
//        $xmlsOb=new xmlsOb();
//        foreach ($xmlsOb->gsXML->virals->viral as $item)
//        {
//           $test1[] = (string)$item['name'];
//               echo "<tr><td>".(string)$item['name'].'</td>';
//               echo "<td>".(string)$item->timeTillReset.'</td>';
//               echo "<td>".(string)$item->reward->expirationTime.'</td>';
//               echo "<td>".(string)$item->reward->maxHelpers.'</td>';

//               echo "<td>".(string)$item->reward->hostRewards->reward['type'].' <br> ';
//               echo " ".(string)$item->reward->hostRewards->reward->data['amount'].'</td>';

//               echo "<td>".(string)$item->reward->helperRewards->reward['type'].' <br> ';
//               echo " ".(string)$item->reward->helperRewards->reward->data['amount'].'</td>';

//               echo "<td>".(string)$item->reward->helperRewardMessage['package'].'<br>';
//               echo " ".(string)$item->reward->helperRewardMessage['key'].'</td>';

//               echo '</tr>';
//        }

//    echo '</table>';
//    echo '<br>';
//       echo'   </div>
//                <div width="100%" align="center"><br>
//                <button id="btn_save_wlist" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
//                </div>
//        </form>';

//    echo '<br>';
//    echo '<br>';
    //    var_dump($test1);
    echo '<br><hr>';
    echo '<script  src="http://tag.contextweb.com/TagPublish/getjs.aspx?action=VIEWAD&cwrun=200&cwadformat=728X90&cwpid=531205&cwwidth=728&cwheight=90&cwpnet=1&cwtagid=99350"></script> ';

// }

echo '    </body>
</html>
   ';
    }

}















class LocalDB
{

    var $db;
    var $dbfile;
    var $userId;

    // ==========================================================================
    function LocalDB() {

    }

    // ==========================================================================
    function ConnectTo($userid, $DBname)
    {
        $this->dbfile = 'Profiles\\' . $userid . '_'.$DBname.'.sqlite';
        $this->db = new SQLite3($this->dbfile);
        $this->ExecQuery("PRAGMA cache_size=200000");
        $this->ExecQuery("PRAGMA synchronous=OFF");
        $this->ExecQuery("PRAGMA journal_mode=MEMORY");
        $this->ExecQuery("PRAGMA temp_store=MEMORY");
        $this->ExecQuery("vacuum");
    }
    // ==========================================================================
    function ExecQuery($query) {
        if ($this->db) {
            $this->db->exec($query) OR $this->ExecQueryexec($query, 3,$DB);      //prevent lock error
        }
    }
    // ==========================================================================
    function EasyConnect() {
        $this->ConnectTo("User", "Wall");
    }
    // ==========================================================================
    function SaveDBSettings($item, $settings) {
        echo "There is no table \n";
        if ($this->db)
        {
          $this->ExecQuery("UPDATE settings SET value='" . serialize($settings) . "' where item='".$item."'");
        }else{
           echo "There is no table \n";
        }
    }
    // ==========================================================================
    function ExecQueryexec($query, $try) {
        if ($this->db)
        {
           while($try > 0)
            {
              usleep(250);
              $try--;
              $this->db->exec($query) OR $this->ExecQueryexec($query, $try);
              echo "Retry DB." . $try;
            }
        }
    }

    // ==========================================================================
    function GetSelect($query) {
        $arr = Array();
        if ($this->db) {
        if($res = $this->db->query($query))
          { // true = good
            while ($row = @$res->fetchArray())
            {  $arr[] = $row;  }
          }
//          else
//          {  $this->DBerror($query);
             // error code here.
//            echo "<br>\n";
//            echo "*************************************** <br>\n";
//            echo "** The Database was not responding    <br>\n";
//            echo "** Please try refresh in few seconds. <br>\n";
//            echo "**  <br>\n";
//            echo "** If you see this a lot, try not to use the plugins when BOT is doing work. <br>\n";
//            echo "** Stop the BOT before using the plug-in's <br>\n";
//            echo "*************************************** <br>\n";
//            die;
//          }
        }
        return $arr;
    }

    // ==========================================================================
    function InitDB()
    {

       // $this->ExecQuery("drop table if exists 'settings'");
        $this->ExecQuery("CREATE TABLE if not exists [settings] ([item] NVARCHAR(25)  NULL,[value] VARCHAR(15000) NULL,UNIQUE (item))");
        $this->ExecQuery("CREATE INDEX if not exists [IDX_settings1_] ON [settings]([item]  ASC)");

       // $this->ExecQuery("drop table if exists 'Request'");        //$uid','$now','$title','$image','$description','$butonHref','$butonText','$feed_type')";
        $this->ExecQuery("CREATE TABLE if not exists [Request] ([uid] NVARCHAR(15)  NULL,[time] VARCHAR(15) NULL,[title] VARCHAR(150) NULL,[type] VARCHAR(500) NULL,[image] VARCHAR(500) NULL,[description] VARCHAR(500) NULL,[butonHref] VARCHAR(500) NULL,[butonText] VARCHAR(500) NULL,[feed_type] VARCHAR(500) NULL,[result] VARCHAR(500) NULL)");
        $this->ExecQuery("CREATE INDEX if not exists [IDX_Request1_] ON [Request]([uid]  ASC)");

       // $this->ExecQuery("drop table if exists 'viral'");
        $this->ExecQuery("CREATE TABLE if not exists [viral] ([name] NVARCHAR(25)  NULL,[timeTillReset] VARCHAR(15) NULL,[maxHostRewards] VARCHAR(5) NULL,[rewardexpirationTime] VARCHAR(15) NULL,[rewardmaxHelpers] VARCHAR(5) NULL,[rewardhelperRewardMessage] VARCHAR(50) NULL,[hostRewardsType] VARCHAR(50) NULL,[hostRewardsAmount] VARCHAR(5) NULL,[helperRewardsType] VARCHAR(50) NULL,[helperRewardsAmount] VARCHAR(5) NULL,UNIQUE (name))");
        $this->ExecQuery("CREATE INDEX if not exists [IDX_viral1_] ON [viral]([name]  ASC)");
    }
} // end class LocalDB










?>



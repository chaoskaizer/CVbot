<?php
class SellPlugin {
    var $ld;
    var $bot;
    var $rep;

    // ==========================================================================
    function SellPlugin()
    {
        $this->ld = new LocalData();
        $bot = new Bot();
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
    function GetOptionsByPlaceable()
    {
        $xmlsOb=new xmlsOb();
        $res="";    //placeable="true"
        foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['placeable'] == "true"){
                $res=$res."<option>".(string)$item['name']."</option>";
            }
        }
        return $res;
    }
    // ==========================================================================
    function NlistCheck() {
        $is = false;
        $res = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Sell"'));
        
        if ($res[0]["cnt"]==1) {
            $is = true;
        }
        return $is;
    }
    // ==========================================================================
    function GetNList() {
        $res = "";
        $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Sell"'));
        if ($sel[0]["cnt"] == 1) {
            $sel = ($this->ld->GetSelect('SELECT count(*) as cnt FROM Sell'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($this->ld->GetSelect('SELECT value FROM Sell'));
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
    function GetInfoFromDB()
    {
        $this->rep=Array();
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            $this->rep["classes"][$obj['className']][$obj['itemName']][]=1;
        }
    }
    // ==========================================================================
// ========================= nice time. Facebook style.
function nicetime($date)
{
    if(empty($date)) {
        return "No date provided";
    }
   
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
//    $unix_date       = strtotime($date);  // Do not provide real date, but unix timestamp
    $unix_date       = $date;
   
       // check validity of date
    if(empty($unix_date)) {   
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = "ago";
       
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
   
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
   
    if($difference != 1) {
        $periods[$j].= "s";
    }
   
    return "$difference $periods[$j] {$tense}";
}
    // ==========================================================================

    function ObjectTable($rep)
    {
        $res='<table width="400" border="1" bordercolor="black" ><tr><td colspan="2" align="center" class="zag">Objects</td></tr>';
        foreach ($rep["classes"] as $key=>$value){
            $res=$res . '<tr><th colspan="2" align="left">'.$key.'</th></tr>';
            foreach ($value as $name=>$count){
                $res=$res . '<tr><td align="center">'.$name.'</td><td align="center" width="100">'.count($count).'</td></tr>';
            }

        }
        $res=$res.'</table>';
        return $res;
    }
    // ==========================================================================




    function GetForm()
    {
    //GetInfoFromDB();
         echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
        <title>Sell helper</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        <style>
            body{  background-color: rgb(255, 249, 200);    font-size: 10pt;    }
            .zag{  font-size: 15pt;  font-weight: bold;   }
            .zag2{ font-size: 13pt;  font-weight: bold;     width: 300px;       }
            #nsp{  width: 150px;  }
            #maint{  font-size: 10pt;  width: 600px;  padding: 0px;       }
            #rcontent{          padding-left: 20px;                 }
            #treesIN, #treesIN2, #cropsIN, #animalIN, #debrisIN{   width: 200px;   }
        </style>
        <script>
        //======================================================================
            $(document).ready(function(){
                            window.settings=eval(' . json_encode($this->ld->GetPlSettings('Sellplugin')) . ');

                            //==============================================================
                            if ((window.settings!==null) && (window.settings!==undefined)){
                                $("#itemid").val(window.settings.itemid);

                                $("#SellIN").val(window.settings.SellIN);
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

                            $("input:text").each(function(){
                                    req[$(this).attr("id")]=$(this).val();
                            });
                            req["itemid"]=$("#itemid").val();
                            req["SellIN"]=$("#SellIN").val();

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
        //======================================================================
        </script>
    </head>
    <body>
        <form >
                <div class="zag" height="35">Sell (Plugin by 12Christiaan) Version 0.3<hr></div>
                <div style="margin-left: 20px">
                This plugin allows you to remove items from your farm<br>
                this could be handy if your farm is now loading any more, because there are faulty items on your farm.<br>
                <br>
                Use with extrem warning, this can break your farm (even more)<br>
                Use at own risk<br>
                <br>
                Look into the list below, remember the id that you need to remove from your farm.<br>
                give the id number in the the box and hit save.<br>
                run the next cycle<br>
                <br>

                <table id="maint" border="0">
                       <tr id="up">
                          <tr BGCOLOR="grey"><td><b>What to sell?</b></td><td></td></tr>
                          <tr><td>Item id: </td><td><input id="itemid" type="text" value="0" size="4">&nbsp;</td></tr>';
   echo '<tr><td>OR sellect the item: </td><td><select id="SellIN">';
   echo '<option>Do not sell all items</option>';
   echo $this->GetOptionsByPlaceable();
   echo '</select></td></tr>';


echo '          </table><br>
              <br><input id="Sell" type="checkbox" value="0" size="4"> Start selling on next cycle.<br>
                </div>
                <div width="100%" align="center"><br>
                <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                </div>
        </form>
    <hr>';

echo '<br><br>';
        $rep=Array();

        $res=$this->ld->GetSelect("select * from objects");
        $is=1;
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            $rep["obj"][$i] = $obj;
            $i++;
        }

        echo 'Objects <br><table width="400" border="1" bordercolor="black" >
        <tr><td>Id</td><td>ItemName</td><td>Classname</td><td>State</td><td>Position x</td><td>Position y</td><td>Item owner</td><td>Start time</td><td></td></tr>';
        foreach ($rep["obj"] as $value)
          {
            $startTime = $this->nicetime($value['startTime']/1000);
            echo '<tr><td align="center">'.$value['id'].'</td><td>&nbsp;'.$value['itemName'].'</td><td >&nbsp;'.$value['className'].'</td><td >&nbsp;'.$value['state'].'</td><td >&nbsp;'.$value['position']['x'].'</td><td >&nbsp;'.$value['position']['y'].'</td><td >&nbsp;'.$value['itemOwner'].'</td><td >&nbsp;'.$startTime.'</td></tr>';
          }
        echo'</table>';
        echo ' </body></html>';
    }
}

?>
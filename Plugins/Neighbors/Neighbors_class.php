<?php
class NeighborsPlugin {
    var $ld;
    var $bot;
    var $np;

    // ==========================================================================
    function NeighborsPlugin()
    {
        $this->ld = new LocalData();
        $bot = new Bot();
        //$np = new NeighborsPlugin();

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
    function NlistCheck() {
        $is = false;
        $res = ($this->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="neighbors"'));
        
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




    function GetForm()
    {
$PlVersion = (array)$this->ld->GetPlVersion('neighborsplugin');
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
        <title>Neighbors helper</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        <style>
            body{  background-color: rgb(255, 249, 200);   font-size: 10pt;    }
            .zag{  font-size: 15pt;          font-weight: bold;          }
            .zag2{ font-size: 13pt;          font-weight: bold;        width: 300px;       }
            #nsp{    width: 150px;            }
            #maint{  font-size: 10pt;    width: 600px;     padding: 0px;     }
            #rcontent{  padding-left: 20px;   }
            #treesIN, #treesIN2, #cropsIN, #animalIN, #debrisIN{        width: 200px;      }
        </style>
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
            $(document).ready(function(){
             window.nlist=eval(' . json_encode($this->GetNList()) . ');
             window.settings=eval(' . json_encode($this->ld->GetPlSettings('neighborsplugin')) . ');
             window.lotsite=eval(' . json_encode($this->ld->GetPlSettings('neighborspluginLotsite')) . ');
        //==============================================================
                            if ((window.lotsite!==null) && (window.lotsite!==undefined)){
                                for(var key in window.lotsite) {
                                    $("#lotsite").append("&nbsp;"+window.lotsite[key]["uid"]+"&nbsp;"+window.lotsite[key]["worldName"]+"&nbsp;"+window.lotsite[key]["itemName"]+"&nbsp;<br>");
                                }
                            }

                            //==============================================================

                            if ((window.nlist!==null) && (window.nlist!==undefined)){
                                for(var key in window.nlist) {
                                    $("#nsp").append("<option id=\""+window.nlist[key]["uid"]+"\">"+window.nlist[key]["cityname"]+"</option>");
                                }

                                $("#ncount").text($("#nsp").children().length);
                                $("#act_count").text($("#nsp").children().length*5);
                            }
                            //==============================================================
                            if ((window.settings!==null) && (window.settings!==undefined)){
                                $("#municipal").val(window.settings.municipal);
                                $("#municipalIN").val(window.settings.municipalIN);
                                $("#residence").val(window.settings.residence);
                                $("#residenceIN").val(window.settings.residenceIN);
                                $("#business").val(window.settings.business);
                                $("#businessIN").val(window.settings.businessIN);

                                $("#cropsW").val(window.settings.cropsW);
                                $("#cropsWIN").val(window.settings.cropsWIN);
                                $("#cropsH").val(window.settings.cropsH);
                                $("#cropsHIN").val(window.settings.cropsHIN);
                                $("#cropsR").val(window.settings.cropsR);
                                $("#cropsRIN").val(window.settings.cropsRIN);
                                $("#constr").val(window.settings.constr);
                                $("#constrIN").val(window.settings.constrIN);

                                $("#franchise").val(window.settings.franchise);
                                $("#franchiseIN").val(window.settings.franchiseIN);

                                $("#ships").val(window.settings.ships);
                                $("#shipsIN").val(window.settings.shipsIN);


                                $("#treesIN").val(window.settings.treesIN);
                                $("#trees").val(window.settings.trees);
                                $("#Pcycle").val(window.settings.Pcycle);

                                 $(":checkbox").each(function(){
                                           if(window.settings[$(this).attr("id")]==true){
                                                $(this).attr("checked", true);
                                            }
                                            else{
                                                $(this).attr("checked", false);
                                            }
                                        });
                                if ((window.settings.pause!==null) && (window.settings.pause!==undefined)){
                                    if(window.settings["pause"]==1){
                                        $("#pause").attr("checked", true);
                                    }
                                }
                            }
              //==============================================================
                            $("#btn_save").click(function(){
                            var req=new Object();
                            req.nlist=new Array();
                            
                            if($("#all").attr("checked")==true){
                                $("#nsp").children().each(function(){
                                    req.nlist.push($(this).attr("id")+"|"+$(this).val());
                                });
                            }
                            else{
                                 $("#nsp option:selected").each(function(){
                                    req.nlist.push($(this).attr("id")+"|"+$(this).val());
                                });
                            }
                            $("input:text").each(function(){
                                    req[$(this).attr("id")]=$(this).val();
                            });
                            req["municipalIN"]=$("#municipalIN").val();
                            req["residenceIN"]=$("#residenceIN").val();
                            req["businessIN"]=$("#businessIN").val();
                            req["cropsWIN"]=$("#cropsWIN").val();
                            req["cropsHIN"]=$("#cropsHIN").val();
                            req["cropsRIN"]=$("#cropsRIN").val();
                            req["constrIN"]=$("#constrIN").val();
                            req["treesIN"]=$("#treesIN").val();
                            req["franchiseIN"]=$("#franchiseIN").val();
                            $(":checkbox").each(function(){
                                    var par=$(this).attr("id");
                                    req[par]=$(this).attr("checked");
                                 });
                            if($("#pause").attr("checked")==false){
                                req["pause"]=0;
                            }
                            else{
                                req["pause"]=1;
                            }
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
                <div class="zag" height="35">Neighbors (Updated by 12Christiaan) Version '.$PlVersion['version'].' ('.$PlVersion['date'].')<hr></div>
                <div style="margin-left: 20px">
                    <input id="AcceptN" type="checkbox" value="0" size="4">Accept work from neigbors.<br><hr>
                    <table id="maint" border="0">
                       <tr id="up">
                       <td width="150" height="200" valign="top">
                         <input id="all" type="radio"  class="check" name="dwork" checked value="alln">&nbsp;All
                         <input class="check" id="all" type="radio" name="dwork" value="selected">&nbsp;Selected
                         <select size="12" multiple name="nlist" id="nsp">' . $neighbours . ' </select>
                       </td>
                       <td valign="top" id="tdright" align="left"><div id="rcontent" align="left"></div></td>
                       </tr>
                       <tr id="down">
                        <td  colspan="2">
                          <table>You have &nbsp;<span id="ncount"></span>&nbsp; neighbors, you can do &nbsp;<span id="act_count"></span>&nbsp; actions, please set how much actions should be done for every type, you can also set wich item should be used for every action type.
                          <tr BGCOLOR="grey"><td><b>Construction Site</b></td><td> If sellected this will be done first.</td></tr>
                          <tr><td>Help Construction Site</td><td><input id="constr" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="constrIN">'.$this->GetOptionsByClassName("ConstructionSite").'</select></td></tr>

                          <tr BGCOLOR="grey"><td><b>Crops</b></td><td>If sellected this will be done 2nd.</td></tr>
                          <tr><td>Harvest crops </td><td><input id="cropsH" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="cropsHIN">'.$this->GetOptionsByTypeName("plot_contract").'</select>Coins & Goods</td></tr>
                          <tr><td>Water crops  </td><td><input id="cropsW" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="cropsWIN">'.$this->GetOptionsByTypeName("plot_contract").'</select>Coins & Goods</td></tr>
                          <tr><td>Revive crops </td><td><input id="cropsR" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="cropsRIN">'.$this->GetOptionsByTypeName("plot_contract").'</select>Coins & Goods</td></tr>

                          <tr BGCOLOR="grey"><td><b>Visits</b></td><td></td></tr>
                          <tr><td>Residence </td><td><input id="residence" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="residenceIN">'.$this->GetOptionsByTypeName("residence").'</select>Coins & Reputation</td></tr>
                          <tr><td>Community Buildings <br>(Not working Yet)</td><td><input DISABLED id="municipal" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select DISABLED id="municipalIN">'.$this->GetOptionsByTypeName("municipal").'</select>Coins & Reputation</td></tr>

                          <tr BGCOLOR="grey"><td COLSPAN="2"><b>Visits Businesses</b></td></tr>
                          <tr><td>Send bus to Business </td><td><input id="business" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="businessIN">'.$this->GetOptionsByTypeName("business").'</select>Coins & Reputation</td></tr>

                          <tr BGCOLOR="grey"><td COLSPAN="2"><b>Clear wilderness.</b> </td></tr>
                          <tr><td>Chop trees</td><td><input id="trees" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="treesIN">'.$this->GetOptionsByTypeName("wilderness").'</select></td></tr>

                          <tr BGCOLOR="grey"><td COLSPAN="2"><b>Maximum neighbors / cycle.</b></td></tr>
                          <tr><td>Amount of N per cycle</td><td><input id="Pcycle" type="text" value="0" size="4">&nbsp; 0 = all</td></tr>

                          <tr BGCOLOR="grey"><td COLSPAN="2"><b>Build franchise in neighbor city.</b> This will cost you coins.</td></tr>
                          <tr><td>Build franchise </td><td><input id="franchise" type="text" value="0" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;item:&nbsp;<select id="franchiseIN">'.$this->GetOptionsByTypeName("business").'</select></td></tr>

                       </table>
                       <br>
                       <br><input id="pause" type="checkbox" value="0" size="4">Pause! (When this is checked we will not visit neighbors, so uncheck and save. then in the next run we will visit the sellected neighbors)
                     </td>
                    </tr>
                  </table>
                </div>
                <div width="100%" align="center"><br>
                <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                </div>
        </form><hr>';

echo 'Franchise you can place.';
echo '<table border="1">';
echo '<tr><td>Type:</td><td>Franchise name:</td><td>How many you have:</td></tr>';
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
                  if(isset($obj['locations'])) { $howm = count($obj['locations']); }else{ $howm = 0;}
                  echo '<td>'. $howm.'</td>';
                  echo '</tr>';
        }
echo '</table ><br><hr>';


echo '                <div class="zag" >Neighbors that have open space for your busniss<br><hr><br>
                This list is filled after you have visited your neighbors. It will stay for 24 hours in the list, before to be deleted from the list.
                </div>
                <div style="margin-left: 20px">
                    <table id="maint" border="0">
                       <tr id="up">
                       <td width="350" height="200" valign="top"><span id="lotsite"></span></td>
                       </tr>
                    </table>
                </div>
                </div>


    </body>
</html>
        ';
    }
}

?>
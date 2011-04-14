<?php

class CropsPlugin {
    var $ld;
    var $xmlsOb;

    function CropsPlugin() {
            include 'codebase-php/GetSettingsFromXml.php';
            $this->xmlsOb=new xmlsOb();
            //$this->xmlsOb->GenerateFnames();
            $this->ld = new LocalData();
    }

    // ==========================================================================
    function GetForm() {
        $fln = 'tmp_dir\fnames.txt';
        $fl = fopen($fln, 'r');
        $fnames = unserialize(fread($fl, filesize($fln)));
        fclose($fl);
        //print_r($fnames);
        foreach($fnames['crops'] as $crop){
            $options = $options . '<option value="' . $crop['name'] . '">' . $crop['fname'] . '</option>';
        }

        //==========================================================================
    $PlVersion = (array)$this->ld->GetPlVersion('cropsplugin');
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
        <title>Neighbors helper</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        <link href="helpers/plugin.css" rel="stylesheet" type="text/css">
        <script>
        //==============================================================
        window.reload = function(){
                var l=window.location.toString();
                var indx=l.indexOf(\'?\');
                window.location=l.slice(0, indx)+"?action=refresh&tmp="+Math.random();
        }
        //==============================================================
            $(document).ready(function(){
                //==============================================================
                window.settings=eval(' . json_encode($this->ld->GetPlSettings('cropsplugin')) . ');

                //console.debug(window.settings);

                if ((window.settings!==null) && (window.settings!==undefined)){
                                        $(":checkbox").each(function(){
                                           if(window.settings[$(this).attr("id")]==true){
                                                $(this).attr("checked", true);
                                            }
                                            else{
                                                $(this).attr("checked", false);
                                            }
                                        });
                                    var slist=window.settings["seedlist"];
                                        if ((slist!=undefined) && (slist.length>0)){
                                            for(var $i=0;$i<slist.length; $i++){
                                                var curr=slist[$i];
                                                var tmp=curr.split("|");
                                                var cnt=tmp[1];
                                                var name=tmp[0];
                                                if ((cnt!=undefined) && (name!=undefined)){
                                                    $("#seedlist").append(\'<option value=\' + name+ " | "+cnt + \'>\' + name+ " | "+cnt + \'</option>\');
                                                }

                                            }
                                        }
                }
                window.seedlist=new Array();

                                $("#add").click(function(){
                    var tmp=$("#croplist").val()+ " | " + $("#count").val();
                    $("#seedlist").append("<option value=\'"+tmp+"\'>"+tmp+"</option>");
                    return false;
                });
                //==============================================================
                $("#remove").click(function(){
                    $("#seedlist option:selected").remove();
                    return false;
                });
                //==============================================================
                $("#up").click(function(){
                    var val=$("#seedlist option:selected").text();
                    var f1val=$("#seedlist").children();
                    var fval=$(f1val[0]).text();
                    var prev=$("#seedlist option:selected").prev();
                    if (val!=fval){
                        $("#seedlist option:selected").remove();
                        prev.before("<option value=\'"+val+"\' selected>"+val+"</option>");
                    }
                });
                //==============================================================
                $("#down").click(function(){
                    var val=$("#seedlist option:selected").text();
                    var fval=$("#seedlist").children().last().text();
                    var prev=$("#seedlist option:selected").next();
                    if (val!=fval){
                        $("#seedlist option:selected").remove();
                        prev.after("<option value=\'"+val+"\' selected>"+val+"</option>");
                    }
                });
                //==============================================================
                $("#btn_save").click(function(){
                var req=new Object();
                var i=0;
                    $("#seedlist").children().each(function(){
                        window.seedlist.push($(this).text());
                    });

                                        $(":checkbox").each(function(){
                                            var par=$(this).attr("id");
                                            req[par]=$(this).attr("checked");
                                        });
                    req["seedlist"]=window.seedlist;
                    data=$.toJSON(req);
                    var l=window.location.toString();
                    var indx=l.indexOf(\'?\');
                    var nurl=l.slice(0, indx)+"?action=save&tmp="+Math.random();
                    $.post(nurl, data);
                    setTimeout(window.reload, 2000);

                    return false;
                });
            });
        </script>

    </head>
    <body>
        <form >
          <div class="zag" height="35"><h1>Seed and harvest crops (by 12Christiaan) Version '.$PlVersion['version'].' ('.$PlVersion['date'].') </h1><hr></div>
             1) Create seed tasks &nbsp;&nbsp;&nbsp;
                <table style="margin-top: 10px; margin-left: 2px;">
                  <tr>
                   <td valign="top"> Select crop<br>
                      <select id="croplist" name="croplist" style="width: 200px;">' . $options . '</select><br>
                      <br><br>
                      Count: <br>
                      <input type="text" size="20" name="count" id="count" value="0" style="width: 193px;">
                   </td>
                   <td  valign="top">
                    <button id="add" style="width:50px">+</button><br>
                    <button id="remove" style="width:50px">-</button><br>
                    <button id="up" style="width:50px">up</button><br>
                    <button id="down" style="width:50px">down</button><br>
                   </td>
                   <td valign="top">
                        <select name="seedlist" id="seedlist" size=10  style="width:250px"></select>
                   </td>
                  </tr>
               </table>
          <br>
            2) <input id="harvestsrops" type="checkbox"> Harvest crops?<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="NoHarvestIfFull" type="checkbox"> Stop Harvest if total goods is 90% full.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="clearWithered" type="checkbox"> Clear withered crops.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="InstantGrow" type="checkbox"> Use instant grow (Harvest within 5 Sec.)<br>
			   <br>
		    3) <input id="AcceptN" type="checkbox" value="0" size="4">Accept work from neigbors.<br><hr>
            <div width="100%" align="center">
            <br><br><br><br>
            <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
                        </div>

        </form>
    </body>
</html>
        ';
    }

}

?>
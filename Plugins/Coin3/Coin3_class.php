<?php

class Coin3Plugin {
    var $ld;
    
    function Coin3Plugin() {
        $this->ld = new LocalData();
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
           
    function GetForm() {
$Coin3 = (array)$this->ld->GetPlSettings('Coin3');
        echo '
<html>
<head>
<title>Coin3</title>
<meta http-equiv="Content-Type" content="text/html; charset="utf-8" /> 
</head>
<script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
<script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
<link href="helpers/plugin.css" rel="stylesheet" type="text/css">
<script>
    $(document).ready(function(){                                                                                  

                            window.settings=eval(' . json_encode($this->ld->GetPlSettings('Coin3')) . ');

                              if ((window.settings!==null) && (window.settings!==undefined)){
                                $("#RunTime").val(window.settings.RunTime);

                                if ((window.settings.Run!==null) && (window.settings.Run!==undefined)){
                                    if(window.settings["Run"]==1){
                                        $("#Run").attr("checked", true);
                                    }
                                }

                            }

                            //==============================================================



                            $("#btn_save").click(function(){
                            var req=new Object();
                            req.bus=new Array();

                            $("input:text").each(function(){
                                    req[$(this).attr("id")]=$(this).val();
                            });

                            $("#bus option:selected").each(function(){
                                    req.bus.push($(this).attr("id")+""+$(this).val());
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
    <body>
               <H1>Coin3 ver 0.2 by 12christiaan</H1><br><hr>
               Coins 3 works with Businesses. (Collecting & filling)<br>';
              // var_dump($Coin3);
 $SelBus = (array)$Coin3['bus'];
 echo '        <form>
               <input id="Run" type="checkbox" value="0" > Run the coin 3 (Check is run)<br>
               Select the buildings that you like to supply / collect. (Use Ctrl to select multiple)<br>
               <select size="6" multiple name="bus" id="bus">';

        $Business = array();
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));        //className  String  Business
            if($obj['className'] == "Business")
            {
              $Business[$obj['itemName']] = "have";
            }
        }

       foreach($Business as $Bus => $item)
        {

          if(in_array($Bus, $SelBus)){$selected = " SELECTED ";}else{$selected = "";}

          echo '<option value="'. $Bus . '" '.$selected.' >'. $Bus .'</option>';

        }

      echo    ' </select><br><br>
               <input id="RunTime" type="text" value="0" size="4">&nbsp;How long to run coin 3? <br>It will run every cycle till you stop it. recommanded 90 Sec. <br><br>
               <input id="Debug" type="checkbox" value="0" > Enable this to have less logging. <br><br>
        <div width="100%" align="center"><br>
          <button id="btn_save" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">&nbsp; Save settings&nbsp;</button>
        </div>
        </form>
    </body>
</html>
   ';
    }
}
?>



<?php
class GameInfoPlugin {
    var $ld;
        var $rep;
        var $xmlsOb;
    // ==========================================================================
    function GameInfoPlugin()
    {
        $this->ld = new LocalData();
        $this->xmlsOb = new xmlsOb();
        $this->xmlsOb->GenerateFnames();
    }
    // ==========================================================================
    function GetInfoFromDB()
    {
        $this->rep=Array();
        $res=$this->ld->GetSelect("select * from userInfo");
        foreach ($res as $val){
            $this->rep["uid"]=$this->ld->userId;


            if ($val["name"]=="MaxGoods") $this->rep["Total_storage_capacity"]=$val["value"];
            if ($val["name"]=="Goods") $this->rep["Total_Goods"]=$val["value"];
            if ($val["name"]=="gold") $this->rep["coins"]=$val["value"];
            if ($val["name"]=="freeGold") $this->rep["freeGold"]=$val["value"];
            if ($val["name"]=="cash") $this->rep["cash"]=$val["value"];
            if ($val["name"]=="freeCash") $this->rep["freeCash"]=$val["value"];
            if ($val["name"]=="level") $this->rep["level"]=$val["value"];
            if ($val["name"]=="xp") $this->rep["xp"]=$val["value"];
            if ($val["name"]=="energyMax") $this->rep["energyMax"]=$val["value"];
            if ($val["name"]=="energy") $this->rep["energy"]=$val["value"];
            if ($val["name"]=="gold_spent") $this->rep["gold_spent"]=$val["value"];
            if ($val["name"]=="socialLevel") $this->rep["socialLevel"]=$val["value"];
            if ($val["name"]=="socialXp") $this->rep["socialXp"]=$val["value"];
            if ($val["name"]=="homeIslandSize") $this->rep["homeIslandSize"]=$val["value"];
            

        }
        $res=$this->ld->GetSelect("select * from world where name='sizeX'");
        $this->rep["size"]=$res[0]["value"];

        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val){
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));
            $this->rep["classes"][$obj['className']][$obj['itemName']][]=1;
        }
    }
    // ==========================================================================
    function ObjectTable()
    {
        $res='<table width="400" border="1" bordercolor="black" ><tr><td colspan="2" align="center" class="zag">Objects</td></tr>';
        foreach ($this->rep["classes"] as $key=>$value){
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
         echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
        <title>Neighbors helper</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        <style>
            body{
                background-color: rgb(255, 249, 200);
                font-size: 12pt;
            }
            .zag{
                font-size: 15pt;
                font-weight: bold;
            }
            .zag2{
                font-size: 13pt;
                font-weight: bold;
                width: 300px;
            }
                        #maint{
                            //border-style: solid;
                        }
            

        </style>


        <script>
        //======================================================================
            $(document).ready(function(){
  
            });
        //======================================================================
        </script>
    </head>
    <body>
                <div class="zag" height="35">GameInfo<hr></div>
                <div style="margin-left: 20px">
                
                                    <table id="maint" border="1" bordercolor="black" width="400">
                                        <tr>
                                            <th width="150"  align="center">Parametr</th>
                                            <th width="250" align="center">Value</th>
                                        </tr>
                                            ';
         foreach ($this->rep as $key=>$value){
             if($key!="classes"){
                 echo '<tr><td align="center">'.$key.'</td><td align="center">'.$value.'</td></tr>';
             }
         }

                                 echo '</table><br><br>'.$this->ObjectTable().'
         

    </body>
</html>
        ';
    }
}

?>
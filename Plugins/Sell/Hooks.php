<?php


//WORK ON Sell objects from CITY   =============================================
$this->AddHook('other_work', 'HelpSell');

function HelpSell($bot) {
    $data = $bot->ld->GetPlSettings("Sellplugin");
    $stop = "Y";
    if (!isset($data->Sell))  { $data->pause = 1;  $bot->ld->SavePlSettings("Sellplugin", $data); return;}
    if ($data->Sell == 0  ) { $bot->SendMsg('Sell: no action to be done'); return;}
    if (!isset($data->itemid)) {$itemid = 0; }
    if (empty($data->itemid)) {$itemid = 0; }
    if (!isset($data->SellIN)) {$SellItems = "Do not sell all items"; }
    $SellItems = $data->SellIN;
    $itemid    = $data->itemid;
    if($itemid == 0 && $SellItems == "Do not sell all items")
           {
             $bot->SendMsg('Plugin Sell: Nothing to do..');
             return;
           }

    $data->Sell = 0; // reset itemid for next cycle
    $data->itemid = 0;
    $data->SellIN = "Do not sell all items";
    $bot->ld->SavePlSettings("Sellplugin", $data);
    $bot->pm->RefreshMePlugin("Sell");
    // start of the plugin doing work
    $bot->SendMsg('*******************************');
    $bot->SendMsg('*******************************');
    $bot->SendMsg('Sell starting obj: '. $itemid);
    $bot->SendMsg('Sell starting item: '. $SellItems);
    $now = time();

    $bot->SendMsg('*******************************');
    $bot->SendMsg("**** Trying to load city    ***");
    $ReloadN = 50;
    while($ReloadN > 0)
    {
        $bot->ReloadConfig();
        sleep(2);
        $res=$bot->ld->GetSelect("select * from objects");
        if(count($res) > 10)
          {
            $bot->SendMsg("**** Reload worked now      ***");
            $bot->SendMsg("**** " . count($bot->fobjects) . " items found ");
            $ReloadN = 0;
          }
          else { $bot->SendMsg("**** Reload did not work  $ReloadN /50*");  return;}
          $ReloadN--;
    }

        foreach ($res as $val)
          {
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));

            if($obj['id'] == $itemid || $obj['itemName'] == $SellItems)
              {
                $bot->SendMsg('Plugin Sell: Object id found.');
                $bot->SendMsg('Plugin Sell: .' . $obj['id']);
                $bot->SendMsg('Plugin Sell: .' . $obj['itemName']);
                $bot->SendMsg('Plugin Sell: .' . $obj['className']);
                $bot->SellObject($obj);

              }
          }
        if (isset($bot->error_msg)) {  $bot->SendMsg('Exit by Error');   }
    $bot->SendMsg('*******************************');
    $bot->SendMsg('*******************************');
    $bot->SendMsg('*******************************');
        $bot->ReloadConfig();

} // end function

function SellReload()
{

 $bot->ReloadConfig();

}


?>
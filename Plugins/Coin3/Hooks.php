<?php
$this->AddHook('other_work', 'Coin3');
  
function Coin3($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "Coin3"; $Version = "0.2"; $Date = "2011-01-27";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    $bot->ReloadConfig();
    $data = $bot->ld->GetPlSettings("Coin3");
    if (isset($data->Run)) { $Run = $data->Run; }else {$bot->SendMsg('Coin 3 not activated.'); return;}
    if (!isset($data->Run)) { $bot->SendMsg('Coin 3 not activated...'); return;}
    if (!$data->Run) { $bot->SendMsg('Coin 3 not activated...='); return;}
    if (isset($data->RunTime)) { $RunTime = $data->RunTime; }else {$RunTime = 90;}

    if (isset($data->Debug)) {$Debug = false;}else{$Debug = true;}
    if ($data->Debug) {$Debug = false;}else{$Debug = true;}
    //var_dump($data->bus);
    if(count($data->bus) == 0){ $bot->SendMsg('Coin 3 no Business selected.'); return;}
    $now = time();
    $stop = $now + $RunTime;
    $bot->CheckDoober = TRUE;
    $bot->SendMsg('Coin 3: Started.');
    $bot->SendMsg('Coin 3: Running for: ' .$RunTime. ' Sec');
    $bot->dooberItem = array();
    $bot->dooberItems = 0;
    //==========================================================================

    // prepare all Business buildings
            $bot->SendMsg('Coin 3: Prepare Business buildings.');
            foreach ($bot->fobjects as $obj)
            {
                if (($obj["className"] == "Business") && ($obj["state"] == "open") && in_array($obj["itemName"], $data->bus)) {
                  $bot->processVisits($obj ,$Debug);
                }
            }
            $bot->ReloadConfig();

    While ($now < $stop)
    {
            $reload = 0;
            $bot->SendMsg('Making business buildings ready (before supply)');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Business") && ($obj["state"] == "open") && in_array($obj["itemName"], $data->bus)) {
                  $bot->processVisits($obj, $Debug);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();   $stop = $now; break; }
            }
          $bot->ReloadConfig();
          //if($reload > 0) $bot->ReloadConfig();
          //
            $reload = 0;
            $bot->SendMsg('Collecting business buildings starting');
            $i =0;
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Business") && ($obj["state"] == "closedHarvestable") && in_array($obj["itemName"], $data->bus)) {
                    $bot->collectBB($obj, $Debug);
                    if($bot->dooberItems > 8)
                      { $bot->dooberItems = 0; //$bot->dooberItems -32;
                        $bot->streakBonus2();
                      }
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  $stop = $now; break; }
            }
          $bot->ReloadConfig();
          //if($reload > 0) $bot->ReloadConfig();
          //
            $bot->SendMsg('Supply business buildings starting');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Business") && ($obj["state"] == "closed") && in_array($obj["itemName"], $data->bus)) {
                    $bot->supplyBB($obj, $Debug);
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig(); $stop = $now;  break;  }
            }
          $bot->ReloadConfig();
          $now = time();
    }
    // ending
    $bot->CheckDoober = FALSE;
    // show stats.
    $bot->SendMsg('Coin 3: -------------------------.');
    $bot->SendMsg('Coin 3: -------------------------.');
    foreach($bot->dooberItem as $item => $amount)
    {
      $bot->SendMsg('Coin 3: '.$amount.'  '.$item.' ');
    }
    $bot->SendMsg('Coin 3: Collected for you.');
    $bot->SendMsg('Coin 3: -------------------------.');
    $bot->SendMsg('Coin 3: -------------------------.');


    $bot->pm->RefreshMePlugin("Coin3");
}
?>
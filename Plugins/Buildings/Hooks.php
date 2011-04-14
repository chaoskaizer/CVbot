<?php

function Rent($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "buildings"; $Version = "0.9"; $Date = "2011-02-08";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    $bot->ReloadConfig();
    $data = $bot->ld->GetPlSettings("buildings");
    //==========================================================================
    $yesterday = time()-86400;
    $NoTrainIfFull = 0;
    $StopRTrain = 0;
    $NoShipIfFull = 0;
    $StopRShip = 0;
    if(($bot->MaxGoods * 0.9) <= $bot->waresMax)
      {
        $bot->SendMsg('Goods storage > 90% Full. ' .$bot->waresMax ."/".$bot->MaxGoods);
        $full = "Y";
      }else{$full = "N";}
    if (isset($data->NoTrainIfFull)) { if ($data->NoTrainIfFull == "1" && $full == "Y"){ $data->receiveTrain = 0; }}
    if (isset($data->NoShipIfFull)) { if ($data->NoShipIfFull == "1" && $full == "Y"){ $data->receiveShip = 0;}}
    //==========================================================================
    if (isset($data->AcceptDayBonus)) {
        if ($data->AcceptDayBonus == "1") {
            $bot->SendMsg('Accept daily bonus is ON');
            if($yesterday > $bot->previousBonusTime)
              {
                 $bot->SendMsg('Accept daily bonus now');
                 $bot->AcceptBonus();
                    if (isset($bot->error_msg)) {
                        $bot->ReloadConfig();
                    }
              }
        }
    }
    //==========================================================================
    if (isset($data->collectrent)) {
        if ($data->collectrent == "1")
			{
			if(count($data->collectRentIN) ==0) $bot->SendMsg(' *** You need to select at least one rent building to collect.');
            $bot->SendMsg('Collecting rent starting');
            foreach ($bot->fobjects as $obj) 
				{
                if (($obj["className"] == "Residence") && ($obj["state"] == "grown") && in_array($obj["itemName"], $data->collectRentIN))
					{
                    $bot->collectRent($obj);
                    if (isset($bot->error_msg)) {
                        $bot->ReloadConfig();
                        break;
                    }
                }
            }
        }
    }
    //==========================================================================
    if (isset($data->supplybbplus)) {
        if ($data->supplybbplus == "1") {
            $reload = 0;
            $bot->SendMsg('Making business buildings ready (before supply)');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Business") && ($obj["state"] == "open")) {
                  $bot->processVisits($obj);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  break; }
            }
          if($reload > 1) $bot->ReloadConfig();
        }
    }
    //==========================================================================
    if (isset($data->collectbb)) {
        if ($data->collectbb == "1")
        {
            $reload = 0;
            $bot->SendMsg('Collecting business buildings starting');
            if(count($data->collectbbIN) ==0) $bot->SendMsg(' *** You did not sellect any business in buildings.');
            foreach ($bot->fobjects as $obj)
            {
                // 2011-02-08 added check if business is in the collect list.
                if (($obj["className"] == "Business") && ($obj["state"] == "closedHarvestable") && in_array($obj["itemName"], $data->collectbbIN))
                {
                    $bot->collectBB($obj);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  break; }
            }
          if($reload > 1) $bot->ReloadConfig();
        }
    }
    //==========================================================================
    if (isset($data->collectLM)) {
        if ($data->collectLM == "1") {
            $reload = 0;
            $bot->SendMsg('Collecting Landmarks starting');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Landmark") && ($obj["state"] == "grown")) {
                    $bot->harvest($obj);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  break; }
            }
          if($reload > 1) $bot->ReloadConfig();
        }
    }
    //==========================================================================
    if (isset($data->collectMu)) {
        if ($data->collectMu == "1") {
            $reload = 0;
            $bot->SendMsg('Collecting Municipal Buildings starting');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Municipal") && ($obj["state"] == "grown")) {
                    $bot->harvest($obj);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  break; }
            }
          if($reload > 1) $bot->ReloadConfig();
        }
    }
    //==========================================================================
    if (isset($data->supplybb)) {
        if ($data->supplybb == "1") {
            $bot->SendMsg('Supply business buildings starting');
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Business") && ($obj["state"] == "closed")) {
                    $bot->supplyBB($obj);
                    if (isset($data->supplybbplus)) {
                       if ($data->supplybbplus == "1") {
                         //$bot->SendMsg('Making business buildings ready (after supply)');
                         $bot->processVisits($obj);
                         }
                    }
                }
                if (isset($bot->error_msg)) {
                    $bot->ReloadConfig();
                    break;
                }
            }
        }
    }
    //==========================================================================
    if (isset($data->ClearWildernes)) {
        if ($data->ClearWildernes == "1") {
            $reload = 0;
            $bot->SendMsg('Clear Wildernes starting');
            foreach ($bot->fobjects as $obj) {
                if ($obj["className"] == "Wilderness") {
                    $bot->clearWilderness($obj);
                    $reload++;
                }
                if (isset($bot->error_msg)) { $bot->ReloadConfig();  break; }
            }
          if($reload > 1) $bot->ReloadConfig();
        }
    }
    //==========================================================================
    if (isset($data->receiveTrain)) {
        if ($data->receiveTrain == "1") {
            $bot->SendMsg('Incoming train is set to automatically');
            $xmlsOb=new xmlsOb();
            foreach ($xmlsOb->gsXML->items->item as $item) {
              if ((string)$item['name'] == $bot->train_mission)
                {  $train_arrive = $bot->train_arrive +$item->trainTripTime;
                   $train_order = $bot->train_mission;
                }
               }
          if($train_arrive < time() && !empty($bot->train_mission))
              {    $bot->SendMsg('Train is there, (' . $train_order .')');
                   $bot->receiveTrain();
                   if (isset($bot->error_msg)) { $bot->SendMsg('Did you put a train mission imposible?'); }
                   $bot->ReloadConfig();  // reload config to prepare for sending train.
              }
            if($train_arrive > time())
              { $bot->SendMsg('Train is not here! Arrival ' . $bot->nicetime($train_arrive) . ', current mission: ' . $train_order);              }
            if(empty($bot->train_mission))
              { $bot->SendMsg('No Train to Receive!'); }
        }
    }
    //==========================================================================
    //==========================================================================
    if (isset($data->sentTrain)) {
        if ($data->sentTrain == "1") {
          // Get how long Train will be Away!
          $xmlsOb=new xmlsOb();
          foreach ($xmlsOb->gsXML->items->item as $item) {
            if ((string)$item['name'] == $bot->train_mission)
            { $train_arrive = $bot->train_arrive +$item->trainTripTime;
              $train_order = $bot->train_mission;
            }
           }
          if($train_arrive < time())
            { // train should be there
              if(empty($bot->train_mission))
                { // train was not on a mission ==> we can send the train now
                  $tmp = explode("|", $data->trainMissionIN);
                  $mission = $tmp[0];
                  $goods = $tmp[1];
                  $bot->SendMsg('Send train on new mission. ('.$mission.' getting '.$goods.' goods)'  );
                  $bot->sendTrain($mission, $goods);
                  if (isset($bot->error_msg))
                    { $bot->SendMsg('Did you put a train mission imposible?');
                      $bot->ReloadConfig();
                    }
                }
            }
            else
            { // the train is not here yet
              if ($data->receiveTrain != "1")
                { $bot->SendMsg('Incoming train is not set to automatically, do this manual first.');
                  $bot->SendMsg('Train is not here! Arrival ' . $bot->nicetime($train_arrive) . ', current mission: ' . $train_order);
                }
            }
        }
        else
        { // recieve is not switch to automaticly
          $bot->SendMsg("Receive train manually!");
        }
    }
    //==========================================================================
    if (isset($data->receiveShip) ) {
    $bot->SendMsg('Ship incoming starting');
    if ($data->receiveShip == "1" )
       {
        foreach ($bot->fobjects as $obj) {
            if (($obj["className"] == "Ship") && ($obj["state"] == "grown")) {
                $bot->SendMsg('Ship incomming: ' . $obj['contractName'] );
                $bot->harvest($obj);
                if (isset($bot->error_msg)) {
                    $bot->ReloadConfig();
                    break;
                }
            }
        }
      }
    }
    //==========================================================================
    if (isset($data->sentShip) && isset($data->shipMissionIN)) {
        if ($data->sentShip == "1") {

            $bot->SendMsg('Send ship starting, mission: ' . $data->shipMissionIN);
            foreach ($bot->fobjects as $obj) {
                if (($obj["className"] == "Ship") && ($obj["state"] == "plowed"))
                  {
                    $bot->sendShip($obj, $data->shipMissionIN);
                  }
                if (isset($bot->error_msg)) {
                    $bot->ReloadConfig();
                    break;
                }
            }
        }
    }
    //==========================================================================
}

$this->AddHook('buildings_work', 'Rent');
?>
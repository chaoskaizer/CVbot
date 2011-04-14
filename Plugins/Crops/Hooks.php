<?php

function Harvest($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "cropsplugin"; $Version = "0.4"; $Date = "2011-01-08";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    $bot->SendMsg('Harvest starting');
    $bot->ReloadConfig();
    $reload = 0;
    $data = $bot->ld->GetPlSettings("cropsplugin");
    if (!isset($data)) return;
    $NoHarvestIfFull = 0;
    if (isset($data->NoHarvestIfFull)) { if ($data->NoHarvestIfFull == "1") {   $NoHarvestIfFull = 1; } }

    $StopHarvest = 0;
    if(($bot->MaxGoods * 0.9) <= $bot->waresMax)
      {
        if($NoHarvestIfFull == 1)
         { $StopHarvest = 1;
           $bot->SendMsg('Goods storage > 90% Full. Do not Harvest.' .$bot->waresMax ."/".$bot->MaxGoods);
         }
      }


	//==========================================================================
	
	// accept neighbors work.
    if (isset($data->AcceptN))
       {
         if ($data->AcceptN == 1)
         {
           if(is_array($bot->order_visitor_help))
             {
               $bot->SendMsg('Start accepting work from Neighbors.');
               foreach ($bot->order_visitor_help as $Nuid => $val)
                {
                  $Nuid = $val['senderID']; //senderID
                  //$bot->SendMsg('Accepting work from :' . $Nuid);

                  if(is_array($val['helpTargets']) && $val['status'] == "unclaimed" && count($val['helpTargets']) > 0)
                  {                                                   //status  String
                    $bot->redeemVisitorHelpAction($val['helpTargets'], $Nuid) ;
                    // all streakBonus moved to botclass, please look there
                  }
               }
			}
		 }	 
	  }

    //==========================================================================
    if ($data->clearWithered == "1" ) {
        foreach ($bot->fobjects as $obj) {
            if (($obj["className"] == "Plot") && ($obj["state"] == "withered") )
            {
                                 //$bot->SendMsg('Withered.' .$obj["id"]);
                $obj["state"] = "plowed";
                $bot->clearWithered($obj);
                $reload = 1;
                if (isset($bot->error_msg)) { $bot->ReloadConfig(); break; }
            }
        }
    }
    if($reload ==1){ $bot->ReloadConfig(); }


    //==========================================================================
    if ($data->harvestsrops == "1" && $StopHarvest == 0) {
        foreach ($bot->fobjects as $obj) {
            if (($obj["className"] == "Plot") && ($obj["state"] != "withered") && ($obj["state"] != "plowed"))
            {
             if ($data->InstantGrow == "1" || ($obj["state"] == "grown") )
               {
                            //$bot->SendMsg('harvest.' .$obj["id"]);
                $obj["state"] = "grown";
                $bot->harvest($obj);
                $reload = 1;
                if (isset($bot->error_msg)) { $bot->ReloadConfig(); break; }
               }
            }
        }
    }
    if($reload ==1){ $bot->ReloadConfig(); }
    //==========================================================================
}

$this->AddHook('harvest_crops', 'Harvest');

function Seed($bot) {
    if ($bot->firstrun)
        return; //not work if it's first bot cycle
 $bot->SendMsg('Seed starting');
    $bot->ReloadConfig();
    $good = true;

    $data = $bot->ld->GetPlSettings("cropsplugin");
    if (isset($data->seedlist)) {

        //======================================================================
        if (isset($data->seedlist[0])) {
            $tmp = explode(' | ', $data->seedlist[0]);
            $cnt = $tmp[1];
            $item = $tmp[0];
        } else {
            $good = false;
            $bot->SendMsg('There are no one seed task');
        }
        //======================================================================
        if ($good) {
            foreach ($bot->fobjects as $obj) {
                if ($cnt <= 0)
                    break;
                if (($obj["className"] == "Plot") && ($obj["state"] == "plowed")) {
                    $bot->startContract($obj, $item);
                    if (isset($bot->error_msg)) {
                        $bot->ReloadConfig();
                        break;
                    } else {
                        $cnt = $cnt - 1;
                    }
                }
            }
            if ($cnt == 0) {
                unset($data->seedlist[0]);
                $data->seedlist = array_values($data->seedlist);
                $bot->ld->ExecQuery("update plugin_settings set value='" . serialize($data) . "' where plname='cropsplugin'");
            } else {
                $data->seedlist[0] = $item . " | " . $cnt;
                $bot->ld->ExecQuery("update plugin_settings set value='" . serialize($data) . "' where plname='cropsplugin'");
            }
            $bot->pm->RefreshMePlugin("Crops");
        }
    } else {
        $bot->SendMsg('There are no one seed task');
    }
}

$this->AddHook('seed_crops', 'Seed');
?>
<?php


//WORK ON NEIGHBORS CITY   =====================================================
$this->AddHook('other_work', 'HelpNeighbors');

function HelpNeighbors($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "neighborsplugin"; $Version = "0.7"; $Date = "2011-02-06";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    $data = $bot->ld->GetPlSettings("neighborsplugin");
    if (!isset($data->pause))
       {
         $data->pause = 1;
         $bot->ld->SavePlSettings("neighborsplugin", $data);
       }
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


    // after this visit neighbors.
    if (isset($data->pause)) { if ($data->pause == 1) return; }
    $data->pause = 0;
    $bot->ld->SavePlSettings("neighborsplugin", $data);

    if (!isset($data->nlist))     return;
    if (!isset($data->Pcycle)) $data->Pcycle = 1000;
    if ($data->Pcycle == 0) $data->Pcycle = 1000;
    if (count($data->nlist) == 0) return;
    if (isset($data->update))     return;
    $now = time();

    $Lotsites = $bot->ld->GetPlSettings("neighborspluginLotsite");
    if(!is_array($Lotsites))
      { // if not exist
        $Lotsites = array();
      }
      else
      { // clean older lotsite
        foreach($Lotsites as $key =>$Lotsite )
          { $yesterday = $now - 86400;
            if($Lotsite['time'] < $yesterday) { unset($Lotsites[$key]); }
          }
      }

    $bot->SendMsg('Neighbors help starting');

    // declare the amounts
    $N = array();

    $item = "constr";   //Construction Site
    $N[$item]['className'] = "ConstructionSite";                    
    $N[$item]['name']     = (string) $data->constrIN;   
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['cnt_set']  = (int) $data->constr;        

    $item = "cropsH";       // Crops Harvest
    $N[$item]['className'] = "Plot";                    
    $N[$item]['state'] = "grown";                   
    $N[$item]['name']     = (string) $data->cropsHIN;   
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['cnt_set']  = (int) $data->cropsH;        

    $item = "cropsW";       // Crops Water
    $N[$item]['className'] = "Plot";                    
    $N[$item]['state'] = "planted";                 
    $N[$item]['name']     = (string) $data->cropsWIN;   
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['cnt_set']  = (int) $data->cropsW;        

    $item = "cropsR";       // Crops Revive
    $N[$item]['className'] = "Plot";                    
    $N[$item]['state'] = "withered";                    
    $N[$item]['name']     = (string) $data->cropsRIN;   
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['cnt_set']  = (int) $data->cropsR;        

    $item = "municipal";
    $N[$item]['className'] = "";                    
    $N[$item]['name']     = (string) $data->municipalIN;        //    $municipal_name = (string) $data->municipalIN;
    $N[$item]['cnt_done'] = 0;                      //    $municipal_h_cnt = 0;
    $N[$item]['cnt_set']  = (int) $data->municipal;         //    $municipal_h_cnt_need = (int) $data->municipal;

    $item = "residence";
    $N[$item]['className'] = "Residence";                   
    $N[$item]['name']     = (string) $data->residenceIN;        //   Name of the items to do or ANY
    $N[$item]['cnt_done'] = 0;                      //   How many did we do
    $N[$item]['cnt_set']  = (int) $data->residence;         //   How many can we do (from setting)

    $item = "business";
    $N[$item]['className'] = "Business";                    
    $N[$item]['name']     = (string) $data->businessIN; 
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['cnt_set']  = (int) $data->business;      

    $item = "trees";        // chop trees
    $N[$item]['className'] = "";                    //wilderness
    $N[$item]['name']     = (string) $data->treesIN;    
    $N[$item]['cnt_done'] = 0;                  
    $N[$item]['className'] = "";                    
    $N[$item]['cnt_set']  = (int) $data->trees;     

    $visited = 0;
    $waiting = 0;
    // Now entering the Neighbor loop... Do this for each neighbor sellected or ALL
    foreach ($data->nlist as $index => $sosed)
    {
        // check for maximum visitation per cycle.
        $visited++;
        if($visited > $data->Pcycle ) { $waiting++ ; continue;}

        if (isset($bot->error_msg)) { $bot->ReloadConfig(); } // we got a error
        $tmp = explode("|", $sosed);
        $hostID = trim($tmp[0]);
        $worldName = trim($tmp[1]);
        // info
        $bot->SendMsg('Neighbors: Visit:' . $worldName . ' ('.count($data->nlist) . ' N to go)');
        //update infor for Energy
        //$Ninfo['status'] = "own"; $Ninfo['id'] = $hostID; $Ninfo['name'] = "own"; $Ninfo['gotn'] = "no"; $Ninfo['lastvisit']      = time();
        //$bot->ld->UpdateNinfo($Ninfo);

        // let's load the Neighbor information (world)
        $amf = new AMFObject($bot->NLoadWorld($hostID, $worldName));
        $deserializer = new AMFDeserializer($amf->rawData);
        $deserializer->deserialize($amf);
        $bod = new MessageBody();
        $bod = $amf->_bodys[0];

        $StillCont = 1;       // this to break the loop
        $N['act_done'] = 0;   // Actions done for this Neighbor       $act_per_neighbor_cnt = 0;

        if (isset($bot->error_msg)) {  $bot->ReloadConfig();  $bot->SendMsg('Exit by Error'); $StillCont = 0;  }
        // added 2010-12-28 to support energy plugin.
        //$NNs = $bod->_value['data'][2]['data']['hostUserObj']['player']['neighbors'];
        //if(is_array($NNs))
        //  {foreach ($NNs as $NN)
        //    { $Ninfo['status'] = "NN"; $Ninfo['id'] = $NN; $Ninfo['name'] = "NN"; $Ninfo['gotn'] = "no"; $Ninfo['lastvisit'] = 0;
        //       $bot->ld->UpdateNinfo($Ninfo);
        //    }
        //  }
        // contineu doing work.
      if ($StillCont == 1)
        { // check the ammount of energy left for this N.
          $Nenergy = $bod->_value['data'][0]['data']['energyLeft'];  //amount of action to be done
          $Nresult = $bod->_value['data'][0]['data']['result'];
          if (isset($bod->_value['data'][0]['data']['reward']['rewards']['xp']))    {$Rxp = $bod->_value['data'][0]['data']['reward']['rewards']['xp'];         }else{$Rxp =0;}
          if (isset($bod->_value['data'][0]['data']['reward']['rewards']['energy'])){$Renergy = $bod->_value['data'][0]['data']['reward']['rewards']['energy']; }else{$Renergy =0;}
          if (isset($bod->_value['data'][0]['data']['reward']['rewards']['coins'])) {$Rcoin = $bod->_value['data'][0]['data']['reward']['rewards']['coins'];    }else{$Rcoin =0;}
          if($Rxp == 0)
            { $bot->SendMsg('You already have visited this neighbor. No rewards');}
              else
            { $bot->SendMsg('Actions left for this N: ' . $Nenergy . ' Rewarded: XP: ' . $Rxp . ' , Energy: ' . $Renergy .' Coins : ' . $Rcoin);}
        }

       // Now detect if we already have franchise in this town.
       $franchisHere = array(); // empty the array.
       if(is_array($bod->_value['data'][1]['data']['world']['objects']))
         {
          foreach ($bod->_value['data'][1]['data']['world']['objects'] as $obj)
            { if ($obj['className'] == "Business" )
              {
                if ($obj['itemOwner'] == (string)$bot->zyUid )
                {
                  $bot->SendMsg('N: You have franchis here. ' .$obj['itemName'] );   //itemName  String  bus_shoestore
                  $franchisHere[$obj['itemName']] = 'Y';
                }
              }

            } // end foreach
         }

       // prep setting to build franchise
       if (!isset($data->franchise))  $data->franchise = 0;
       if (!isset($data->franchiseIN))  $data->franchiseIN = "";
       if((int) $data->franchise > 0 && $data->franchiseIN !="")
          {
            $buildFranchise = "Y";
            $bot->SendMsg('N: we are allowed to build franchis here. ' );
            if(array_key_exists($data->franchiseIN, $franchisHere ))
              {
                $bot->SendMsg('N: You already have this franchis here. ' . $data->franchiseIN );
                $buildFranchise = "N";
              }
          }
          else
          {
            $buildFranchise = "N";
            //$bot->SendMsg('N: we are NOT allowed to build franchis here. ' );
          }

       // Now detect lotsite.
       if(is_array($bod->_value['data'][1]['data']['world']['objects']))
         {
          foreach ($bod->_value['data'][1]['data']['world']['objects'] as $obj)
            {
              // store lotesite in db, so we can show it later.
              if ($obj['className'] == "LotSite" )
              { $Lotsite['time']     = $now;
                $Lotsite['itemName'] = $obj['itemName'];
                $Lotsite['uid']      = $hostID;
                $Lotsite['worldName']= $worldName;
                $Lotsites[] = $Lotsite;
              }
              // Now check if we can build a franchise here.
              if($obj['className'] == "LotSite" && $buildFranchise == "Y")
               {
                 $bot->SendMsg('N: found empty LotSite. Building franchis here NOW. ' );
                 $object = array();                           // example
                 $object['lotId']             = $obj['id']  ; //    118
                 $object['recipientID']       = $hostID  ; //    20022222222
                 $object['orderResourceName'] = $worldName . substr($data->franchiseIN, 4)  ; //  Jansen's Cinema
                 $object['resourceType']      = $data->franchiseIN  ; //  resourceType  String  bus_movietheater
                 $bot->placeOrder($object);
                 $data->franchise--;
               }


            } // end foreach
         }

// first look for ConstructionSite
$items = array("constr", "cropsH", "cropsW", "cropsR", "residence", "business", "trees");    //"municipal"
foreach( $items as $item)
  {
  if ($StillCont == 1 && ($N['act_done'] < $Nenergy) && ($N[$item]['cnt_set'] > 0)) 
    { foreach ($bod->_value['data'][1]['data']['world']['objects'] as $obj)
       {
          $obj['VisitorId'] = $hostID;
          if (($obj['className'] == $N[$item]['className']) && ($N[$item]['cnt_done'] < $N[$item]['cnt_set']) && (strlen($N[$item]['name']) > 0) && ($N['act_done'] < $Nenergy))
           {
             if($N[$item]['className'] == "Plot") { if($obj['state'] == $N[$item]['state']) {$stateStatus = "OK";}else{$stateStatus = "NOK";}}else{$stateStatus = "OK";}
             if ($N[$item]['name'] == "any" && $stateStatus == "OK")
               { $bot->BlessNeighbor($obj, $hostID);
                    // all streakBonus moved to botclass, please look there
                    // $bot->streakBonus3();
                 if (!isset($bot->error_msg)) {    $N[$item]['cnt_done']++;    $N['act_done']++; }
               } else
               { if ($N[$item]['name'] == $obj['itemName'] && $stateStatus == "OK")
                  { $bot->BlessNeighbor($obj, $hostID);
                    // all streakBonus moved to botclass, please look there
                    //$bot->streakBonus3();
                    if (!isset($bot->error_msg)) {   $N[$item]['cnt_done']++;    $N['act_done']++; }
                  }
               }
           }
           if (isset($bot->error_msg)) { $bot->ReloadConfig();  $bot->SendMsg('Exit by Error'); break; }
           if ($N['act_done'] >= $Nenergy) { $bot->SendMsg('Max action done for neighbor, exit');  $StillCont = 0; break; }
       } // end foreach
    } // end if cont
  }// end for each items

  // save lotsite
  $bot->ld->SavePlSettings("neighborspluginLotsite", $Lotsites);

  // this Neighbors is done reset from list.
    unset($data->nlist[$index]);
    $bot->ld->SavePlSettings("neighborsplugin", $data);


} // end foreach Neighbor
    // all Neighbors done let's put back to pause.
    if($waiting == 0) $data->pause = 1;
    $bot->ld->SavePlSettings("neighborsplugin", $data);

   // all done, refresh the plugin
   $bot->pm->RefreshMePlugin("Neighbors");

} // end function

//CREATE OR UPDATE NEIGHBORS LIST=========================================================================
$this->AddHook('before_other_work', 'UpdateNList');

function UpdateNList($bot) {
    $data = $bot->ld->GetPlSettings("neighborsplugin");
    if (!isset($data->update))
        return;
    if ((int) $data->update <> 1)
        return;

    //exit;

    $bot->SendMsg('Creating neighbors list');

    $res = ($bot->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="neighborslist"'));
    if ($res[0]["cnt"] > 0) {
        $bot->ld->ExecQuery("drop table if exists 'neighborslist'");
    }
    $bot->ld->ExecQuery("CREATE TABLE if not exists [neighborslist]  ([name] NVARCHAR(100)  NULL,[value] VARCHAR(1000)  NULL)  ");


    foreach ($bot->cfg->_value['data'][0]['data']['userInfo']['player']['neighbors'] as $neighbor) {
        if (isset($bot->error_msg)) 
           {
            $bot->ReloadConfig();
           }
        $tmp['uid'] = $neighbor;
        $tmp['worldName'] = $neighbor;
        $bot->ld->ExecQuery("insert into neighborslist values ('" . $neighbor . "', '" . serialize($tmp) . "')");
    }
//
//    foreach ($bot->neighbors as $neighbor) {
//        if (isset($bot->error_msg)) {
//            $bot->ReloadConfig();
//        }
  //      $amf = new AMFObject(PostInit());

  //      $deserializer = new AMFDeserializer($amf->rawData);
  //      $deserializer->deserialize($amf);
  //      $bod = new MessageBody();
  //      $bod = $amf->_bodys[0];
//        foreach($bod->_value['data'][0]['data']['inGame'] as $key=>$val){
//            $inGame[]="1:".$val;
//        }

  //      foreach($bod->_value['data'][0]['data']['neighbors'] as $fr){
//            if(in_array($fr['uid'], $inGame)){
  //          unset($tmp);
  //          $tmp['uid'] = $fr['uid'];
  //          $tmp['worldName'] = $fr['cityname'];
  //          $tmp['pic'] = "";
  //          $tmp['level'] = $fr['level'];
//            $tmp['cash'] = $bod->_value['data'][0]['data']['user']['player']['cash'];
  //          $tmp['gold'] = $fr['gold'];
  //          $tmp['xp'] = $fr['xp'];
//            $tmp['ncount'] = count($bod->_value['data'][0]['data']['user']['player']['neighbors']);
//            $tmp['ocount'] = count($bod->_value['data'][0]['data']['user']['world']['objects']);
//            $tmp['size'] = $bod->_value['data'][0]['data']['user']['world']['sizeX'];
//            $tmp['lastplayed'] = $bod->_value['data'][0]['data']['user']['player']['lastEnergyCheck'];

  //          $bot->ld->ExecQuery("insert into neighborslist values ('" . $tmp['uid'] . "', '" . serialize($tmp) . "')");
//            }
  //      }

    //}


    $bot->pm->RefreshMePlugin("Neighbors");

    $data->update = 0;
    $bot->ld->SavePlSettings("neighborsplugin", $data);
}

?>
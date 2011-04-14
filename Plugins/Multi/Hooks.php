<?php


//WORK ON Multi CITY   =====================================================
$this->AddHook('other_work', 'HelpMulti');

function HelpMulti($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "Multi"; $Version = "0.7"; $Date = "2011-02-08";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    // get the settings
    $Sett = (array) $bot->ld->GetPlSettings("MultiEnergy");
    $SettFranchise = (array) $bot->ld->GetPlSettings("Franchise");


    $now = time();
    $yesterday = $now - 86400;
    $bot->ReloadConfig();


    // Franchise Collect Daily Bonus
    if (!isset($SettFranchise['AcceptDayBonusHQ'])){ $SettFranchise['AcceptDayBonusHQ'] = false; $bot->ld->SavePlSettings("Franchise", $SettFranchise);}
    if ($SettFranchise['AcceptDayBonusHQ'])
    {
      $bot->SendMsg('Franchise Collect Daily Bonus');
        foreach ($bot->franchises as $obj)
        {
           $collected = (int)$obj['time_last_collected'];
            if($collected == 0) {continue;} // do not have this HQ
            if($collected < $yesterday )
              {
                $bot->SendMsg('Franchise Collect from: ' . $obj['franchise_name']);//franchise_name  String  Kara's Bike Shop
                $bot->onCollectDailyBonus($obj);
              }
              else
              {
                $collect = $obj['time_last_collected']+86400;
                $bot->SendMsg('Franchise: ' . $obj['franchise_name'] . ' collect ' . $bot->ld->nicetime($collect) );//franchise_name  String  Kara's Bike Shop
              }
        }
    }

    // Collections trade-in
    $SettCollection = (array) $bot->ld->GetPlSettings("MultiCollection");
    if (is_array($SettCollection) )
    {
     if ($SettCollection['TradeCollection'] == 1)
      {
       $bot->SendMsg('Multi trade-in collections starting.');

       $res = ($bot->ld->GetSelect("SELECT count(*) as total FROM collection WHERE completed >0"));
       $kk = $res[0]['total'];
       if ($kk > 0){
        $bot->SendMsg('Multi trade-in collections Actions');
        $res = ($bot->ld->GetSelect("SELECT * FROM collection WHERE completed >0"));
        for ($i=0; $i<$kk; $i++)
        {
            $amount = $res[$i]["completed"];
            $collectionType = $res[$i]["collectionType"];
            if($amount > $SettCollection['KeepCollection'])
            {
             $cnt = $amount - $SettCollection['KeepCollection'] ;
             for($j=1; $j<$cnt+1; $j++)
             {
                $bot->TradeCollection($collectionType,$j,$amount);
             }
            }
            else
            {
               $bot->SendMsg('Have collection: '.$amount .' ' . $collectionType . ' Keep: '.$SettCollection['KeepCollection'] );
            }
        }
       }



      }
      else
      {
       $bot->SendMsg('Multi trade-in collections Not enabled.');
      }


    }

    // Wish List
    $SettWishlist = (array) $bot->ld->GetPlSettings("wishlist");
    $bot->SendMsg('Updating Wish List');
    if (is_array($SettWishlist) && count($bot->wishlist) < 5)
    {
     $bot->SendMsg('Wish List: currently '.count($bot->wishlist).' items on the wish list.');
     foreach($SettWishlist as $WLItem1 => $WLset)
      {
         $WLItem = substr($WLItem1, 0, -4);
         if($WLset == 1 && !array_keys($bot->wishlist, $WLItem))
           {
              $bot->SendMsg('Wish List: adding '.$WLItem);
              $bot->addToWishlist($WLItem);
           }
      }
    }else{$bot->SendMsg('Wish List: Done');}

    // inventory stuff  Energy
    $bot->SendMsg('Multi help starting');

            if (is_array($bot->Inventory['items'])) {
                foreach ($bot->Inventory['items'] as $item => $n)
                {
                  $ItemName = $item ; //$n['Item'];
                  $ItemNumber = $n;   //['Number'];
                  $ItemNameUse = $ItemName . "Use";
                  $ItemNameKeep = $ItemName . "Keep";
                  // Let's do some checks to see if settings are defined.
                  if($ItemName == "-")               { continue; }
                  if(strpos($ItemName, "nergy") == 0){ continue; } // skipping item = not energy
                  if(!isset($Sett[$ItemNameUse]))    {  $bot->SendMsg("Inventory: $ItemName No action set. Skipping this item.");      continue;  }
                  if(!isset($Sett[$ItemNameKeep]))   {  $bot->SendMsg("Inventory: $ItemName No Keep amount set. Skipping this item.");  continue; }
                  if($Sett[$ItemNameKeep] == "")     {  $bot->SendMsg("Inventory: $ItemName Keep amount is empty. Skipping this item.");  continue; }

                  // if action = false stop the loop
                  if(!$Sett[$ItemNameUse])    { $bot->SendMsg("Inventory: $ItemName Action set to NO ");       continue; }
                  // now let's see what to do with this item
                  if($Sett[$ItemNameUse])
                    { // Ok we found a item where we can do action on.
                      $bot->SendMsg("Inventory: $ItemName Actions allowed");
                      if($Sett[$ItemNameKeep] >= $ItemNumber)
                        { // nothing to do.
                          $bot->SendMsg("Inventory: $ItemNumber $ItemName found. No actions (Keep:$Sett[$ItemNameKeep])");
                        }
                        else
                        { // we have more than we need to keep.
                          $bot->SendMsg("Inventory: $ItemNumber $ItemName found. (Keep:$Sett[$ItemNameKeep])");
                          while($Sett[$ItemNameKeep] < $ItemNumber)
                           {
                             $bot->SendMsg("Inventory: Preforming action on $ItemName have: $ItemNumber keep: $Sett[$ItemNameKeep]");
                             $ItemNumber--;
                             $bot->BuyEnergy($ItemName) ;
                           }
                          //$arr = array();
                          //$arr[$ItemName] = $ItemNumber - 1;
                          //$bot->ld->UpdateMulti($arr);
                        }
                }
                }// end for each

            }
// check if we need to change the City name.
    $CityName = (array) $bot->ld->GetPlSettings("MultiCityName");
    if( is_array($CityName))
    {

    if(array_key_exists('CityName',$CityName))
      {
       if($CityName['CityName'] !="" )
        {
           $bot->SendMsg('Changing you city name.');
           $bot->setCityName($CityName['CityName']);
           $CityName['CityName'] ="";
           $bot->ld->SavePlSettings("MultiCityName", $CityName);
        }


      }
      }


// Let's send gifts to others.
$errorCnt = 0;
if ($bot->GetParamByName("iProxyUse") == 1)
{
   $bot->SendMsg('********************************');
   $bot->SendMsg('*Sorry, wait for next release');
   $bot->SendMsg('*Sending gifts does not work with proxy');
   $bot->SendMsg('*You have configured a proxy');
   $bot->SendMsg('********************************');
   $errorCnt = 10;
}

if(!$MultiSendGift['ErrorLog'] ) { $bot->SendMsg("Send gift: Error log disabled. ");  }

$bot->SendMsg('Checking Inventory to send Items to others.');
$MultiSendGift = (array) $bot->ld->GetPlSettings("MultiSendGift");
if(array_key_exists('XMLconfigFile',$MultiSendGift) && $errorCnt == 0)
 { // config exist, let contineu.
    $XMLconfigFile = $MultiSendGift['XMLconfigFile'];
    $XMLconfig = new Zend_Config_Xml($XMLconfigFile);
    $URL = 'http://fb-0.cityville.zynga.com/neighbors.php?action=gift';
    $URL .= '&zySnid=1'; //.   $this->XMLconfig->zySnid;    //zySnid=1&
    $URL .= '&zySnuid=' .   $XMLconfig->zySnuid;       //zySnuid=100000111111111&
    $URL .= '&zyUid='.      $XMLconfig->zyUid;         //zyUid=20222222222&
    $URL .= '&zyAuthHash='. $XMLconfig->zyAuthHash;    //zyAuthHash=f1f1f1f1f1f1f1f1f1f1f1ff1f1&
    $URL .= '&zySig='.      $XMLconfig->zySig;         //zySig=44ff44dd44ee55ee55ee66ff77dd66ee




      // define user agent ID's
      define("UA_EXPLORER",0);
      define("UA_MOZILLA",1);
      define("UA_FIREFOX",2);
      define("UA_OPERA",3);
      // define progress message severity levels
      define('HRP_DEBUG',0);
      define('HRP_INFO',1);
      define('HRP_ERROR',2);
      // HTTPRetriever usage example
      //require_once("class_HTTPRetriever.php");
      $http = new HTTPRetriever();


    $SendGiftKeep = array();
    $SendGiftNeig = array();
    foreach($MultiSendGift as $Item => $Action)
    {
      if(substr($Item,0,5) == 'Item_') { $ItemName = substr($Item, 5); $SendGiftKeep[$ItemName] = $Action; }
      if(substr($Item,0,5) == 'Neig_') { $ItemName = substr($Item, 5); $SendGiftNeig[$ItemName] = $Action; }
    }


$sel = ($bot->ld->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Inventory"'));
$InvFileTot = array();
        if ($sel[0]["cnt"] == 1) {
            $sel = ($bot->ld->GetSelect('SELECT count(*) as cnt FROM Inventory'));
            if ($sel[0]["cnt"] > 0) {
                $sel = ($bot->ld->GetSelect('SELECT * FROM Inventory'));
                foreach ($sel as $n)
                {
                  $ItemName = (string)$n['Item'];
                  if($ItemName == "-"){continue;}
                  $ItemHave = $n['Number'];
                  if(array_key_exists($ItemName, $SendGiftKeep))
                      { $Keep = $SendGiftKeep[$ItemName]; } else {$Keep = 9999;}
                  if(array_key_exists($ItemName, $SendGiftNeig))
                    { $Neig = $SendGiftNeig[$ItemName]; } else {$Neig = "";}
                  $bot->SendMsg("Gift: ".$ItemName." Have:" . $ItemHave . " Keep:" . $Keep );
                  // check if we have to send.
                  while($ItemHave > $Keep && $Neig !="")
                    { // let's send those.
                       $ItemHave--;
                       $URL2 = $URL .'&itemName='.$ItemName.'&uid='.$Neig;


                        $http->headers = array(
                            "Referer"=>"http://www.facebook.com/plugins/serverfbml.php",
                            "User-Agent"=>$XMLconfig->UserAgent,
                            "Connection"=>"close"
                        );

                        // enable error logging
                        if($MultiSendGift['ErrorLog'] )
                          {
                            $bot->SendMsg(" Error log enabled. ");
                            $http->progress_callback = "http_callback";
                          }


                        if (!$http->get($URL2, '', $cookies)) {
                        $bot->SendMsg( "HTTP request error: #{$http->result_code}: {$http->result_text} ");
                        DumpError($http, $URL2, $cookies) ;
                        if($errorCnt > 3) return false;
                        $errorCnt++;
                        }
                        //DumpError($http, $URL2, $cookies);
                                                    //               $ch = curl_init($URL2);
                                                    //               curl_setopt($ch, CURLOPT_HEADER, true);
                                                    //               curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                                                    //               curl_setopt($ch, CURLOPT_REFERER, "http://www.facebook.com/plugins/serverfbml.php");
                                                                   //curl_setopt($ch, CURLOPT_PROXY, "localhost:8888" );
                                                    //               curl_setopt($ch, CURLOPT_USERAGENT, $XMLconfig->UserAgent);
                                                    //               curl_setopt($ch, CURLOPT_COOKIE, $XMLconfig->Cookie);
                                                                   //curl_setopt($ch, CURLOPT_POST, 1);
                                                                   //curl_setopt($ch, CURLOPT_GET, true);
                                                    //               curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                    //               $output = curl_exec($ch);
                                                    //               curl_close($ch);
                        $outputArray = explode("\n", $http->response);
                        $found = 0;
                        foreach( $outputArray as $line) { if (strpos($line, "errorMessage"))
                           {
                              $bot->SendMsg("Gift result: " . trim(strip_tags($line)));
                              //echo date(DATE_RFC822) . ' ' .$line; //$found = 1;}
                              $log .= $line;
                              $found = 1;
                            }
                           }
                        if($found == 0) $bot->SendMsg("Gift result: ".$ItemName." Error" );
                        sleep(1);
                    }


                }
            }
        }

 } // end, config does not exist.


// all done, refresh the plugin
$bot->pm->RefreshMePlugin("Multi");
} // end function


$this->AddHook('fill_goods_before', 'fillGoods_before');
$this->AddHook('fill_goods_after', 'fillGoods_after');
function fillGoods_before($bot) {
  fillGoods($bot, "before");
}
function fillGoods_after($bot) {
  fillGoods($bot, "after");
}
function fillGoods($bot, $what) {
    if ($bot->firstrun) return; //not work if it's first bot cycle
    $data = (array) $bot->ld->GetPlSettings("MultiMaxGoods");
    $save = "N";
    if (!isset($data['cropsIN'])){ $data['cropsIN'] = "default"; $save = "Y";}
    if (!isset($data['MultiMaxGoods'])){ $data['MultiMaxGoods'] = false; $save = "Y";}
    if (!isset($data['MultiMaxGoodsAfter'])){ $data['MultiMaxGoodsAfter'] = false; $save = "Y";}
    if($save == "Y") {$bot->ld->SavePlSettings("Franchise", $data); return;}

    if($what == "before" )
      {
        if (!$data['MultiMaxGoods'])  {    $bot->SendMsg('Fill Goods before: nothing to do.'); return;}
        $max = $data['MultiMaxGoodsFill'] ;
      }
      else
      {
        if (!$data['MultiMaxGoodsAfter'])  {    $bot->SendMsg('Fill Goods after: nothing to do.'); return;}
        $max = $data['MultiMaxGoodsFillAfter'] ;
      }

    $current = $bot->waresMax;
    //$max = $bot->MaxGoods-320;
    if($max > $bot->MaxGoods){$max = $bot->MaxGoods;}
    if($current > $max) {    $bot->SendMsg('Fill Goods: have '. $current . ' goods' ); return;}
    $bot->SendMsg('Fill Goods starting');
    $bot->ReloadConfig();

    if($data['cropsIN'] == "default")
      {   if((int)$bot->level < 4) { $item = "plot_strawberries"; $adds = 15; }
          else { $item = "plot_corn";  $adds = 110; }
      }
      else
      {
        $item =$data['cropsIN'];
      }

    $adds = 110; // default
    if($item == "plot_no_wither" ){ $adds = 15;}
    if($item == "plot_strawberries" ){ $adds = 15;}
    if($item == "plot_carrots" ){ $adds = 70;}
    if($item == "plot_corn" ){ $adds = 110;}
    if($item == "plot_eggplants" ){ $adds = 30;}
    if($item == "plot_watermelon" ){ $adds = 45;}
    if($item == "plot_cranberries" ){ $adds = 80;}
    if($item == "plot_pumpkin" ){ $adds = 90;}
    if($item == "plot_wheat" ){ $adds = 130;}
    if($item == "plot_peas" ){ $adds = 155;}
        
    $work = array();

    $bot->SendMsg('Fill Goods. current goods:'. $bot->waresMax);
    $bot->SendMsg('Fill Goods. Max goods:'. $bot->MaxGoods);
    $cont = 1;


            foreach ($bot->fobjects as $obj) {
                if ($obj["className"] == "Plot")
                   { // plot found
                     $bot->SendMsg('Plot found. :' . $obj['id']);
                     $work = $obj;
                     break;
                   }
                }

            if (($work["state"] != "withered") && ($work["state"] != "plowed"))
              {
                $work["state"] = "grown";
                $bot->harvest($work);
                if (isset($bot->error_msg)) { $cont = 2; $bot->ReloadConfig(); break; }
              }

            while($current < $max && $cont == 1)
              {
                $work["state"] = "plowed";
                $bot->startContract($work, $item);
                if (isset($bot->error_msg)) { $cont = 3; $bot->ReloadConfig();  }
                $work["state"] = "grown";
                $bot->harvest($work);
                $current = $current + $adds;
                if (isset($bot->error_msg)) { $cont = 4; $bot->ReloadConfig();  }

              }

}
$this->AddHook('download_images', 'GetImages');
function GetImages($bot)
{
  // This function is the get the images from the City Vile server
  // So we can use them in the plugins.

    $data = (array) $bot->ld->GetPlSettings("ImageDownload");
    $save = "N";
    if (!isset($data['DownloadTime'])){ $data['DownloadTime'] = ""; $save = "Y";}
    if (!isset($data['GameVersion'])){ $data['GameVersion'] = ""; $save = "Y";}
    if (!isset($data['reset'])){ $data['reset'] = ""; $save = "Y";}

    if ($data['reset'] == "10")
       {  $bot->SendMsg('Images reset activated. ');
          $bot->ld->GetOne("UPDATE Images SET download = 'N' WHERE download = 'Y'");
          $data['GameVersion'] = "";
          $data['reset'] = "1";
          $save = "Y";
       }
    if($save == "Y") {$bot->ld->SavePlSettings("ImageDownload", $data); }


    // check if we need to run the download.
    if ($data['GameVersion'] == $bot->flashRevision)
       {  $bot->SendMsg('Images are up to date. ('. $bot->flashRevision .')');
          return;
       }

    $data['GameVersion'] =$bot->flashRevision;
    $data['DownloadTime'] = time();


  $bot->SendMsg('Checking images started. for version: '. $bot->flashRevision);
        $xmlsOb=new xmlsOb();
        $res= array();
        // load the images in the DB
        $DBimage = $bot->ld->GetSelect("SELECT * FROM Images");
        //First Let's build the DB what Images to download
        // Filter all the ICON from the Items
        $r = 0;   $s = 0;
        foreach ($xmlsOb->gsXML->items->item as $item)
        {  $imageurl = '';
           $name = $item['name'];
           foreach($item->image as $icon)
             { //check if this is a icon.
               if($icon['name'] == "icon") $imageurl = $icon['url'];
             }
           if($bot->ld->in_multiarray($name, $DBimage))
                 { $s++; }// the image is already in DB.
                 else
                 { // Image not in DB, need to add.
                    $bot->ld->GetOne("REPLACE into Images values ('$name', '$imageurl', 'N', '".$data['GameVersion']."', '')");
                    //$bot->ld->GetOne("REPLACE into Images2 values ('$name', '$imageurl', '', 'N', '".$data['GameVersion']."', '')");
                    $r++;
                 }
        }
        $bot->SendMsg("$r new and $s existing items in database ");

        $res = ($bot->ld->GetSelect('SELECT count(*) as cnt FROM Images WHERE download = \'N\' AND url != \'\''));
        if ($res[0]["cnt"] > 0)
         {
                 $bot->SendMsg( count($DBimage). " image(s) need to be downloaded. ");
         }
         else
         {
           $bot->SendMsg( "Images up to date.");
           return;
         }

        $DBimage = $bot->ld->GetSelect("SELECT * FROM Images WHERE download != 'Y' AND url != ''");
        //$bot->SendMsg( "Images: checking: ". count($DBimage));

        // Now build the hash key
        $bot->SendMsg( "Images: checking hash keys");
        foreach ($xmlsOb->gsXML->assetIndex as $images)
        {
         $images2 = explode("\n",$images);
         $i = 0;
         foreach($images2 as $imageline)
          {
            $loc    = strrpos($imageline, ":assets");
            $name = substr($imageline, ($loc+1));
            $extlen = strlen($name) - strrpos($name, ".");
            $ext =  substr($name, (0-$extlen) );
            $hash = substr($imageline, ($loc-32), $loc);
            $imagehashs[$i]['name'] = $name ;
            $imagehashs[$i]['hash'] = $hash;
            $imagehashs[$i]['url'] = "http://assets.cityville.zynga.com/hashed/".$hash."$ext";
            $i++;
          }
         }
        $bot->SendMsg( "Images: $i hash keys generated");
        $bot->SendMsg( "Images: Download proccess started. this can take some time when there are many image to be found.");

        //813a26e69d8833bcfcfc6cb6c2f50d0e:assets/bases/base_redbrick/base_redbrick_12x12.png
        //http://assets.cityville.zynga.com/hashed/195d20b6ffd084b360512bdd15391c9a.png
          $FileExistLocal = 0;
          $FileHashNotFound = 0;
          $Filedownload = 0;
        foreach ($DBimage as $images)
        {
            if(file_exists($images['url']))
              { // file already exist local
                $FileExistLocal++;
                $bot->ld->GetOne("REPLACE into Images values ('".$images['ItemName']."', '".$images['url']."', 'E', '".$images['GameVersion']."', '".$data['DownloadTime']."')");
                //$bot->ld->GetOne("REPLACE into Images2 values ('".$images['ItemName']."', '".$images['url']."', '' , 'Y', '".$images['GameVersion']."', '".$data['DownloadTime']."')");
                continue;
              }
            //now finding the hash for this item.
            $DownloadURL = '';
            foreach($imagehashs as $imagehash)
              {
                if($imagehash['name'] == $images['url'])
                  { //hash found
                    $DownloadURL = $imagehash['url'];
                  }
              }
            if($DownloadURL == '')
              { // no hash found
                $FileHashNotFound++;
              }
              else
              {
                //check for folders names
                $vFolder = substr($images['url'], 0, strrpos($images['url'], "/"));
                if (!is_dir($vFolder)) { @mkdir($vFolder, 0777, true);  }
                // downloading the image
                $bot->SendMsg( "Downloading: ". $images['ItemName']);
                //$vImageData = file_get_contentsProxy($DownloadURL);
                // proxy settings
                if ($bot->GetParamByName("iProxyUse") == 1)
                  {
                    $bot->SendMsg( "Images: Download via proxy ".$DownloadURL);
                    $auth = base64_encode($bot->GetParamByName("sProxyUser").':'.$bot->GetParamByName("sProxyPass"));
                    $aContext = array( 'http' => array( 'proxy' => $bot->GetParamByName("sProxyHost").':'.$bot->GetParamByName("iProxyPort"),
                        'request_fulluri' => true,
                        'header' => "Proxy-Authorization: Basic $auth",),);
                    $cxContext = stream_context_create($aContext);
                    $vImageData = file_get_contents($DownloadURL, False, $cxContext);
                  } else
                  {
                    $bot->SendMsg( "Images: Download ".$DownloadURL);
                    $vImageData =  file_get_contents($DownloadURL);
                  }
                // end proxy

                if($vImageData)
                {
                    file_put_contents($images['url'], $vImageData);
                    $bot->ld->GetOne("REPLACE into Images values ('".$images['ItemName']."', '".$images['url']."',  'Y', '".$images['GameVersion']."', '".$data['DownloadTime']."')");
                    //$bot->ld->GetOne("REPLACE into Images2 values ('".$images['ItemName']."', '".$images['url']."', '".$DownloadURL."', 'Y', '".$images['GameVersion']."', '".$data['DownloadTime']."')");
                    $Filedownload++;
                }

              }
         }
        // save version info.
        $bot->ld->SavePlSettings("ImageDownload", $data);
        $bot->SendMsg( "Images: $FileExistLocal already existed");
        $bot->SendMsg( "Images: $FileHashNotFound hash not found, will try next time");
        $bot->SendMsg( "Images: $Filedownload images downloaded");
}



function file_get_contentsProxy($DownloadURL)
{

        if ($bot->GetParamByName("iProxyUse") == 1)
        {
            $auth = base64_encode($bot->GetParamByName("sProxyUser").':'.$bot->GetParamByName("sProxyPass"));
            $aContext = array( 'http' => array( 'proxy' => $bot->GetParamByName("sProxyHost").':'.$bot->GetParamByName("iProxyPort"),
                'request_fulluri' => true,
                'header' => "Proxy-Authorization: Basic $auth",),);
            $cxContext = stream_context_create($aContext);
            return file_get_contents($DownloadURL, False, $cxContext);
        }
        else
        {
        return file_get_contents($DownloadURL);
        }
}



//
//
        // Optionally set this to a valid callback method to have HTTPRetriever
        // provide progress messages.  Your callback must accept 2 parameters:
        // an integer representing the severity (0=debug, 1=information, 2=error),
        // and a string representing the progress message

function http_callback($level, $string)
{
 // display Error mesages.
 if (strpos("500 Internal Server Error",$string)) {$bot->SendMsg( "Error: 500. Cityville server error. try again later."); }
 if (strpos("socket read timeout",$string))       {$bot->SendMsg( "Error: timeout. Cityville did not respond.  try again later."); }




 // Check if log folder exist.
 $vFolder = "tmp_dir/SendGift/";
 if (!is_dir($vFolder)) { @mkdir($vFolder, 0777, true);  }
 // make file name.
 date_default_timezone_set("Europe/London");
 $date = date("Y-m-d_H-i");
 $filename = $vFolder . $date . "_Http_info.txt";
 // make log msg
 $log = "-----------------------------------------------------------\r\n";
 $log .= $string ;
 $log .= "\r\n";
 // write to file
        $fl = fopen($filename, 'a');
        fwrite($fl, $log);
        fclose($fl);
 // done.
}


function DumpError($http, $URL2, $cookies)
{
 // Check if log folder exist.
 $vFolder = "tmp_dir/SendGift/";
 if (!is_dir($vFolder)) { @mkdir($vFolder, 0777, true);  }
 // make file name.
 date_default_timezone_set("Europe/London");
 $date = date("Y-m-d_H-i-s");
 $filename = $vFolder . $date . "_Http_Error.txt";
 // make the log message.
 $log = "Http log. ";
 $log .= "-----------------------------------------------------------\r\n";
 $log .= "URL:  " .$URL2 . "\r\n";
 $log .= "Cookies send:  " . $cookies. "\r\n";
 $log .= "method: " . $http->method. "\r\n";
 $log .= "post_data: " . $http->post_data. "\r\n";
 $log .= "connect_ip: " . $http->connect_ip. "\r\n";
 $log .= "cookie_headers" . $http->cookie_headers. "\r\n";
 $log .= "-----------------------------------------------------------\r\n";
 $log .= "Stats: \r\n" . serialize($http->stats) . "\r\n";
 foreach($http->stats as $hed => $val)
  {
     $log .= "Stats: " . $hed . " = ".  $val . "\r\n";

  }
 $log .= "-----------------------------------------------------------\r\n";
 $log .= "Result code: " . $http->result_code. " Result text:  " . $http->result_text. "\r\n";
 $log .= "header: " . serialize($http->headers). "\r\n";
 $log .= "Result cookies" . $http->request_cookies. "\r\n";
 $log .= "Response_cookies" . serialize($http->response_cookies). "\r\n";
 $log .= "-----------------------------------------------------------\r\n";
 $log .= " http response: \r\n".$http->response."\r\n";

 // write to file
        $fl = fopen($filename, 'w');
        fwrite($fl, $log);
        fclose($fl);
 // done.


}

//   $http->response_headers











class HTTPRetriever {
    
    // Constructor
    function HTTPRetriever() {
        // default HTTP headers to send with all requests
        $this->headers = array(
            "Referer"=>"",
            "User-Agent"=>"HTTPRetriever/1.0",
            "Connection"=>"close"
        );
        
        // HTTP version (has no effect if using CURL)
        $this->version = "1.1";
        
        // Normally, CURL is only used for HTTPS requests; setting this to
        // TRUE will force CURL for HTTP requests as well.  Not recommended.
        $this->force_curl = false;  
        
        // If you don't want to use CURL at all, set this to TRUE.
        $this->disable_curl = true;
        
        // If HTTPS request return an error message about SSL certificates in
        // $this->error and you don't care about security, set this to TRUE
        $this->insecure_ssl = true;
        
        // Set the maximum time to wait for a connection
        $this->connect_timeout = 10;
        
        // Set the maximum time to allow a transfer to run, or 0 to disable.
        $this->max_time = 10;
        
        // Set the maximum time for a socket read/write operation, or 0 to disable.
        $this->stream_timeout = 10;
        
        // If you're making an HTTPS request to a host whose SSL certificate
        // doesn't match its domain name, AND YOU FULLY UNDERSTAND THE
        // SECURITY IMPLICATIONS OF IGNORING THIS PROBLEM, set this to TRUE.
        $this->ignore_ssl_hostname = true;
        
        // If TRUE, the get() and post() methods will close the connection
        // and return immediately after receiving the HTTP result code
        $this->result_close = false;
        
        // If set to a positive integer value, retrieved pages will be cached
        // for this number of seconds.  Any subsequent calls within the cache
        // period will return the cached page, without contacting the remote
        // server.
        $this->caching = false;
        
        // If TRUE and $this->caching is not false, retrieved pages/files will be
        // cached only if they appear to be static.
        $this->caching_intelligent = false;
        
        // If TRUE, cached files will be stored in subdirectories corresponding
        // to the first 2 letters of the hash filename
        $this->caching_highvolume = false;

        // If $this->caching is enabled, this specifies the folder under which
        // cached pages are saved.
        $this->cache_path = '/tmp/';
        
        // Set these to perform basic HTTP authentication
        $this->auth_username = '';
        $this->auth_password = '';

        // Optionally set this to a valid callback method to have HTTPRetriever
        // provide page preprocessing capabilities to your script.  If set, this
        // method should accept two arguments: an object representing an instance
        // of HTTPRetriever, and a string containing the page contents
        $this->page_preprocessor = null;
        
        // Optionally set this to a valid callback method to have HTTPRetriever
        // provide progress messages.  Your callback must accept 2 parameters:
        // an integer representing the severity (0=debug, 1=information, 2=error),
        // and a string representing the progress message
        $this->progress_callback = null;
        
        // Optionally set this to a valid callback method to have HTTPRetriever
        // provide bytes-transferred messages.  Your callbcak must accept 2
        // parameters: an integer representing the number of bytes transferred,
        // and an integer representing the total number of bytes expected (or
        // -1 if unknown).
        $this->transfer_callback = null;
        
        // Set this to TRUE if you HTTPRetriever to transparently follow HTTP
        // redirects (code 301, 302, 303, and 307).  Optionally set this to a
        // numeric value to limit the maximum number of redirects to the specified
        // value.  (Redirection loops are detected automatically.)
        // Note that non-GET/HEAD requests will NOT be redirected except on code
        // 303, as per HTTP standards.
        $this->follow_redirects = false;
    }
    
    // Send an HTTP GET request to $url; if $ipaddress is specified, the
    // connection will be made to the selected IP instead of resolving the 
    // hostname in $url.
    //
    // If $cookies is set, it should be an array in one of two formats.
    //
    // Either: $cookies[ 'cookiename' ] = array (
    //      '/path/'=>array(
    //          'expires'=>time(),
    //          'domain'=>'yourdomain.com',
    //          'value'=>'cookievalue'
    //      )
    // );
    //
    // Or, a more simplified format:
    //  $cookies[ 'cookiename' ] = 'value';
    //
    // The former format will automatically check to make sure that the path, domain,
    // and expiration values match the HTTP request, and will only send the cookie if
    // they do match.  The latter will force the cookie to be set for the HTTP request
    // unconditionally.
    // 
    function get($url,$ipaddress = false,$cookies = false) {
        $this->method = "GET";
        $this->post_data = "";
        $this->connect_ip = $ipaddress;
        return $this->_execute_request($url,$cookies);
    }
    
    // Send an HTTP POST request to $url containing the POST data $data.  See ::get()
    // for a description of the remaining arguments.
    function post($url,$data="",$ipaddress = false,$cookies = false) {
        $this->method = "POST";
        $this->post_data = $data;
        $this->connect_ip = $ipaddress;
        return $this->_execute_request($url,$cookies);
    }
    
    // Send an HTTP HEAD request to $url.  See ::get() for a description of the arguments.  
    function head($url,$ipaddress = false,$cookies = false) {
        $this->method = "HEAD";
        $this->post_data = "";
        $this->connect_ip = $ipaddress;
        return $this->_execute_request($url,$cookies);
    }
        
    // send an alternate (non-GET/POST) HTTP request to $url
    function custom($method,$url,$data="",$ipaddress = false,$cookies = false) {
        $this->method = $method;
        $this->post_data = $data;
        $this->connect_ip = $ipaddress;
        return $this->_execute_request($url,$cookies);
    }   
    
    function array_to_query($arrayname,$arraycontents) {
        $output = "";
        foreach ($arraycontents as $key=>$value) {
            if (is_array($value)) {
                $output .= $this->array_to_query(sprintf('%s[%s]',$arrayname,urlencode($key)),$value);
            } else {
                $output .= sprintf('%s[%s]=%s&',$arrayname,urlencode($key),urlencode($value));
            }
        }
        return $output;
    }
    
    // builds a query string from the associative array array $data;
    // returns a string that can be passed to $this->post()
    function make_query_string($data) {
        $output = "";
        if (is_array($data)) {
            foreach ($data as $name=>$value) {
                if (is_array($value)) {
                    $output .= $this->array_to_query(urlencode($name),$value);
                } elseif (is_scalar($value)) {
                    $output .= urlencode($name)."=".urlencode($value)."&";
                } else {
                    $output .= urlencode($name)."=".urlencode(serialize($value)).'&';
                }
            }
        }
        return substr($output,0,strlen($output)-1);
    }

    
    // this is pretty limited... but really, if you're going to spoof you UA, you'll probably
    // want to use a Windows OS for the spoof anyway
    //
    // if you want to set the user agent to a custom string, just assign your string to
    // $this->headers["User-Agent"] directly
    function set_user_agent($agenttype,$agentversion,$windowsversion) {
        $useragents = array(
            "Mozilla/4.0 (compatible; MSIE %agent%; Windows NT %os%)", // IE
            "Mozilla/5.0 (Windows; U; Windows NT %os%; en-US; rv:%agent%) Gecko/20040514", // Moz
            "Mozilla/5.0 (Windows; U; Windows NT %os%; en-US; rv:1.7) Gecko/20040803 Firefox/%agent%", // FFox
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT %os%) Opera %agent%  [en]", // Opera
        );
        $agent = $useragents[$agenttype];
        $this->headers["User-Agent"] = str_replace(array("%agent%","%os%"),array($agentversion,$windowsversion),$agent);
    }
    
    // this isn't presently used as it's now handled inline by the request parser
    function remove_chunkiness() {
        $remaining = $this->response;
        $this->response = "";
        
        while ($remaining) {
            $hexlen = strpos($remaining,"\r");
            $chunksize = substr($remaining,0,$hexlen);
            $argstart = strpos($chunksize,';');
            if ($argstart!==false) $chunksize = substr($chunksize,0,$argstart);
            $chunksize = (int) @hexdec($chunksize);

            $this->response .= substr($remaining,$hexlen+2,$chunksize);
            $remaining = substr($remaining,$hexlen+2+$chunksize+2);

            if (!$chunksize) {
                // either we're done, or something's borked... exit
                $this->response .= $remaining;
                return;
            }
        }
    }
    
    // (internal) store a page in the cache
    function _cache_store($token,$url) {

        if ($this->caching_intelligent) {
            $urlinfo = parse_url($url);
            if ($this->method=='POST') {
                $this->progress(HRP_DEBUG,"POST request; not caching");
                return;
            } else if (strlen($urlinfo['query'])) {
                $this->progress(HRP_DEBUG,"Request used query string; not caching");
                return;
            } else {
                $this->progress(HRP_DEBUG,"Request appears to be static and cacheable");
            }
        }

        $values = array(
            "stats"=>$this->stats,
            "result_code"=>$this->result_code,
            "result_text"=>$this->result_text,
            "version"=>$this->version,
            "response"=>$this->response,
            "response_headers"=>$this->response_headers,
            "response_cookies"=>$this->response_cookies,
            "raw_response"=>$this->raw_response,
        );
        $values = serialize($values);

        $cache_dir = $this->cache_path;
        if (substr($cache_dir,-1)!='/') $cache_dir .= '/';
        
        if ($this->caching_highvolume) {
            $cache_dir .= substr($token,0,2) . '/';
            if (!is_dir($cache_dir)) @mkdir($cache_dir);
        }
        
        $filename = $cache_dir.$token.'.tmp';

        $fp = @fopen($filename,"w");
        if (!$fp) {
            $this->progress(HRP_DEBUG,"Unable to create cache file");
            return false;
        }
        fwrite($fp,$values);
        fclose($fp);

        $this->progress(HRP_DEBUG,"HTTP response stored to cache");
    }
    
    // (internal) fetch a page from the cache
    function _cache_fetch($token) {
        $this->cache_hit = false;
        $this->progress(HRP_DEBUG,"Checking for cached page value");

        $cache_dir = $this->cache_path;
        if (substr($cache_dir,-1)!='/') $cache_dir .= '/';
        
        if ($this->caching_highvolume) $cache_dir .= substr($token,0,2) . '/';
        
        $filename = $cache_dir.$token.'.tmp';
        if (!file_exists($filename)) {
            $this->progress(HRP_DEBUG,"Page not available in cache");
            return false;
        }
        
        if (time()-filemtime($filename)>$this->caching) {
            $this->progress(HRP_DEBUG,"Page in cache is expired");
            @unlink($filename);
            return false;
        }
        
        if ($values = file_get_contents($filename)) {
            $values = unserialize($values);
            if (!$values) {
                $this->progress(HRP_DEBUG,"Invalid cache contents");
                return false;
            }
            
            $this->stats = $values["stats"];
            $this->result_code = $values["result_code"];
            $this->result_text = $values["result_text"];
            $this->version = $values["version"];
            $this->response = $values["response"];
            $this->response_headers = $values["response_headers"];
            $this->response_cookies = $values["response_cookies"];
            $this->raw_response = $values["raw_response"];
            
            $this->progress(HRP_DEBUG,"Page loaded from cache");
            $this->cache_hit = true;
            return true;
        } else {
            $this->progress(HRP_DEBUG,"Error reading cache file");
            return false;
        }
    }
    
    function parent_path($path) {
        if (substr($path,0,1)=='/') $path = substr($path,1);
        if (substr($path,-1)=='/') $path = substr($path,0,strlen($path)-1);
        $path = explode('/',$path);
        array_pop($path);
        return count($path) ? ('/' . implode('/',$path)) : '';
    }
    
    // $cookies should be an array in one of two formats.
    //
    // Either: $cookies[ 'cookiename' ] = array (
    //      '/path/'=>array(
    //          'expires'=>time(),
    //          'domain'=>'yourdomain.com',
    //          'value'=>'cookievalue'
    //      )
    // );
    //
    // Or, a more simplified format:
    //  $cookies[ 'cookiename' ] = 'value';
    //
    // The former format will automatically check to make sure that the path, domain,
    // and expiration values match the HTTP request, and will only send the cookie if
    // they do match.  The latter will force the cookie to be set for the HTTP request
    // unconditionally.
    //  
    function response_to_request_cookies($cookies,$urlinfo) {
        // added to test if string.
        if (!is_array($cookies)) {
            $this->request_cookies = $cookies;
            return;
        }
        
        // check for simplified cookie format (name=value)
        $cookiekeys = array_keys($cookies);
        if (!count($cookiekeys)) return;
        
        $testkey = array_pop($cookiekeys);
        if (!is_array($cookies[ $testkey ])) {
            foreach ($cookies as $k=>$v) $this->request_cookies[$k] = $v;
            return;
        }
        
        // must not be simplified format, so parse as complex format:
        foreach ($cookies as $name=>$paths) {
            foreach ($paths as $path=>$values) {
                // make sure the cookie isn't expired
                if ( isset($values['expires']) && ($values['expires']<time()) ) continue;
                
                $cookiehost = $values['domain'];
                $requesthost = $urlinfo['host'];
                // make sure the cookie is valid for this host
                $domain_match = (
                    ($requesthost==$cookiehost) ||
                    (substr($requesthost,-(strlen($cookiehost)+1))=='.'.$cookiehost)
                );              
                
                // make sure the cookie is valid for this path
                $cookiepath = $path; if (substr($cookiepath,-1)!='/') $cookiepath .= '/';
                $requestpath = $urlinfo['path']; if (substr($requestpath,-1)!='/') $requestpath .= '/';
                if (substr($requestpath,0,strlen($cookiepath))!=$cookiepath) continue;
                
                $this->request_cookies[$name] = $values['value'];
            }
        }
    }                   
    
    // Execute the request for a particular URL, and transparently follow
    // HTTP redirects if enabled.  If $cookies is specified, it is assumed
    // to be an array received from $this->response_cookies and will be
    // processed to determine which cookies are valid for this host/URL.
    function _execute_request($url,$cookies = false) {
        // valid codes for which we transparently follow a redirect
        $redirect_codes = array(301,302,303,307);
        // valid methods for which we transparently follow a redirect
        $redirect_methods = array('GET','HEAD');

        $request_result = false;
        
        $this->followed_redirect = false;
        $this->response_cookies = array();
        $this->cookie_headers = '';

        $previous_redirects = array();
        do {
            // send the request
            $request_result = $this->_send_request($url,$cookies);
            $lasturl = $url;
            $url = false;

            // see if a redirect code was received
            if ($this->follow_redirects && in_array($this->result_code,$redirect_codes)) {
                
                // only redirect on a code 303 or if the method was GET/HEAD
                if ( ($this->result_code==303) || in_array($this->method,$redirect_methods) ) {
                    
                    // parse the information from the OLD URL so that we can handle
                    // relative links
                    $oldurlinfo = parse_url($lasturl);
                    
                    $url = $this->response_headers['Location'];
                    
                    // parse the information in the new URL, and fill in any blanks
                    // using values from the old URL
                    $urlinfo = parse_url($url);
                    foreach ($oldurlinfo as $k=>$v) {
                        if (!$urlinfo[$k]) $urlinfo[$k] = $v;
                    }
                    
                    // create an absolute path
                    if (substr($urlinfo['path'],0,1)!='/') {
                        $baseurl = $oldurlinfo['path'];
                        if (substr($baseurl,-1)!='/') $baseurl = $this->parent_path($url) . '/';
                        $urlinfo['path'] = $baseurl . $urlinfo['path'];
                    }
                    
                    // rebuild the URL
                    $url = $this->rebuild_url($urlinfo);

                    $this->method = "GET";
                    $this->post_data = "";
                    
                    $this->progress(HRP_INFO,'Redirected to '.$url);
                }
            }
            
            if ( $url && strlen($url) ) {
                
                if (isset($previous_redirects[$url])) {
                    $this->error = "Infinite redirection loop";
                    $request_result = false;
                    break;
                }
                if ( is_numeric($this->follow_redirects) && (count($previous_redirects)>$this->follow_redirects) ) {
                    $this->error = "Exceeded redirection limit";
                    $request_result = false;
                    break;
                }

                $previous_redirects[$url] = true;
            }

        } while ($url && strlen($url));

        // clear headers that shouldn't persist across multiple requests
        $per_request_headers = array('Host','Content-Length');
        foreach ($per_request_headers as $k=>$v) unset($this->headers[$v]);
        
        if (count($previous_redirects)>1) $this->followed_redirect = array_keys($previous_redirects);
        
        return $request_result;
    }
    
    // private - sends an HTTP request to $url
    function _send_request($url,$cookies = false) {
        $this->progress(HRP_INFO,"Initiating {$this->method} request for $url");
        if ($this->caching) {
            $cachetoken = md5($url.'|'.$this->post_data);
            if ($this->_cache_fetch($cachetoken)) return true;
        }
        
        $time_request_start = $this->getmicrotime();
        
        $urldata = parse_url($url);
        $this->urldata = &$urldata;
        $http_host = $urldata['host'] . (isset($urldata['port']) ? ':'.$urldata['port'] : '');
        
        if (!isset($urldata["port"]) || !$urldata["port"]) $urldata["port"] = ($urldata["scheme"]=="https") ? 443 : 80;
        if (!isset($urldata["path"]) || !$urldata["path"]) $urldata["path"] = '/';
        
        if (!empty($urldata['user'])) $this->auth_username = $urldata['user'];
        if (!empty($urldata['pass'])) $this->auth_password = $urldata['pass'];
        
        //echo "Sending HTTP/{$this->version} {$this->method} request for ".$urldata["host"].":".$urldata["port"]." page ".$urldata["path"]."<br>";
        
        if ($this->version>"1.0") $this->headers["Host"] = $http_host;
        if ($this->method=="POST") {
            $this->headers["Content-Length"] = strlen($this->post_data);
            if (!isset($this->headers["Content-Type"])) $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
        }
        
        if ( !empty($this->auth_username) || !empty($this->auth_password) ) {
            $this->headers['Authorization'] = 'Basic '.base64_encode($this->auth_username.':'.$this->auth_password);
        } else {
            unset($this->headers['Authorization']);
        }
        
        if (is_array($cookies)) {
            $this->response_to_request_cookies($cookies,$urldata);
        }
        
        if (!empty($urldata["query"])) $urldata["path"] .= "?".$urldata["query"];
        $request = $this->method." ".$urldata["path"]." HTTP/".$this->version."\r\n";
        $request .= $this->build_headers();
        $request .= $this->post_data;
        
        $this->response = "";
        
        // clear headers that shouldn't persist across multiple requests
        // (we can do this here as we've already built the request, including headers, above)
        $per_request_headers = array('Host','Content-Length');
        foreach ($per_request_headers as $k=>$v) unset($this->headers[$v]);
        
        // Native SSL support requires the OpenSSL extension, and was introduced in PHP 4.3.0
        $php_ssl_support = extension_loaded("openssl") && version_compare(phpversion(),"4.3.0")>=0;
        
        // if this is a plain HTTP request, or if it's an HTTPS request and OpenSSL support is available,
        // natively perform the HTTP request
        if ( ( ($urldata["scheme"]=="http") || ($php_ssl_support && ($urldata["scheme"]=="https")) ) && (!$this->force_curl) ) {
            $curl_mode = false;

            $hostname = $this->connect_ip ? $this->connect_ip : $urldata['host'];
            if ($urldata["scheme"]=="https") $hostname = 'ssl://'.$hostname;
            
            $time_connect_start = $this->getmicrotime();

            $this->progress(HRP_INFO,'Opening socket connection to '.$hostname.' port '.$urldata['port']);

            $this->expected_bytes = -1;
            $this->received_bytes = 0;
            
            $fp = @fsockopen ($hostname,$urldata["port"],$errno,$errstr,$this->connect_timeout);
            $time_connected = $this->getmicrotime();
            $connect_time = $time_connected - $time_connect_start;
            if ($fp) {
                if ($this->stream_timeout) stream_set_timeout($fp,$this->stream_timeout);
                $this->progress(HRP_INFO,"Connected; sending request");
                
                $this->progress(HRP_DEBUG,$request);
                fputs ($fp, $request);
                $this->raw_request = $request;
                
                if ($this->stream_timeout) {
                    $meta = socket_get_status($fp);
                    if ($meta['timed_out']) {
                        $this->error = "Exceeded socket write timeout of ".$this->stream_timeout." seconds";
                        $this->progress(HRP_ERROR,$this->error);
                        return false;
                    }
                }
                
                $this->progress(HRP_INFO,"Request sent; awaiting reply");
                
                $headers_received = false;
                $data_length = false;
                $chunked = false;
                $iterations = 0;
                while (!feof($fp)) {
                    if ($data_length>0) {
                        $line = fread($fp,$data_length);
                        $this->progress(HRP_DEBUG,"[DL] Got a line: [{$line}] " . gettype($line));

                        if ($line!==false) $data_length -= strlen($line);
                    } else {
                        $line = @fgets($fp,10240);
                        $this->progress(HRP_DEBUG,"[NDL] Got a line: [{$line}] " . gettype($line));
                        
                        if ( ($chunked) && ($line!==false) ) {
                            $line = trim($line);
                            if (!strlen($line)) continue;
                            
                            list($data_length,) = explode(';',$line,2);
                            $data_length = (int) hexdec(trim($data_length));
                            
                            if ($data_length==0) {
                                $this->progress(HRP_DEBUG,"Done");
                                // end of chunked data
                                break;
                            }
                            $this->progress(HRP_DEBUG,"Chunk length $data_length (0x$line)");
                            continue;
                        }
                    }
                    
                    if ($line===false) {
                        $meta = socket_get_status($fp);
                        if ($meta['timed_out']) {
                            if ($this->stream_timeout) {
                                $this->error = "Exceeded socket read timeout of ".$this->stream_timeout." seconds";
                            } else {
                                $this->error = "Exceeded default socket read timeout";
                            }
                            $this->progress(HRP_ERROR,$this->error);
                            return false;
                        } else {
                            $this->progress(HRP_ERROR,'No data but not timed out');
                        }
                        continue;
                    }                   

                    // check time limits if requested
                    if ($this->max_time>0) {
                        if ($this->getmicrotime() - $time_request_start > $this->max_time) {
                            $this->error = "Exceeded maximum transfer time of ".$this->max_time." seconds";
                            $this->progress(HRP_ERROR,$this->error);
                            return false;
                            break;
                        }
                    }

                    $this->response .= $line;
                    
                    $iterations++;
                    if ($headers_received) {
                        if ($time_connected>0) {
                            $time_firstdata = $this->getmicrotime();
                            $process_time = $time_firstdata - $time_connected;
                            $time_connected = 0;
                        }
                        $this->received_bytes += strlen($line);
                        if ($iterations % 20 == 0) {
                            $this->update_transfer_counters();
                        }
                    }

                    
                    // some dumbass webservers don't respect Connection: close and just
                    // leave the connection open, so we have to be diligent about
                    // calculating the content length so we can disconnect at the end of
                    // the response
                    if ( (!$headers_received) && (trim($line)=="") ) {
                        $headers_received = true;
                        $this->progress(HRP_DEBUG,"Got headers: {$this->response}");

                        if (preg_match('/^Content-Length: ([0-9]+)/im',$this->response,$matches)) {
                            $data_length = (int) $matches[1];
                            $this->progress(HRP_DEBUG,"Content length is $data_length");
                            $this->expected_bytes = $data_length;
                            $this->update_transfer_counters();
                        } else {
                            $this->progress(HRP_DEBUG,"No data length specified");
                        }
                        if (preg_match("/^Transfer-Encoding: chunked/im",$this->response,$matches)) {
                            $chunked = true;
                            $this->progress(HRP_DEBUG,"Chunked transfer encoding requested");
                        } else {
                            $this->progress(HRP_DEBUG,"CTE not requested");
                        }
                        
                        if (preg_match_all("/^Set-Cookie: ((.*?)\=(.*?)(?:;\s*(.*))?)$/im",$this->response,$cookielist,PREG_SET_ORDER)) {
                            foreach ($cookielist as $k=>$cookie) $this->cookie_headers .= $cookie[0]."\n";
                            
                            // get the path for which cookies will be valid if no path is specified
                            $cookiepath = preg_replace('/\/{2,}/','',$urldata['path']);
                            if (substr($cookiepath,-1)!='/') {
                                $cookiepath = explode('/',$cookiepath);
                                array_pop($cookiepath);
                                $cookiepath = implode('/',$cookiepath) . '/';
                            }
                            // process each cookie
                            foreach ($cookielist as $k=>$cookiedata) {
                                list(,$rawcookie,$name,$value,$attributedata) = $cookiedata;
                                $attributedata = explode(';',trim($attributedata));
                                $attributes = array();

                                $cookie = array(
                                    'value'=>$value,
                                    'raw'=>trim($rawcookie),
                                );
                                foreach ($attributedata as $k=>$attribute) {
                                    list($attrname,$attrvalue) = explode('=',trim($attribute));
                                    $cookie[$attrname] = $attrvalue;
                                }

                                if (!isset($cookie['domain']) || !$cookie['domain']) $cookie['domain'] = $urldata['host'];
                                if (!isset($cookie['path']) || !$cookie['path']) $cookie['path'] = $cookiepath;
                                if (isset($cookie['expires']) && $cookie['expires']) $cookie['expires'] = strtotime($cookie['expires']);
                                
                                if (!$this->validate_response_cookie($cookie,$urldata['host'])) continue;
                                
                                // do not store expired cookies; if one exists, unset it
                                if ( isset($cookie['expires']) && ($cookie['expires']<time()) ) {
                                    unset($this->response_cookies[ $name ][ $cookie['path'] ]);
                                    continue;
                                }
                                
                                $this->response_cookies[ $name ][ $cookie['path'] ] = $cookie;
                            }
                        }
                    }
                    
                    if ($this->result_close) {
                        if (preg_match_all("/HTTP\/([0-9\.]+) ([0-9]+) (.*?)[\r\n]/",$this->response,$matches)) {
                            $resultcodes = $matches[2];
                            foreach ($resultcodes as $k=>$code) {
                                if ($code!=100) {
                                    $this->progress(HRP_INFO,'HTTP result code received; closing connection');

                                    $this->result_code = $code;
                                    $this->result_text = $matches[3][$k];
                                    fclose($fp);
                    
                                    return ($this->result_code==200);
                                }
                            }
                        }
                    }
                }
                if (feof($fp)) $this->progress(HRP_DEBUG,'EOF on socket');
                @fclose ($fp);
                
                $this->update_transfer_counters();
                
                if (is_array($this->response_cookies)) {
                    // make sure paths are sorted in the order in which they should be applied
                    // when setting response cookies
                    foreach ($this->response_cookies as $name=>$paths) {
                        ksort($this->response_cookies[$name]);
                    }
                }
                $this->progress(HRP_INFO,'Request complete');
            } else {
                $this->error = strtoupper($urldata["scheme"])." connection to ".$hostname." port ".$urldata["port"]." failed";
                $this->progress(HRP_ERROR,$this->error);
                return false;
            }

        // perform an HTTP/HTTPS request using CURL
        } elseif ( !$this->disable_curl && ( ($urldata["scheme"]=="https") || ($this->force_curl) ) ) {
            $this->progress(HRP_INFO,'Passing HTTP request for $url to CURL');
            $curl_mode = true;
            if (!$this->_curl_request($url)) return false;
            
        // unknown protocol
        } else {
            $this->error = "Unsupported protocol: ".$urldata["scheme"];
            $this->progress(HRP_ERROR,$this->error);
            return false;
        }
        
        $this->raw_response = $this->response;

        $totallength = strlen($this->response);
        
        do {
            $headerlength = strpos($this->response,"\r\n\r\n");

            $response_headers = explode("\r\n",substr($this->response,0,$headerlength));
            $http_status = trim(array_shift($response_headers));
            foreach ($response_headers as $line) {
                list($k,$v) = explode(":",$line,2);
                $this->response_headers[trim($k)] = trim($v);
            }
            $this->response = substr($this->response,$headerlength+4);
    
            /* // Handled in-transfer now
            if (($this->response_headers['Transfer-Encoding']=="chunked") && (!$curl_mode)) {
                $this->remove_chunkiness();
            }
            */
        
            if (!preg_match("/^HTTP\/([0-9\.]+) ([0-9]+) (.*?)$/",$http_status,$matches)) {
                $matches = array("",$this->version,0,"HTTP request error");
            }
            list (,$response_version,$this->result_code,$this->result_text) = $matches;

            // skip HTTP result code 100 (Continue) responses
        } while (($this->result_code==100) && ($headerlength));
        
        // record some statistics, roughly compatible with CURL's curl_getinfo()
        if (!$curl_mode) {
            $total_time = $this->getmicrotime() - $time_request_start;
            $transfer_time = $total_time - $connect_time;
            $this->stats = array(
                "total_time"=>$total_time,
                "connect_time"=>$connect_time,  // time between connection request and connection established
                "process_time"=>$process_time,  // time between HTTP request and first data (non-headers) received
                "url"=>$url,
                "content_type"=>$this->response_headers["Content-Type"],
                "http_code"=>$this->result_code,
                "header_size"=>$headerlength,
                "request_size"=>$totallength,
                "filetime"=>strtotime($this->response_headers["Date"]),
                "pretransfer_time"=>$connect_time,
                "size_download"=>$totallength,
                "speed_download"=>$transfer_time > 0 ? round($totallength / $transfer_time) : 0,
                "download_content_length"=>$totallength,
                "upload_content_length"=>0,
                "starttransfer_time"=>$connect_time,
            );
        }
        
        
        $ok = ($this->result_code==200);
        if ($ok) {
            // if a page preprocessor is defined, call it to process the page contents
            if (is_callable($this->page_preprocessor)) $this->response = call_user_func($this->page_preprocessor,$this,$this->response);
            
            // if caching is enabled, save the page
            if ($this->caching) $this->_cache_store($cachetoken,$url);
        }

        return $ok;
    }
    
    function validate_response_cookie($cookie,$actual_hostname) {
        // make sure the cookie can't be set for a TLD, eg: '.com'      
        $cookiehost = $cookie['domain'];
        $p = strrpos($cookiehost,'.');
        if ($p===false) return false;
        
        $tld = strtolower(substr($cookiehost,$p+1));
        $special_domains = array("com", "edu", "net", "org", "gov", "mil", "int");
        $periods_required = in_array($tld,$special_domains) ? 1 : 2;
        
        $periods = substr_count($cookiehost,'.');
        if ($periods<$periods_required) return false;
        
        if (substr($actual_hostname,0,1)!='.') $actual_hostname = '.'.$actual_hostname;
        if (substr($cookiehost,0,1)!='.') $cookiehost = '.'.$cookiehost;
        $domain_match = (
            ($actual_hostname==$cookiehost) ||
            (substr($actual_hostname,-strlen($cookiehost))==$cookiehost)
        );
        
        return $domain_match;

    }
    
    function build_headers() {
        $headers = "";
        foreach ($this->headers as $name=>$value) {
            $value = trim($value);
            if (empty($value)) continue;
            $headers .= "{$name}: {$value}\r\n";
        }

        if (isset($this->request_cookies) && is_array($this->request_cookies)) {
            $cookielist = array();
            foreach ($this->request_cookies as $name=>$value) {
                $cookielist[] = "{$name}={$value}";
            }
            if (count($cookielist)) $headers .= "Cookie: ".implode('; ',$cookielist)."\r\n";
        }
        
        
        $headers .= "\r\n";
        
        return $headers;
    }
    
    // opposite of parse_url()
    function rebuild_url($urlinfo) {
        $url = $urlinfo['scheme'].'://';
        
        if ($urlinfo['user'] || $urlinfo['pass']) {
            $url .= $urlinfo['user'];
            if ($urlinfo['pass']) {
                if ($urlinfo['user']) $url .= ':';
                $url .= $urlinfo['pass'];
            }
            $url .= '@';
        }
        
        $url .= $urlinfo['host'];
        if ($urlinfo['port']) $url .= ':'.$urlinfo['port'];
        
        $url .= $urlinfo['path'];
        
        if ($urlinfo['query']) $url .= '?'.$urlinfo['query'];
        if ($urlinfo['fragment']) $url .= '#'.$urlinfo['fragment'];
        
        return $url;
    }
    
    function _replace_hostname(&$url,$new_hostname) {
        $parts = parse_url($url);
        $old_hostname = $parts['host'];
        
        $parts['host'] = $new_hostname;
        
        $url = $this->rebuild_url($parts);
                
        return $old_hostname;
    }
    
    function _curl_request($url) {
        $this->error = false;

        // if a direct connection IP address was specified, replace the hostname
        // in the URL with the IP address, and set the Host: header to the
        // original hostname
        if ($this->connect_ip) {
            $old_hostname = $this->_replace_hostname($url,$this->connect_ip);
            $this->headers["Host"] = $old_hostname;
        }
        

        unset($this->headers["Content-Length"]);
        $headers = explode("\n",$this->build_headers());
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url); 
        curl_setopt($ch,CURLOPT_USERAGENT, $this->headers["User-Agent"]); 
        curl_setopt($ch,CURLOPT_HEADER, 1); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); 
//      curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1); // native method doesn't support this yet, so it's disabled for consistency
        curl_setopt($ch,CURLOPT_TIMEOUT, 10);
        if ($this->curl_proxy) {
            curl_setopt($ch,CURLOPT_PROXY,$this->curl_proxy);
        }
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        
        if ($this->method=="POST") {
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$this->post_data);
        }
        if ($this->insecure_ssl) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        }
        if ($this->ignore_ssl_hostname) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
        }
        
        $this->response = curl_exec ($ch);
        if (curl_errno($ch)!=0) {
            $this->error = "CURL error #".curl_errno($ch).": ".curl_error($ch);
        }
        
        $this->stats = curl_getinfo($ch);
        curl_close($ch);
        
        return ($this->error === false);
    }
    
    function progress($level,$msg) {
        if (is_callable($this->progress_callback)) call_user_func($this->progress_callback,$level,$msg);
    }
    
    // Gets any available HTTPRetriever error message (including both internal
    // errors and HTTP errors)
    function get_error() {
        return $this->error ? $this->error : 'HTTP ' . $this->result_code.': '.$this->result_text;
    }
    
    function get_content_type() {
        if (!$ctype = $this->response_headers['Content-Type']) {
            $ctype = $this->response_headers['Content-type'];
        }
        list($ctype,) = explode(';',$ctype);
        
        return strtolower($ctype);
    }
    
    function update_transfer_counters() {
        if (is_callable($this->transfer_callback)) call_user_func($this->transfer_callback,$this->received_bytes,$this->expected_bytes);
    }

    function set_transfer_display($enabled = true) {
        if ($enabled) {
            $this->transfer_callback = array(&$this,'default_transfer_callback');
        } else {
            unset($this->transfer_callback);
        }
    }
    
    function set_progress_display($enabled = true) {
        if ($enabled) {
            $this->progress_callback = array(&$this,'default_progress_callback');
        } else {
            unset($this->progress_callback);
        }
    }
    
    function default_progress_callback($severity,$message) {
        $severities = array(
            HRP_DEBUG=>'debug',
            HRP_INFO=>'info',
            HRP_ERROR=>'error',
        );
        
        echo date('Y-m-d H:i:sa').' ['.$severities[$severity].'] '.$message."\n";
        flush();
    }

    function default_transfer_callback($transferred,$expected) {
        $msg = "Transferred " . round($transferred/1024,1);
        if ($expected>=0) $msg .= "/" . round($expected/1024,1);
        $msg .= "KB";
        if ($expected>0) $msg .= " (".round($transferred*100/$expected,1)."%)";
        echo date('Y-m-d H:i:sa')." $msg\n";
        flush();
    }   
    
    function getmicrotime() { 
        list($usec, $sec) = explode(" ",microtime()); 
        return ((float)$usec + (float)$sec); 
    }   
}



?>
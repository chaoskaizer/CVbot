<?php
$this->AddHook('before_harvest_crops', 'Wall');
  
function Wall($bot) {
    if ($bot->firstrun)
      { //not work if it's first bot cycle
        $Name = "Wall"; $Version = "0.1"; $Date = "2011-02-11";
        $bot->ld->UpdatePluginVersion($bot, $Name, $Version, $Date )  ;
        return;
      }
    $bot->ReloadConfig();
    $data = $bot->ld->GetPlSettings("Wall");
   // if (isset($data->Run)) { $Run = $data->Run; }else {$bot->SendMsg('Wall not activated.'); return;}
   // if (!isset($data->Run)) { $bot->SendMsg('Wall not activated...'); return;}
   // if (!$data->Run) { $bot->SendMsg('Wall not activated...='); return;}

   // if (isset($data->Debug)) {$Debug = false;}else{$Debug = true;}
   // if ($data->Debug) {$Debug = false;}else{$Debug = true;}

    $now = time();
    $uid = $bot->zyUid;
    $bot->SendMsg('Wall: Started for: '. $uid);

    include('Plugins/Wall/Wall_class.php');
    $DB = new LocalDB();
    $DB->ConnectTo("User", "Wall");
    $DB->InitDB();

    // what request has to be checked?
    $WallSet = array();
    $WallSet["Request"] = (array)$bot->ld->GetPlSettings('WallRequests');
    // what request has already in the DB.
    $query = "SELECT * FROM Request";
    $DBrequests = $DB->GetSelect($query) ;
      $bot->SendMsg('Requests in DB: ' . count($DBrequests));
    $requestDB = array();
    foreach($DBrequests as $option)
      {
        if($uid == $option['uid'])
        {
          if(array_key_exists($option['type'], $requestDB))
            { // alreadt there check the latest time.
              if($option['time'] > $requestDB[$option['type']])
               { // time from current request is bigger
                $requestDB[$option['type']]=$option['time'];
               }
            }
            else
            { // does not exist yet
               $requestDB[$option['type']]=$option['time'];

            }
        }
      }
    //var_dump($requestDB);
    // viral
    $query = "SELECT * FROM viral";
    $DB1virals = $DB->GetSelect($query) ;
      $bot->SendMsg('Virals in DB: ' . count($DB1virals));
    $DBvirals = array();
    foreach($DB1virals as $option)
     {
       $timeTillReset = (int)$option['timeTillReset'];
       $timeTillReset = $timeTillReset * 60 * 60 ; // sec.
       $DBvirals[$option['name']] =  $timeTillReset ;
     }
    //var_dump($DBvirals);


           // Check if log folder exist.
     //      $vFolder = "tmp_dir/GetGift/";
     //      if (!is_dir($vFolder)) { @mkdir($vFolder, 0777, true);  }
     //      // make file name.
     //      date_default_timezone_set("Europe/London");
     //      $date = date("Y-m-d_H-i");
     //      $filename = $vFolder . $date . "_Get_gifts.html";


    $requestsFound = 0;
    foreach($WallSet["Request"] as $req => $action)
    {
      if(!$action) continue;  // check if we need to request this item. if not, goto next.
      if(array_key_exists($req, $requestDB))
        { // We have requested this befoe, check how long this is ago?
          // $now = now
          // $requestDB[$req] = last time checked.
          // $DBvirals[$req]  = sec till next request.
          $nextRequest = $requestDB[$req] + $DBvirals[$req];
          if($nextRequest > $now )
            { // it is not yet time for this request.
              $bot->SendMsg('Wall request: ' . $req . ' ' . $bot->ld->nicetime($nextRequest));
              continue;
            }
        }

      $requestsFound++;
      $bot->SendMsg('Wall Request: ' . $req);
      //$bot->streamPublish($req);
      $type = $req;
      $amf = new AMFObject($bot->streamPublish($req));
        $deserializer = new AMFDeserializer($amf->rawData);
        $deserializer->deserialize($amf);
        $bod = new MessageBody();
        $bod = $amf->_bodys[0];
       $result = "OK";
      //result  String  postLimitReached
      if (isset($bod->_value['data'][0]['data']['result'])) $result = $bod->_value['data'][0]['data']['result'];

      //if($result != "postLimitReached")
      //  {
          $title = "title";
          //title  String Reference Jansen needs your help completing a collection.
          if (isset($bod->_value['data'][0]['data']['full']['title'])) $title =htmlspecialchars($bod->_value['data'][0]['data']['full']['title'], ENT_QUOTES);

          $image = "";
          //media  Array Reference           //type  String  image
          //src  String  http://assets.cityville.zynga.com/27093/images/feed_virals/feed_viral_wishlist.png
          if (isset($bod->_value['data'][0]['data']['full']['media'][0]['src'])) $image =$bod->_value['data'][0]['data']['full']['media'][0]['src'];

          $description = "description";
          // description  String Reference  Jansen is looking for any of these items: Zoning Permit in Cityville.
          if (isset($bod->_value['data'][0]['data']['full']['description'])) $description =htmlspecialchars($bod->_value['data'][0]['data']['full']['description'], ENT_QUOTES);

          $butonHref = "";
          $butonText = "";
          // buttons  Array  
          // href  String Reference  http://apps.facebook.com/cityville/neighbors.php?uid=20
          // text  String  Send collectables!
          if (isset($bod->_value['data'][0]['data']['full']['buttons'][0]['href'])) $butonHref =$bod->_value['data'][0]['data']['full']['buttons'][0]['href'] ;
          if (isset($bod->_value['data'][0]['data']['full']['buttons'][0]['text'])) $butonText =htmlspecialchars($bod->_value['data'][0]['data']['full']['buttons'][0]['text'], ENT_QUOTES);

          //  feed_type  String  wall-to-wall
          $feed_type = "feed_type";
          if (isset($bod->_value['data'][0]['data']['feed_type'])) $feed_type =$bod->_value['data'][0]['data']['feed_type'];

       // }
         $now = time();
         //$butonHref = '';
         //$image = '';
         $query = "insert into Request values ('$uid','$now','$title','$type','$image','$description','$butonHref','$butonText','$feed_type','$result')";
         //$bot->SendMsg('Query: '. $query);
         $DB->ExecQuery($query);
         // done.

    }

    // let's make the html file.
    if($requestsFound > 0)
    {
       date_default_timezone_set('GMT');
       $now = date("D Y F d  H:i:s");
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
             <head><title>Wall</title><meta http-equiv="Content-type" content="text/html; charset=utf-8">'."\r\n";
    $html .= '<style>
              body                {  }
              .Wall1              { font-family: "lucida grande",tahoma,verdana,arial,sans-serif; font-size: 11px; color: #333; text-align: left; direction: ltr;
                                      margin-top:6px;overflow:hidden;padding-right:10px;margin-bottom:5px}
              .Wall1_Media        {float:left;overflow:hidden;padding-right:10px}
              .Wall1_MediaSingle  {padding-right:10px}
              .Wall1_Title        {font-weight:bold;padding-top:3px}
              .Wall1_Info         {display:table}
              .Wall1_Caption      {color:#808080;padding-top:3px}
              .Wall1_Caption a    {color: #6D84B4; font-weight:bold;}
              .Wall3 img          {display:block}
              </style>'."\r\n";
    $html .= '<script type="text/javascript">function Clicklinks(){var u =0; for (var i = 0; i < document.links.length; ++i){ if(document.links[i].style.visibility == "hidden"){continue;} u++; window.open(document.links[i].href, "_blank"); document.links[i].style.visibility = "hidden"; document.links[i].style.display = "none"; if (u >= 5){alert("Close the windows when they are loaded. Repeat clicking this buton untill all links are gone. Than check you city."); break; }}} </script>';
    $html .= '</head><body ><H1>Wall by 12christiaan</H1>';
    $html .= 'This file is created:  <u>'.$now. '</u> for user <u>'.$bot->usern.'</u> ('.$uid.') <br>';
    $html .= 'The request below will send gifts to the creator of this file and / or you will get rewards.<br>';
    $html .= 'You can use the requests manualy (open 1 by 1) or click the buton below to open 5 requests at 1 click.<br>';
    $html .= 'Please make sure that you are login in to facebook as a different user. You can not give this requests to your self.<br>';
    $html .= 'Or send this file to and other user, to open the links.<br>';
    $html .= '<hr>';
    $html .= '<input type="button" onclick="Clicklinks()" value="Open Links till 5 Gifts"><br>'."\r\n";
    $html .= '<br><hr>'."\r\n";
   $query = "SELECT * FROM Request WHERE result='OK' AND uid='".$uid."'";
   $res = $DB->GetSelect($query) ;
    foreach ($res as $item)
     {
        $html .= '<a href="'.$item['butonHref'].'" target="_blank">';
        $html .= '<div class="Wall1" ><div class="Wall1_Media Wall1_MediaSingle" ><div class="Wall3">';
        $html .= ' <img class="img" src="'.$item['image'].'"></div></div>';
        $html .= '<div class="Wall1_Info "><div class="Wall1_Title">';
        $html .=  htmlspecialchars_decode($item['title']) ;
        $html .= '</div><div class="Wall1_Caption">';
        $html .=  $item['type'] ;
        $html .= '</div><div class="Wall1_Caption">';
        $html .=  $item['description'] ;
        $html .= '</div><div class="Wall1_Caption">';
        // orig1 $html .= $bot->ld->nicetime($item['time']) . '&nbsp; via CityVile&nbsp;<a href="'.$item['butonHref'].'" target="_blank">'.$item['butonText'].'</a>';
        $html .= $bot->ld->nicetime($item['time']) . '&nbsp; via CityVile&nbsp;<b>'.$item['butonText'].'</b>';
        $html .= '</div>';
        $html .= '</div></div><hr></a>'."\r\n";
     }
    $html .= ''."\r\n";
    $html .= '';
    $html .= '';

       // check if folder exist.
       $vFolder = 'tmp_dir/Wall/';
       if (!is_dir($vFolder)) { @mkdir($vFolder, 0777, true);  }
       // date
       $today = date("Y-m-d-H");
       $HTMLFile = $vFolder . $today . '_'.$uid. '_Wall.html';
        $fl = fopen($HTMLFile, 'w');
        fwrite($fl, $html);
        fclose($fl);
    $bot->SendMsg('Wall Requests writen in file.');


    }
    else
    {
      $bot->SendMsg('Wall Requests NOT writen to file, there where NO new requests.');
    }









    $bot->pm->RefreshMePlugin("Wall");
}



?>
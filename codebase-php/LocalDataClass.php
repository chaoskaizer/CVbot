<?php

class LocalData {

    var $db;
    var $dbfile;
    var $userId;

    // ==========================================================================
    function LocalData() {

    }

    // ==========================================================================
    function ConnectTo($userid)
    {
        $this->dbfile = 'Profiles\\' . $userid . '.sqlite';
            $this->db = new SQLite3($this->dbfile);
        $this->ExecQuery("PRAGMA cache_size=200000" );
        $this->ExecQuery("PRAGMA synchronous=OFF");
        $this->ExecQuery("PRAGMA journal_mode=MEMORY");
        $this->ExecQuery("PRAGMA temp_store=MEMORY");
        $this->ExecQuery("vacuum");
    }
    // ==========================================================================
    function DBerror($query)
    { // Show Error mesages from the database lock etc.
      echo "We came to error message.";
    }
    // ==========================================================================
    function EasyConnect() {
        $this->ConnectTo($this->userId);
    }
    // ==========================================================================
    function Disconnect() {
        if(isset ($this->db)){
           $this->db->close();
        }
    }
    // ==========================================================================
    function ExecQuery($query) {
        if ($this->db) {
            $this->db->exec($query) OR $this->ExecQueryexec($query, 3);      //prevent lock error
        }
    }
    // ==========================================================================
    function ExecQueryexec($query, $try) {
        if ($this->db)
        {
           while($try > 0)
            {
              usleep(25);
              $try--;
              $this->db->exec($query) OR $this->ExecQueryexec($query, $try);
              echo "Retry DB." . $try;
            }
        }
    }
    // ==========================================================================
    function SavePlSettings($plname, $settings) {
        if ($this->db) {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="plugin_settings"'));
            if ($res[0]["cnt"] > 0) {
                $res = ($this->GetSelect('select count(*) as settings_exists from plugin_settings where plname="'.$plname.'"'));
                if ($res[0]["settings_exists"] > 0) {
                    $this->ExecQuery("update plugin_settings set value='" . serialize($settings) . "' where plname='".$plname."'");
                }else{
                    $this->ExecQuery("insert into plugin_settings values ('".$plname."', '" . serialize($settings) . "')");
                }
            }else{
                echo "There is not table 'plugin_settings' (If this is the first run, ignore this error)\n";
            }
        }
    }
    // ==========================================================================
    function UpdatePlSettings($plname, $settings) {
        if ($this->db) {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="plugin_settings"'));
            if ($res[0]["cnt"] > 0) {
                $res = ($this->GetSelect('select count(*) as settings_exists from plugin_settings where plname="'.$plname.'"'));
                if ($res[0]["settings_exists"] > 0)
                {
                    $res2 = $this->GetSelect('select value from plugin_settings where plname="'.$plname.'"');
                    $CurSet = (array)unserialize($res2[0]['value']);
                    //$CurSet = (array)$this->GetPlSettings($plname);

                    //$NewSet = array_merge($CurSet, $settings);
                    $NewSet = (array)$settings + $CurSet;


                    $this->ExecQuery("update plugin_settings set value='" . serialize($NewSet) . "' where plname='".$plname."'");
                }else{
                    $this->ExecQuery("insert into plugin_settings values ('".$plname."', '" . serialize($settings) . "')");
                }
            }else{
                echo "There is not table 'plugin_settings' (If this is the first run, ignore this error)\n";
            }
        }
    }
    // ==========================================================================
    function SaveInTable($TableName, $settings) {
        if ($this->db) {
         //   $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="'.$TableName.'"'));
         //   if ($res[0]["cnt"] > 0)
         //   {
         //     if(!is_array($settings)) {echo "Error SaveInTable, no array.";  return;}
              foreach($settings as $key => $item)
              { //insert or replace into tbl values('john', 'someaddr1')
                $this->ExecQuery("REPLACE INTO '.$TableName.' VALUES('".$key."', '" . $item . "');");
                //$this->ExecQuery("UPDATE '.$TableName.' SET item = '".$key."', value='" . $item . "'");
              }
         //   }else{
         //       echo "There is not table '".$TableName."'\n";
         //   }
        }
    }
    // ==========================================================================
    function GetPlSettings($plname) {
        $data='';
        if ($this->db) {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="plugin_settings"'));
            if ($res[0]["cnt"] > 0) {
                $res = ($this->GetSelect('select count(*) as settings_exists from plugin_settings where plname="'.$plname.'"'));
                if ($res[0]["settings_exists"] > 0) {
                    $res = $this->GetSelect('select value from plugin_settings where plname="'.$plname.'"');
                    $data = unserialize($res[0]['value']);
                }
            }
        }
        return $data;
    }
    // ==========================================================================
    function GetObjects() {
        $data='';
        if ($this->db) {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="objects"'));
            if ($res[0]["cnt"] > 0) {
                $res = ($this->GetSelect('select count(*) as objects_exists from objects '));
                if ($res[0]["objects_exists"] > 0) {
                    $res = $this->GetSelect('select value from objects ');
                    $data = unserialize(base64_decode($res[0]['value']));
                }
            }
        }
        return $data;
    }
    // ==========================================================================
    function GetSelect($query) {
        $arr = Array();
        if ($this->db) {
        if($res = $this->db->query($query))
          { // true = good
            while ($row = @$res->fetchArray())
            {  $arr[] = $row;  }
          }
          else
          {  $this->DBerror($query);
             // error code here.
            echo "<br>\n";
            echo "*************************************** <br>\n";
            echo "** The Database was not responding    <br>\n";
            echo "** Please try refresh in few seconds. <br>\n";
            echo "**  <br>\n";
            echo "** If you see this a lot, try not to use the plugins when BOT is doing work. <br>\n";
            echo "** Stop the BOT before using the plug-in's <br>\n";
            echo "*************************************** <br>\n";
            die;

          }
        }
        return $arr;
    }
    // ==========================================================================
    function GetIconByItemName($name) {
        if ($this->db)
        {
          $query = "SELECT url FROM Images WHERE ItemName == '".$name."'";
           $res = $this->GetSelect($query) ;
           //if($res[0] ==) $res = "assets/missions/card_icon.png";
           $res = $res[0]['url'];

        }
        return $res;
    }
    // ==========================================================================
    function GetOne($query) {
        $arr = Array();
        if ($this->db) {
            $res = $this->db->querySingle($query);
        }
        return $res;
    }

    // ==========================================================================
    function SimpleInsert($arr, $tbl) {
      if(is_array($arr)){  // added by 12christiaan to detect empty arry
        foreach ($arr as $key => $val) {
            if (!is_array($val)) {
                if (isset($key) && $key != 'fakePlayerData')
                    $this->ExecQuery('insert into ' . $tbl . ' values ("' . $key . '", "' . $val . '")');
            }
        }
      }// if not array
    }
    // ==========================================================================
    function UpdateNinfo($Ninfo) {
      if(is_array($Ninfo))
        { // first check if the uid is already in the database.
            $res = ($this->GetSelect('SELECT * FROM neighborsinfo WHERE uid="'.$Ninfo['id'].'"'));
            if(isset($res[0]))
              { // uid already in DB                for now just renew
              //var_dump($res);
                  $uid = $Ninfo['id'];
                  if(isset($res[0]['name']))  { $name = $res[0]['name'];}else{ $name = $Ninfo['name'];}
                  if(isset($res[0]['status'])){ $status = $res[0]['status'];}else{ $status = $Ninfo['status'];}
                  $gotn = $Ninfo['gotn'];
                  // 2010-12-28 update to have the correct time in the database.
                  if($Ninfo['lastvisit']==0 || $Ninfo['lastvisit']=="0"){ $lastvisit = $res[0]['lastvisit'];}else{ $lastvisit = $Ninfo['lastvisit'];}
                  $info = " ";//$info = base64_encode(serialize($Ninfo));
                $res = ($this->GetSelect('UPDATE neighborsinfo SET uid="'.$uid.'", name="'. $name.'", status="'.$status.'", lastvisit="'.$lastvisit.'", gotn="'.$gotn.'", info="'.$info.'" WHERE uid="'.$uid.'"'));
              }
              else
              { // uid not yet in DB
                  $uid = $Ninfo['id'];
                  $name = $Ninfo['name'];
                  $status = $Ninfo['status'];   // own or NN
                  $gotn = $Ninfo['gotn'];   // own or NN
                  $lastvisit = $Ninfo['lastvisit'];
                  $info = " ";//$info = base64_encode(serialize($Ninfo));
                  $this->ExecQuery('insert into neighborsinfo values ("' . $uid . '", "' . $name . '", "' . $status . '", "' . $lastvisit . '","' . $gotn . '", "' . $info . '")');
              }
      } // if not array
    } // end function

    // ==========================================================================
    function UpdateNtime($Ninfo) {
      if(is_array($Ninfo))
        { // first check if the uid is already in the database.
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM neighborsinfo WHERE uid="'.$Ninfo['id'].'"'));
            if ($res[0]["cnt"] > 0)
              { // uid already in DB
                $res = ($this->GetSelect('UPDATE neighborsinfo SET lastvisit = '.$Ninfo['lastvisit'].' WHERE uid="'.$Ninfo['id'].'"'));
                //echo "UpdateNinfo time update: \n";
              }
              else
              { // uid not yet in DB
                echo "UpdateNinfo time update, but uid not found. \n";
              }
      } // if not array
    } // end function

    // ==========================================================================
    function ArrayInsert($arr, $tbl) {
      if(is_array($arr)){  // added by 12christiaan to detect empty arry
        foreach ($arr as $key => $val) {
            if (is_array($val))
              {
                  $val['cityname'] = str_replace ( "'", " ", $val['cityname'] );
                  $val = base64_encode(serialize($val));
                  $this->ExecQuery('insert into ' . $tbl . ' values ("' . $key . '", "' . $val . '")');
              }
        }
      } // if not array
    }
    // ==========================================================================
    function OrdersInsert($arr) {
      if(is_array($arr))
      {                                               // added by 12christiaan to detect empty arry
        $orderTypes = array("order_visitor_help", "order_lot", "order_train");
        //var_dump($arr);
        foreach($orderTypes as $orderType)
         {
          echo 'order info id:' .$orderType . ' ' .$way . ' ' .$status . "\n";
          if(!array_key_exists($orderType, $arr)){ break;}
          $ways = array("sent", "received");
          foreach($ways as $way)
           {
          echo 'order info id:' .$orderType . ' ' .$way . ' ' .$status . "\n";
            if(!array_key_exists($way, $arr[$orderType])){ break;}
            $statuss = array("pending", "accepted");
            foreach($statuss as $status)
             {
          echo 'order info id:' .$orderType . ' ' .$way . ' ' .$status . "\n";
              if(!array_key_exists($status, $arr[$orderType][$way])) { break;}
              foreach($arr[$orderType][$way][$status] as $id => $item)
                {
                  echo 'order info id:' .$orderType . ' ' .$way . ' ' .$status . ' ' .$id  . "\n";
                }


             }
           }
         }



       // foreach ($arr as $key => $val) {
       //     if (is_array($val))
       //       {
       //           $val['cityname'] = str_replace ( "'", " ", $val['cityname'] );
       //           $val = base64_encode(serialize($val));
       //           $this->ExecQuery('insert into ' . $tbl . ' values ("' . $key . '", "' . $val . '")');
       //       }
       // }
      } // if not array
    }

    // ==========================================================================
    function UpdatePluginVersion(&$bot, $Name, $Version, $Date )
    { // This fuction to maintain the version controle for the plugins.
        $this->ExecQuery("CREATE TABLE if not exists [PluginVersion] ([name] NVARCHAR(20)  NULL,[version] VARCHAR(5),[date] VARCHAR(15)  NULL,UNIQUE (name))");
        $this->ExecQuery("REPLACE into PluginVersion values ('$Name', '$Version', '$Date')");
    }
    // ==========================================================================
    function GetPlVersion($plname) {
        $data='';
        if ($this->db) {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="PluginVersion"'));
            if ($res[0]["cnt"] > 0) {
                $res = ($this->GetSelect('select count(*) as settings_exists from PluginVersion where name="'.$plname.'"'));
                if ($res[0]["settings_exists"] > 0) {
                    $res = $this->GetSelect('select * from PluginVersion where name="'.$plname.'"');
                    $data['name'] = (string)$res[0]['name'];
                    $data['version'] = (string)$res[0]['version'];
                    $data['date'] = (string)$res[0]['date'];
                }
            }
        }
        return $data;
    }

    // ==========================================================================
    function SaveProfile2db(&$bot)
    {
          // put here code that saving game profile to db file
        $this->ExecQuery("drop table if exists 'userInfo'");
        $this->ExecQuery("CREATE TABLE if not exists [userInfo] ([name] NVARCHAR(100)  NULL,[value] VARCHAR(1000)  NULL)");
           $this->ExecQuery("CREATE INDEX [IDX_userInfo1_] ON [userInfo]([name]  ASC)");
        $this->SimpleInsert($bot->cfg->_value['data'][0]['data']['userInfo']['player'], 'userInfo');
        $this->ExecQuery('insert into userInfo values ("worldName", "' . $bot->usern  . '")');
        $this->ExecQuery('insert into userInfo values ("Goods", "' . $bot->waresMax  . '")');

        $this->ExecQuery("drop table if exists 'neighbors'");
        $this->ExecQuery("CREATE TABLE if not exists [neighbors] ([name] NVARCHAR(100)  NULL,[value] VARCHAR(1000)  NULL)");
           $this->ExecQuery("CREATE INDEX [IDX_neighbors1_] ON [neighbors]([value]  ASC)");
//        $this->SimpleInsert($bot->cfg->_value['data'][0]['data']['userInfo']['player']['neighbors'], 'neighbors');
// replaced for Neigbor plugin
        $this->ArrayInsert($bot->cfg->_value['data'][1]['data']['neighbors'], 'neighbors');

        $this->ExecQuery("drop table if exists 'world'");
        $this->ExecQuery("CREATE TABLE if not exists [world] ([name] NVARCHAR(100)  NULL,[value] VARCHAR(1000)  NULL)");
           $this->ExecQuery("CREATE INDEX [IDX_world1_] ON [world]([name]  ASC)");
        $this->SimpleInsert($bot->cfg->_value['data'][0]['data']['userInfo']['world'], 'world');
        // store all objects
        $this->ExecQuery("drop table if exists 'objects'");
        //$this->ExecQuery('CREATE TABLE if not exists [objects] ( [n] INTEGER,  [value] vARCHAR(100)  NULL )');
        $this->ExecQuery("CREATE TABLE if not exists [objects] ([n] INTEGER,[value] vARCHAR(100)  NULL,[className] VARCHAR(1000)  NULL,[id] VARCHAR(10)  NULL)");
           $this->ExecQuery("CREATE INDEX [IDX_objects1_] ON [objects]([value]  ASC)");
        $id=0;
        $totalStorageGoods = 300;
        // added 2011-01-14 for 92% error : world has moved
        //if(is_array($bot->cfg->_value['data'][0]['data']['userInfo']['world']['objects'])){  // added by 12christiaan to detect empty arry
        //   foreach ($bot->cfg->_value['data'][0]['data']['userInfo']['world']['objects'] as $fobj)
        if(is_array($bot->fobjects)){  // added by 12christiaan to detect empty arry
           foreach ($bot->fobjects as $fobj)
           {
             // added to count total storge
             if(isset($fobj['itemName']))               
               {
					$cv_storage_type = array(
						'storage_barn'=> 415,
						'storage_silo'=> 100,
						'storage_outskirtsfarm'=> 485,
						'storage_shack'=> 1000,
						'goods_pier'=> 420,
						'storage_grain_elevator' => 700, // Zynga update on March 2011 @Cybuster
						'storage_grainsilo' => 150 // Zynga update on March 2011 @Cybuster
					);
					
					foreach($cv_storage_type as $sname =>$sval){
						if ( $fobj['itemName'] == $sname ) { 
							$totalStorageGoods = $totalStorageGoods + $sval; 
						}
					}
               }
               $val=base64_encode(serialize($fobj));
               //$query="insert into objects values ('" . $id . "', '" . $val . "')";
               $query="insert into objects values ('" . $id . "', '" . $val . "','".$fobj['className']."','".$fobj['id']."')";
               $id++;
               $this->ExecQuery($query);
           }
           // store the MaxGoods
           $this->ExecQuery('insert into userInfo values ("MaxGoods", "' . $totalStorageGoods . '")');
        } // if not array
        // store all franchises
        $this->ExecQuery("drop table if exists 'franchises'");
        $this->ExecQuery('CREATE TABLE if not exists [franchises] ( [n] INTEGER,  [value] vARCHAR(100)  NULL )');
           $this->ExecQuery("CREATE INDEX [IDX_franchises1_] ON [franchises]([n]  ASC)");
        $id=0;
        if(is_array($bot->cfg->_value['data'][0]['data']['franchises'])){  // added by 12christiaan to detect empty arry
           foreach ($bot->cfg->_value['data'][0]['data']['franchises'] as $fobj)
           {
               $val=base64_encode(serialize($fobj));
               $query="insert into franchises values ('" . $id . "', '" . $val . "')";
               $id++;
               $this->ExecQuery($query);
           }
        } // if not array
        //======================================================================
        //added for WALL
        $this->ExecQuery("CREATE TABLE if not exists [WallRequests]  ([item] NVARCHAR(100)  NULL,[value] VARCHAR(10000)  NULL,UNIQUE (item))  ");

        $this->ExecQuery("CREATE TABLE if not exists [plugin_settings]  ([plname] NVARCHAR(100)  NULL,[value] VARCHAR(10000)  NULL)  ");
        $this->ExecQuery("CREATE TABLE if not exists [neighborsinfo] ([uid] NVARCHAR(25) NULL,[name] NVARCHAR(50) NULL, [status] NVARCHAR(25) NULL, [lastvisit] NVARCHAR(25)  NULL, [gotn] NVARCHAR(5)  NULL,[info] VARCHAR(1000)  NULL)  ");
        $this->ExecQuery("CREATE TABLE if not exists [orders] ([types] NVARCHAR(15) NULL,[way] NVARCHAR(10) NULL, [status] NVARCHAR(15) NULL, [uid] NVARCHAR(25)  NULL, [info] NVARCHAR(500) NULL)  ");
        $this->ExecQuery("CREATE TABLE if not exists [Inventory] ([Type] NVARCHAR(15) NULL,[Item] NVARCHAR(100) NULL, [Use] NVARCHAR(15) NULL, [Keep] NVARCHAR(15)  NULL, [Number] NVARCHAR(10) NULL)  ");
        $this->UpdateInventory($bot->cfg->_value['data'][0]['data']['userInfo']['player']['inventory'] , "Y");
        // for images
        $this->ExecQuery("CREATE TABLE if not exists [Images] ([ItemName] NVARCHAR(55) NULL,[url] NVARCHAR(100) NULL,[download] NVARCHAR(5) NULL,[GameVersion] NVARCHAR(10) NULL,[DownloadTime] NVARCHAR(10) NULL,UNIQUE (ItemName))  ");
           //$this->ExecQuery("CREATE INDEX [IDX_Images1_] ON [Images]([ItemName]  ASC)");
        $this->ExecQuery("CREATE TABLE if not exists [Images2] ([ItemName] NVARCHAR(55) NULL,[url] NVARCHAR(100) NULL,[hashurl] NVARCHAR(100) NULL,[download] NVARCHAR(5) NULL,[GameVersion] NVARCHAR(10) NULL,[DownloadTime] NVARCHAR(10) NULL,UNIQUE (ItemName))  ");
           //$this->ExecQuery("CREATE INDEX [IDX_Images21_] ON [Images2]([ItemName]  ASC)");


        //$this->OrdersInsert($bot->cfg->_value['data'][0]['data']['userInfo']['player']['Orders']);

        //save collections
        $this->ExecQuery("drop table if exists 'collection'");        
        $this->ExecQuery("CREATE TABLE if not exists [collection]  ([collectionType] NVARCHAR(100)  NULL,[completed] VARCHAR(1000)  NULL)  ");
           $this->ExecQuery("CREATE INDEX [IDX_collection1_] ON [collection]([collectionType]  ASC)");
        $this->SimpleInsert($bot->cfg->_value['data'][0]['data']['userInfo']['player']['completedCollections'], 'collection');
        // save collection items
        $this->ExecQuery("drop table if exists 'collectionItems'");
        $this->ExecQuery("CREATE TABLE if not exists [collectionItems]  ([collection] NVARCHAR(100)  NULL,[item] VARCHAR(100)  NULL,[amount] VARCHAR(100)  NULL)  ");
           $this->ExecQuery("CREATE INDEX [IDX_collectionItems1_] ON [collectionItems]([collection]  ASC)");
        if(is_array($bot->Collection)){
           foreach ($bot->Collection as $CollName => $coll)
           {
              foreach($coll as $CollItem => $CollAmount)
              {
               $query="insert into collectionItems values ('" . $CollName . "', '" . $CollItem . "', '" . $CollAmount . "')";
               $this->ExecQuery($query);
              }
           }
        } // if not array
//        $this->SimpleInsert($bot->cfg->_value['data'][0]['data']['userInfo']['player']['completedCollections'], 'collection');
    }

    // ==========================================================================
    function SaveInventory2db(&$bot) {
          // put here code that saving game profile to db file
        $this->ExecQuery("CREATE TABLE if not exists [Inventory] ([Type] NVARCHAR(15) NULL,[Item] NVARCHAR(100) NULL, [Use] NVARCHAR(15) NULL, [Keep] NVARCHAR(15)  NULL, [Number] NVARCHAR(10) NULL)  ");
        $this->UpdateInventory($bot->cfg->_value['data'][0]['data']['userInfo']['player']['inventory'] , "Y");
    }

    // ==========================================================================
    // ==========================================================================
    //UpdateInventory
    // added by 12christiaan to detect empty arry
    //                                      [Type] [Item Name] [Use] [Keep] [Number]
    // ==========================================================================
    function UpdateInventory($arr, $EmptyFirst) {

      $res = ($this->GetSelect('SELECT count(*) as cnt FROM sqlite_master where tbl_name="Inventory"'));
      if ($res[0]["cnt"] == 0) { return;}
       // let's empty all items first.
      if($EmptyFirst == "Y") {$res = ($this->GetSelect("UPDATE Inventory SET Number = '0' WHERE Type='item' "));}
      if(is_array($arr)){
        foreach ($arr as $key => $val)
        {
          if($key == "count")
            {
            $res = ($this->GetSelect('SELECT count(*) as cnt FROM Inventory WHERE Type="count"'));
            if ($res[0]["cnt"] > 0)
              { $res = ($this->GetSelect("UPDATE Inventory SET Number = '".$val."' WHERE Type='count'" )); }
              else
              { $this->ExecQuery('insert into Inventory values ( "count", "-", "-", "-", "' . $val . '")');}
            }
            if(is_array($val))
              {
                //$DBitems = $this->GetSelect('SELECT Type,Item,Number FROM Inventory WHERE Type="item"');
                foreach($val as $name => $number)
                 {
                 $DBitems = $this->GetSelect("SELECT count(*) as cnt FROM Inventory WHERE (Type='item' AND Item='".$name."')");
                 //echo "Result:" . $name . " Count:" . $DBitems[0]["cnt"] . "\n";
                 if ($DBitems[0]["cnt"] > 0)
                    { // item already in DB, need update
                      //echo "Inv: Update $number : $name \n";
                      $res = ($this->GetSelect("UPDATE Inventory SET Number = '".$number."' WHERE Type='item' AND Item='".$name."'"));
                    }
                    else
                    {
                      //echo "Inv: insert $number : $name \n";
                     $this->ExecQuery('insert into Inventory values ( "item", "'.$name.'", "-", "-", "' . $number . '")');
                    }
                 }// end for each
              } // end if.

        }
      } // if not array
    }

    // ==========================================================================
   // ========================= nice time. Facebook style.
function nicetime($date)
{
    if(empty($date)) {
        return "No date provided";
    }
   
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
//    $unix_date       = strtotime($date);  // Do not provide real date, but unix timestamp
    $unix_date       = $date;
   
       // check validity of date
    if(empty($unix_date)) {   
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = "ago";
       
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
   
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
   
    if($difference != 1) {
        $periods[$j].= "s";
    }
   
    return "$difference $periods[$j] {$tense}";
}

// a multidimensional array in_array 
    function in_multiarray($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        while($bottom <= $top)
        {
            if($array[$bottom] == $elem)
                return true;
            else 
                if(is_array($array[$bottom]))
                    if($this->in_multiarray($elem, ($array[$bottom])))
                        return true;
                    
            $bottom++;
        }        
        return false;
    }

}

?>
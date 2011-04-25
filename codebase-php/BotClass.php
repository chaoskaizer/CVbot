<?php

class Bot {
    var $zyAuthHash;  //fortunes
    var $flashRevision;
    var $zySnuid;
    var $zyUid;
    var $zySnid;
    var $zySig;
    var $zySig2;

    var $sequense;
    var $usern;

    var $pm;
    var $ld;

    var $level;
    var $gold;
    var $cash;
    var $xp;
    var $energy;
    var $energyMax;

    var $fobjects;
    var $neighbors;

    var $error_msg;
    var $hooks;

    var $traymsg;
    var $xmlsOb;
    var $firstrun;

     var $dooberCoins;
     var $dooberXps;
     var $dooberEnergys;
     var $dooberItems;
     var $dooberItem;
     var $CheckDoober;

     var $xmlConfig;
     var $streakBonusCheck; // added by 12Christiaan to be switch on / off streakBonus
                            // See function streakBonusCheck


    // ==========================================================================
    function Bot() {
        
    }

    // ==========================================================================
    function Init() {
        if ($this->GetParamByName("Time2Log")==1){
            $this->Time2Log=1;
        }else{
            $this->Time2Log=0;
        }

        $this->CheckDoober = FALSE;

        date_default_timezone_set($this->GetParamByName("sTimeZone"));
        
        $this->SendMsg('Bot Init 1');
        define('AMFPHP_BASE', 'amfphp/core/');
        require_once(AMFPHP_BASE . "shared/util/CharsetHandler.php");
        require_once(AMFPHP_BASE . "amf/util/AMFObject.php");
        require_once(AMFPHP_BASE . "shared/util/CompatPhp5.php");
        require_once(AMFPHP_BASE . "shared/util/MessageBody.php");
        require_once(AMFPHP_BASE . "shared/app/Constants.php");
        require_once(AMFPHP_BASE . "shared/app/Globals.php");
        require_once(AMFPHP_BASE . "amf/io/AMFDeserializer.php");
        require_once(AMFPHP_BASE . "amf/io/AMFSerializer.php");

        global $headers_file, $amfbin_file;
        if ((file_exists($amfbin_file) == false) or (file_exists($headers_file) == false)) {
            $this->SendMsg('Cannot find MainRequest.bin or Headers.bin');
            $this->SendMsg('If running on windows 7, please run the bot as administrator');
            exit;
        }

        try {
            $f = fopen($amfbin_file, 'r');
            $size = filesize($amfbin_file);
            $amf = new AMFObject(fread($f, $size));
            fclose($f);
        } catch (Exception $e) {
            $this->SendMsg($e->getMessage());
            exit;
        }

        $deserializer = new AMFDeserializer($amf->rawData);
        $deserializer->deserialize($amf);

        $bod = new MessageBody();
        $bod = $amf->_bodys[0];

        $this->usern = trim($bod->_value[1][0]['params'][0]);
        $this->zyAuthHash=trim($bod->_value['0']['zyAuthHash']); //fortunes
        $this->flashRevision = trim($bod->_value['0']['flashRevision']);

        if(isset($this->zySig2))
         {
                $this->SendMsg("zySig updated ");
                $this->zySig = $this->zySig2 ;
         }
         else
         {
                 $this->zySig=trim($bod->_value['0']['zySig']);
         }


        $this->zySnid=trim($bod->_value['0']['zySnid']);
        //$this->zyUid=trim($bod->_value['0']['zyUid']);                   2011-02-17 no longer in this file.
                //  zyUid = Z uid
        //$this->zySnuid=trim($bod->_value['0']['zySnuid']);               2011-02-17 no longer in this file.
              //   zySnuid used for DB = facebook id

        $fh = file($headers_file);
        $this->url = trim($fh[0]);
        unset($fh[0]);
        foreach ($fh as $value) {
            $pos = strpos($value, ':');
            $name = trim(substr($value, 0, $pos));
            $val = trim(substr($value, $pos + 1, strlen($value)));
            $headers[$name]=$val;
        }

        $this->host = $headers['Host'];
        $this->x_flash_version = $headers['x-flash-version'];
        $this->headers["Accept"] =$headers['Accept'];
        $this->headers["Accept-Language"] =$headers['Accept-Language'];
        $this->headers["Referer"] =$headers['Referer'];
        $this->headers["x-flash-version"] =$headers['x-flash-version'];
        $this->headers["Content-Type"] =$headers['Content-Type'];
        $this->headers["Accept-Encoding"] ="deflate"; 
        $this->headers["User-Agent"] =$headers['User-Agent'];
        $this->headers["Host"] =$headers['Host'];
        if(isset($headers['Connection'])){
            $this->headers["Connection"] =$headers['Connection'];    
        }
        if(isset($headers['Proxy-Connection'])){
            $this->headers["Proxy-Connection"] =$headers['Proxy-Connection'];
        }
        if(isset($headers['Pragma'])){
            $this->headers["Pragma"] =$headers['Pragma'];
        }
        if(isset($headers['Cache-Control'])){
            $this->headers["Cache-Control"] =$headers['Cache-Control'];
        }
        $this->headers["Cookie"] =$headers['Cookie'];
        
        $this->pm = new PluginManager();
        $this->pm->GetConfiguration();

    }

    // ==========================================================================
    function Init2() { // second phase of the init
                       // after the reload.

        $this->SendMsg('Bot Init 2');

        $this->ld = new LocalData();

        $this->ld->ConnectTo($this->zySnuid);
                
        global $headers_file, $amfbin_file;
        $this->firstrun=false;
        if ($amfbin_file != 'tmp_dir\\' . $this->zySnuid . '_amf.bin') {
            echo "Deleting old BIN files \n";
            if (file_exists('tmp_dir\\' . $this->zySnuid . '_amf.bin')) {
                unlink('tmp_dir\\' . $this->zySnuid . '_amf.bin');
                unlink('tmp_dir\\' . $this->zySnuid . '_headers.bin');
            }
            $this->firstrun=true;
            SendApi('BotApi.SetUserId=' . $this->zySnuid);
        }

        $this->xmlConfig = new xmlConfig();
        $ConfigArray = array();
        $ConfigArray = $this->GetAmfHeader();
        $ConfigArray["Cookie"] =$headers['Cookie'];
        $ConfigArray["Referer"] =$headers['Referer'];
        $ConfigArray["UserAgent"] =$headers['User-Agent'];
        $ConfigFile =  $this->zySnuid . '__Zy.xml';
        $this->xmlConfig->writeXml($ConfigFile, $ConfigArray);

    }

    // ==========================================================================
    function IncludeAllHooks() {
        foreach ($this->pm->plugins as $plugin) {
            if (file_exists($plugin['hooks'])) {
                include($plugin['hooks']);
            }
        }
    }

    // ==========================================================================
    function ShowReportMsgInTray() {
        if (count($this->statistics) > 0) {
            foreach ($this->statistics as $key => $value) {
                $this->traymsg[] = $key . " = " . sizeof($value);
            }
        }

        if (isset($this->traymsg)) {
            if (sizeof($this->traymsg) > 0) {
                $this->SendMsg("");

                foreach ($this->traymsg as $msg) {
                    $tmp = $tmp . $msg . "\n";
                    $this->SendMsg($msg);
                }
                if (strlen($tmp) > 0) {
                    SendApi('BotApi.ShowTrayMsg=' . $tmp);
                }
            }
        }
    }

    // ==========================================================================
    function AddHook($hook, $fname) {
        $this->hooks[$hook][] = $fname;
    }

    // ==========================================================================
    function Hook($hook) {
        if (isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $function) {
                if (function_exists($function)) {
                    call_user_func($function, $this);
                }
            }
        }
    }

    // ==========================================================================
    function GetAmfHeader() {
        $arr['zyAuthHash'] = $this->zyAuthHash; //fortunes
        $arr['zySnid'] = $this->zySnid;
        $arr['zySig'] = $this->zySig;
        //$arr['zyUid'] = $this->zyUid;                 // no longer needed 2011-2-17 Z update
        //$arr['zySnuid'] = $this->zySnuid;             // no longer needed 2011-2-17 Z update
        $arr['flashRevision'] = $this->flashRevision;
        global $Zy;
        $Zy = $arr;
        return $arr;

    }

    // ==========================================================================
    function ReloadConfig() {
        $this->SendMsg('Loading configuration.');

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = 1;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.initUser";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $this->usern;
        //$amf->_bodys[0]->_value[1][0]['params'][1] = '';
        // added for new neighbor info 2011-01-26
        $amf->_bodys[0]->_value[1][1]['sequence'] = 2;
        $amf->_bodys[0]->_value[1][1]['functionName'] = "UserService.initNeighbors";
        $amf->_bodys[0]->_value[1][1]['params'] = array();
        //

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);

        $amf = new AMFObject($x);
        $deserializer = new AMFDeserializer($amf->rawData);
        $deserializer->deserialize($amf);
        $bod = new MessageBody();
        $bod = $amf->_bodys[0];
        $this->cfg = $bod;
        $this->sequense = 2;

        $this->zyUid = $bod->_value['data'][0]['data']['userInfo']['id'];//addded by phreaker 2011-2-17 Z update
        $this->zySnuid =  $this->zyUid; // for now we use the Z id, not the facebook id.

        $this->level = $bod->_value['data'][0]['data']['userInfo']['player']['level'];
        $this->gold = $bod->_value['data'][0]['data']['userInfo']['player']['gold'];
        $this->cash = $bod->_value['data'][0]['data']['userInfo']['player']['cash'];
        $this->xp = $bod->_value['data'][0]['data']['userInfo']['player']['xp'];
        $this->energy = $bod->_value['data'][0]['data']['userInfo']['player']['energy'];
        $this->energyMax = $bod->_value['data'][0]['data']['userInfo']['player']['energyMax'];
        //$this->fobjects = $bod->_value['data'][0]['data']['userInfo']['world']['objects'];
        $this->neighbors = $bod->_value['data'][0]['data']['userInfo']['player']['neighbors'];
        //added for franchises
        $this->franchises = $bod->_value['data'][0]['data']['franchises'];
        //added for inventory.wishlist
        $this->Inventory = (array)$bod->_value['data'][0]['data']['userInfo']['player']['inventory'];
        //added for collection overview
        $this->Collection = (array)$bod->_value['data'][0]['data']['userInfo']['player']['collections'];
        $this->wishlist = (array)$bod->_value['data'][0]['data']['userInfo']['player']['wishlist'];
//        $EmptyFirst = "Y";
//        $this->ld->UpdateInventory($bod->_value['data'][0]['data']['userInfo']['player']['inventory'] , $EmptyFirst);
        // added for trains
        $this->waresMax = $bod->_value['data'][0]['data']['userInfo']['player']['commodities']['storage']['goods'];
        $this->train_arrive = $bod->_value['data'][0]['data']['userInfo']['player']['Orders']['order_train']['sent']['accepted']['i-1']['timeSent'];
        $this->train_mission = $bod->_value['data'][0]['data']['userInfo']['player']['Orders']['order_train']['sent']['accepted']['i-1']['trainItemName'];
        // added to count total storge
        $totalStorageGoods = 300;
        // added 2011-01-14 for 92% error : world has moved
        $this->fobjects = array();
        $this->fobjects1 = $bod->_value['data'][0]['data']['userInfo']['world']['objects'];
        $this->fobjects2 = $bod->_value['data'][0]['data']['world']['objects'];
        if(count($this->fobjects1) > 4) $this->fobjects = $this->fobjects1;
        if(count($this->fobjects2) > 4) $this->fobjects = $this->fobjects2;
        if(is_array($this->fobjects)){
           foreach ($this->fobjects as $fobj)
           {
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
					
                    if (array_key_exists($fobj['itemName'], $cv_storage_type))
                    {                     
                        $key = (string) $fobj['itemName'];
                        $totalStorageGoods = $totalStorageGoods + $cv_storage_type[$key];
                        
                    }
               }
           }
        }
        $this->MaxGoods =  $totalStorageGoods;
        //added for daily bonus. 27-12-2010
        $this->currentBonusDay = $bod->_value['data'][0]['data']['userInfo']['player']['currentBonusDay'];
        $this->previousBonusDay = $bod->_value['data'][0]['data']['userInfo']['player']['previousBonusDay'];
        $this->previousBonusTime = $bod->_value['data'][0]['data']['userInfo']['player']['previousBonusTime'];
        //added to accept Neighbors work.
        $this->order_visitor_help = $bod->_value['data'][0]['data']['userInfo']['player']['Orders']['order_visitor_help']['received']['pending'];
        // show some up2date information.
        $this->SendMsg("> Coins: $this->gold Goods: $this->waresMax/$this->MaxGoods Energy: $this->energy");


}
// ==============================================================================
    function harvest($obj) {
        $this->SendMsg('Harvest ' . $obj['itemName'] . ' '. $obj['contractName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y'] . " id:" .$obj["id"]);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2] = null;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        //add this line to results that does not give dooberitems via AMF, but streak bonus can be collected.
        $this->dooberItems +=2;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Harvest');
    }
// ==============================================================================
    function clearWithered($obj) {
        $this->SendMsg('Clear withered ' . $obj['contractName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y'] . " id:" .$obj["id"]);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "clearWithered";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2] = null;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        //add this line to results that does not give dooberitems via AMF, but streak bonus can be collected.
        $this->dooberItems +=2;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Clear withered');
    }
// ==============================================================================
    function BuyEnergy($item) {
        $this->SendMsg("Use Energy from inventory: $item");

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.buyEnergy";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $item;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Energy used from inventory');
    }
// ==============================================================================
    function addToWishlist($item) {
        $this->SendMsg("addToWishlist: $item");

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "CollectionsService.addToWishlist";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $item;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('addToWishlist');
    }
// ==============================================================================
    function AcceptBonus() {
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.collectDailyBonus";
        $amf->_bodys[0]->_value[1][0]['params'] = array();

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('DailyBonus');
    }
// ==============================================================================
    function collectRent($obj) {
        $this->SendMsg('Collect rent from' . $obj['itemName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y']);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Collect rent');
    }
// ==============================================================================
    function collectBB($obj, $silent = true) {
        if($silent)$this->SendMsg('Collect ' . $obj['itemName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y']);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = $this->xmlsOb->GetBuildingsProductCount($obj['itemName']);
        $amf->_bodys[0]->_value[1][0]['params'][2][1] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Collect business buildings');
    }
// ============================================================================== $silent = true  if($silent)
    function supplyBB($obj, $silent = true) {
        if($silent)$this->SendMsg('Supply ' . $obj['itemName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y']);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "openBusiness";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2]=Array();

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Supply');
    }
// ==============================================================================
    function processVisits($obj, $silent = true) {
        $extraVisits = $obj['lastSavedMax'] - $obj['visits'];
        if($silent)$this->SendMsg('Visiting ' . $obj['itemName'] . " $extraVisits times. ");
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.processVisitsBatch";
        $amf->_bodys[0]->_value[1][0]['params'][0][0] = $obj['id'];
        $amf->_bodys[0]->_value[1][0]['params'][1][0] = $extraVisits;  // the max amount of visitors.

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('processVisits');
    }
// ==============================================================================
    function onCollectDailyBonus($obj) {
        $this->SendMsg('Franchise Collect Daily Bonus ' . $obj['name'] );
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "FranchiseService.onCollectDailyBonus";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $obj['name'];

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Franchise Collect Daily Bonus');
    }
// ==============================================================================
    function sendShip($obj, $sname) {
        $this->SendMsg('Send ship ' . $sname . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y']);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "startContract";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][1]['state'] = 'planted';
        $amf->_bodys[0]->_value[1][0]['params'][1]['contractName'] = $sname;
        $amf->_bodys[0]->_value[1][0]['params'][1]['itemName'] = 'ship_boat';
        $amf->_bodys[0]->_value[1][0]['params'][1]['plantTime'] = microtime(1) * 1000;;
        $amf->_bodys[0]->_value[1][0]['params'][2] = null;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Send Ship');
    }
   // ==============================================================================
    function startContract($obj, $sname) {
        $this->SendMsg('Seed crop ' . $sname . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y']);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "startContract";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][1]['state'] = 'planted';
        $amf->_bodys[0]->_value[1][0]['params'][1]['contractName'] = $sname;
        $amf->_bodys[0]->_value[1][0]['params'][1]['itemName'] = 'plot_crop';
        $amf->_bodys[0]->_value[1][0]['params'][1]['plantTime'] = microtime(1) * 1000;;
        $amf->_bodys[0]->_value[1][0]['params'][2] = null;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Seed');
    }
   // ==============================================================================
   //  functionName  String  UserService.onRemoveFromInventory
    function RemoveFromInventory($item, $amount) {
        $this->SendMsg('Remove From Inventory: ' . $amount . " " . $item );

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.onRemoveFromInventory";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $item;
        $amf->_bodys[0]->_value[1][0]['params'][1] = $amount;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Remove From Inventory');
    }
   // ==============================================================================
    function redeemVisitorHelpAction($objs, $sname) {
        $this->SendMsg('Accept Neighbors help ' . $sname );

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $i = 0;
        foreach($objs as $obj)
         {

                $this->SendMsg('Accept Neighbors help ' . $sname . " obj=" . $obj);

        $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][$i]['functionName'] = "VisitorService.redeemVisitorHelpAction";
        $amf->_bodys[0]->_value[1][$i]['params'][0] = $sname;
        $amf->_bodys[0]->_value[1][$i]['params'][1] = $obj;

        $i++;
         }

        //add this line to results that does not give dooberitems via AMF, but streak bonus can be collected.
        //$this->dooberItems +=2;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Accept Neighbors help');
    }
   // ==============================================================================
    function updateEnergy() {
//        $this->SendMsg('updateEnergy');
//
//        unset($this->error_msg);
//        $amf = new AMFObject("");
//        $amf->_bodys[0] = new MessageBody();
//        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
//
//        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
//        $amf->_bodys[0]->responseURI = '';
//        $amf->_bodys[0]->_value[2] = 0;
//
//        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
//        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.updateEnergy";
//        $amf->_bodys[0]->_value[1][0]['params'] = Array();
//        $amf->_bodys[0]->_value[2] = 0;
//
//        $serializer = new AMFSerializer();
//        $result = $serializer->serialize($amf);
//        $x = $this->SendRequest($result);
//        $this->Add2Report('updateEnergy');
    }
   // ==============================================================================
    function orig_streakBonus($i,$cnt) {
        $bonus = round(($this->level * 1.5 * 55)-1);
        $this->SendMsg('streakBonus '.$i .'/'.$cnt .': '. $bonus);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][0]['params'][0]['amount'] = 9; //$bonus; //9;
        $amf->_bodys[0]->_value[1][0]['params'][0]['maxesReached'] = 1;
        $amf->_bodys[0]->_value[2] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('streakBonus');
    }
   // ==============================================================================
    function streakBonusCheck() {

    $showDooberInLog = true;  // Make this false to have less log
    if($showDooberInLog && is_array($this->dooberItem))
    { foreach($this->dooberItem as $item => $amount)   { $this->Add2Report('Bonus: ' . $item); }
      $this->dooberItem = array();
    }
    // check if we can get streakBonus
     $streakBonus = $this->GetParamByName("steakbonus");
    // streak bonus is now controled via the settings plugin.
    // $streakBonus = 1; // 0 = Do not collect streakBonus (off, in case Z patch this again)
                       // 1 = collect streakBonus every time (default)
                       // 2 - 31 = configurable
                       // 32 = the normal amount to collect maximum bonus.
     if($streakBonus == 0 ) return;
     if($this->dooberItems > $streakBonus)
       {
         $this->streakBonus3();
         $this->dooberItems = 0;
       }
      return;
     }
   // ==============================================================================
    function streakBonus2() { return; } // place holder to be backward compatible
    function streakBonus3() {
        $bonus = round(($this->level * 1.5 * 55)-1);
        $this->SendMsg('streakBonus3: '. $bonus . ' coins');
        $this->dooberItem['coin'] += $bonus;
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][0]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][0]['params'][0]['maxesReached'] = 10;
        $amf->_bodys[0]->_value[2] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('streakBonus3');
    }
   // ==============================================================================
    function setCityName($name) {
        $this->SendMsg('setCityName: '. $name );
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.setCityName";
        $amf->_bodys[0]->_value[1][0]['params'][0] = 'renameCity';
        $amf->_bodys[0]->_value[1][0]['params'][1] = $name;
        $amf->_bodys[0]->_value[2] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('setCityName');
    }
   // ======================= LotSite build franchise =========================
    function placeOrder($obj) {
        $this->SendMsg('Place Order: '. $obj['resourceType'] );
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "LotOrderService.placeOrder";
        $amf->_bodys[0]->_value[1][0]['params'][0]['constructionCount'] = 0;
        $amf->_bodys[0]->_value[1][0]['params'][0]['offsetY'] = null;
        $amf->_bodys[0]->_value[1][0]['params'][0]['senderID'] = $this->zyUid;  // that is me
        $amf->_bodys[0]->_value[1][0]['params'][0]['lotId']             = $obj['lotId'];
        $amf->_bodys[0]->_value[1][0]['params'][0]['recipientID']       = $obj['recipientID'];
        $amf->_bodys[0]->_value[1][0]['params'][0]['orderResourceName'] = $obj['orderResourceName'] ;
        $amf->_bodys[0]->_value[1][0]['params'][0]['resourceType']      = $obj['resourceType'];
        $amf->_bodys[0]->_value[1][0]['params'][0]['offsetX'] = null;

          //    constructionCount  Integer  0
          //    offsetY  Undefined
          //    senderID  String Reference  2222222222
          //    lotId  Integer  118
          //    recipientID  String  20022222222
          //    orderResourceName  String  Kees's Cinema
          //    resourceType  String  bus_movietheater
          //    offsetX  Undefined

        $amf->_bodys[0]->_value[2] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('setCityName');
    }
       // ==============================================================================
    function streakBonus($i,$cnt) {
        $bonus = round(($this->level * 1.5 * 55)-1);
        $bonus = round(($this->level * 1.5 )-1);
        //$bonus = 126;
        $bonusM = (8*$bonus);
        $this->SendMsg('streakBonus (Multi) '.$i .'/'.$cnt .': 8*'. $bonus . ' = '.$bonusM);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][0]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][0]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][1]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][1]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][1]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][1]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][2]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][2]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][2]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][2]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][3]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][3]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][3]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][3]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][4]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][4]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][4]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][4]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][5]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][5]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][5]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][5]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][6]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][6]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][6]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][6]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[1][7]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][7]['functionName'] = "UserService.streakBonus";
        $amf->_bodys[0]->_value[1][7]['params'][0]['amount'] = $bonus; //9;
        $amf->_bodys[0]->_value[1][7]['params'][0]['maxesReached'] = 1;

        $amf->_bodys[0]->_value[2] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('streakBonus multi');
        $total = $cnt * $bonusM;
        if(($i+1) == $cnt) { $this->SendMsg('streakBonus totaly collected '. $total);}
    }
    // ------------------------------------------------------------------------------
      function TradeCollection($collectionType, $i, $cnt) {
          unset($this->error_msg);
          $amf = new AMFObject("");
          $amf->_bodys[0] = new MessageBody();
          $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
          $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
          $amf->_bodys[0]->responseURI = '';
          $amf->_bodys[0]->_value[2] = 0;
          $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
          $amf->_bodys[0]->_value[1][0]['functionName'] = "CollectionsService.onTradeIn";
          $amf->_bodys[0]->_value[1][0]['params'][0] = $collectionType;
          $serializer = new AMFSerializer();
          $result = $serializer->serialize($amf);
          $x = $this->SendRequest($result);
          $this->Add2Report('Trade in collections');
          $this->SendMsg('Trade in ' . $collectionType . ' : ' . $i . '/' . $cnt);
      }
    // ------------------------------------------------------------------------------

    function GetParamByName($name) {
        $fl = file('options.txt');
        foreach ($fl as $line) {
            if (strpos($line, $name) !== false) {
                $pos = strpos($line, '=');
                $val = trim(substr($line, $pos + 1, strlen($line)));
            }
        }
        return $val;
    }

    // ==============================================================================
    function SendRequest($data) {
        $response = $this->Request($data);
        $this->CheckServerError($response);
        return $response;
    }

    // ------------------------------------------------------------------------------
    function Disconnect() {
        fclose($this->s);
    }

    // ------------------------------------------------------------------------------
    function Connect() {
        if ($this->GetParamByName("iProxyUse") == 1) {
            $this->s = fsockopen($this->GetParamByName("sProxyHost"), $this->GetParamByName("iProxyPort"));
        }
        else{
            //$this->SendMsg("headers:". $this->headers["Host"]);
            $this->s = fsockopen($this->headers["Host"], 80);
        }

        if (!$this->s) {
            $this->SendMsg("Error: Can't connect to game server");
            exit;
        }
    }

    // ------------------------------------------------------------------------------
    function fullread($sd, $len) {
        $ret = '';
        $read = 0;

        while ($read < $len && ($buf = fread($sd, $len - $read))) {
            $read += strlen($buf);
            $ret .= $buf;
        }

        return $ret;
    }

    // ------------------------------------------------------------------------------
    function Request($data) {
        $this->Connect();
        if (strpos($this->url, 'http://') === false)
            $query = "POST http://" . $this->host . $this->url . " HTTP/1.1\r\n";
        else
            $query = "POST $this->url HTTP/1.1\r\n";

        //$query = "POST http://" . $this->host . $this->url . " HTTP/1.1\r\n";

        if ($this->GetParamByName("iProxyUse") == 1) {
            $authorization = base64_encode(trim($this->GetParamByName("sProxyUser")) . ':' . trim($this->GetParamByName("sProxyPass")));
            $this->headers["Proxy-Authorization"]= "Basic $authorization";
        }

        foreach ($this->headers as $key=>$value) {
            $query .= $key . ": ".$value . "\r\n";
        }

        $query .= "Content-Length: " . strlen($data) . "\r\n\r\n";
        $query .= $data;

        stream_set_blocking($this->s, 0);

        fwrite($this->s, $query);
        $answer = '';

        $max_tick = 500; // wait max 50 seconds for data before we repeat request
        $cur_tick = 0;
        $is_bad = false;

        while (!strlen($answer)) {
            $answer .= $this->fullread($this->s, 1024);

            if (!strlen($answer)) {
                usleep(100000);
                $cur_tick++;

                if ($cur_tick > $max_tick) {
                    $is_bad = true;
                    break;
                }
            }
        }

        if ($is_bad) {
            $this->SendMsg("Repeat request -no answer-");
            $this->Disconnect();
            $this->Connect();
            return $this->SendRequest($data);
        }

        if (strpos($answer, '404 Not Found') !== false) {
            $this->Disconnect();
            $this->Connect();
            $this->SendMsg("Repeat request -404-");
            return $this->SendRequest($data);
        }

        if (strripos($answer, '500 Internal Server Error') !== false) {
            $this->SendMsg("ERROR: Internal Server Error");
            $this->Disconnect();
            return 0;
        }

        if (strripos($answer, '502 Bad Gateway') !== false) {
            $this->SendMsg("ERROR: 502 Bad Gateway");
            return 0;
        }

        preg_match('/Content-length:[\s]([0-9]*)[\s]/si', $answer, $match);
        $pos = strpos($answer, "\r\n\r\n");
        if ($pos !== false) {
            $answer = substr($answer, $pos + 4, strlen($answer));
        } else
            $answer = null;

        while (true) {
            if ($Loaded_Size == 0) {
                $Loaded_Size = @$match[1];
            }
            if ($Loaded_Size == 0) {
                echo 'Debug: Read ' . strlen($answer) . ' wanted: ' . @$match[1] . ' Answer: ' . $answer . "\n";
                $this->SendMsg("ERROR: Lost connection to server");
                $this->SendRequest($data);
            }

            $answer .= $this->fullread($this->s, $Loaded_Size);

            if (strlen($answer) >= $match[1])
                break;
            usleep(100000);
        }

        if ($Loaded_Size == $match[1]) {
            $Loaded_Size = 0;
        }
        return $answer;

        $this->Disconnect();
    }

    // ==============================================================================
    function CheckServerError($resp) {
        $tmp = new AMFObject($resp);
        $deserializer = new AMFDeserializer($tmp->rawData);
        $deserializer->deserialize($tmp);

        if (isset($tmp->_bodys[0]->_value['data'][0]['data']['result'])) { // added by 12Christiaan      result  String  failure
            if($tmp->_bodys[0]->_value['data'][0]['data']['result'] == "failure") {$this->error_msg = "Unknown error";}
            if($tmp->_bodys[0]->_value['data'][0]['data']['result'] == "batchFailure") {$this->error_msg = "Unknown batchFailure error";}
        }
        if (isset($tmp->_bodys["0"]->_value["faultCode"])) { //added 2010-12-19 by 12christiaan found by Altizar
            $this->error_msg = $tmp->_bodys["0"]->_value["faultCode"];
        }
        if (isset($tmp->_bodys[0]->_value['data'][0]['errorData'])) {
            $this->error_msg = $tmp->_bodys[0]->_value['data'][0]['errorData'];
        }
        if (isset($tmp->_bodys[0]->_value['errorData'])) {
            $this->error_msg = $tmp->_bodys[0]->_value['errorData'];
        }

        if (isset($this->error_msg)) {
            $this->SendMsg('ERROR: ' . $this->error_msg);
            // changed if by 12christiaan to detect AMFPHP_RUNTIME_ERROR  found by Altizar
            if (($this->error_msg == 'AMFPHP_RUNTIME_ERROR') ||($this->error_msg == "User not found") || (strpos($this->error_msg, "There is a new version of the game released")!==false)) {

                $this->SendMsg('The session expired, the bot will restart in 10 seconds');
                sleep(10);
                global $headers_file, $amfbin_file;
                if (file_exists($headers_file)) {
                    unlink($headers_file);
                }
                if (file_exists($amfbin_file)) {
                    unlink($amfbin_file);
                }
                if (file_exists('tmp_dir\CurrentRevision.txt')) {
                    unlink('tmp_dir\CurrentRevision.txt');
                }
                $this->RestartBot();
            } 
        }
        // added by 12Christiaan to prevent  AMFPHP_RUNTIME_ERROR
        if (isset($tmp->_bodys[0]->_value['zySig']['zySig'])) {
           if($this->zySig != $tmp->_bodys[0]->_value['zySig']['zySig'])
             {
                //$this->SendMsg("zySig updated **************************************");
                $this->zySig2 = $tmp->_bodys[0]->_value['zySig']['zySig'];

                $file = 'tmp_dir/'.$this->zySnuid.'_amf.bin';

                $fl = fopen($file, 'r');
                $content = fread($fl, filesize($file));
                fclose($fl);

                $content =  str_replace($this->zySig, $this->zySig2, $content);

                $fl = fopen($file, 'w');
                fwrite($fl, $content);
                fclose($fl);

                $this->zySig = $this->zySig2;
             }

        }
        // added by 12Christiaan for streak bonus.              Array
        $this->CheckDoober = true;
        if(is_array($tmp->_bodys[0]->_value['data'][0]['data']) && $this->CheckDoober)
        {
          if(isset($tmp->_bodys[0]->_value['data'][0]['data']['doobers']))
           { //We got a Reward.
             foreach($tmp->_bodys[0]->_value['data'][0]['data']['doobers'] as $doober)
               {
                 //$this->SendMsg('Doober2: ' . $doober[0] . ' = ' . $doober[1] . ' Total Doob:' . $this->dooberItems);
                 if($doober[0] == "coin")       { $this->dooberItems++;  $this->dooberItem[$doober[0]] += $doober[1]; }
                 if($doober[0] == "xp")         { $this->dooberItems++;  $this->dooberItem[$doober[0]] += $doober[1];     }
                 if($doober[0] == "collectable"){ $this->dooberItems++;  $this->dooberItem[$doober[1]] += 1;}
                 if($doober[0] == "energy")     { $this->dooberItems++;  $this->dooberItem[$doober[0]] += $doober[1]; }
               }
           }
        $this->streakBonusCheck();
        }// end CheckDoober

    }

    // ==============================================================================
    function RestartBot() {
        SendApi('BotApi.RUNNOW');
        exit;
    }

    // ==============================================================================
    function GetSequense() {
        $this->sequense += 1;
        return $this->sequense;
    }

    // ==============================================================================
    function Add2Report($name) {
        if (!isset($this->error_msg)) {
            $this->statistics[$name][] = 1;
            $this->lastAction = $name;
        }
    }
       // ==============================================================================
    function DoWork() {
        $this->SendMsg('DoWork');
        $this->Hook('before_work');

        $this->ReloadConfig();
        $this->SendMsg('Begin saving user inventory to local db file');
        $this->ld->SaveInventory2db($this);

        $this->Hook('fill_goods_before');
        $this->Hook('before_help_neighbors');
        $this->Hook('help_neighbors');
        $this->Hook('after_help_neighbors');

        $this->Hook('before_harvest_crops');
        $this->Hook('harvest_crops');
        $this->Hook('after_harvest_crops');
        
        $this->Hook('before_seed_crops');
        $this->Hook('seed_crops');
        $this->Hook('after_seed_crops');

        $this->Hook('before_buildings_work');
        $this->Hook('buildings_work');
        $this->Hook('after_buildings_work');

        $this->Hook('before_other_work');
        $this->Hook('other_work');
        $this->Hook('after_other_work');
        $this->Hook('fill_goods_after');

        //Please do not spent any energy in this hook
        $this->Hook('after_work'); //GameInfo plugin

        $this->ReloadConfig();
        $this->SendMsg('Begin saving user profile to local db file');
        $this->ld->SaveProfile2db($this);
        $this->SendMsg('End saving user profile to local db file');
        $this->Hook('download_images');
    }


    // ==============================================================================
    function GetCurrentRevision() {
        if (file_exists("tmp_dir\CurrentRevision.txt")) {
            $fl = fopen('tmp_dir\CurrentRevision.txt', 'r');
            $currver = fread($fl, filesize("tmp_dir\CurrentRevision.txt"));
            fclose($fl);
            return $currver;
        } else {
            return false;
        }
    }

    // ==============================================================================
    function SetCurrentRevision($ver) {
        if (file_exists("tmp_dir\CurrentRevision.txt")) {
            unlink("tmp_dir\CurrentRevision.txt");
        }

        $fl = fopen('tmp_dir\CurrentRevision.txt', 'w');
        fwrite($fl, $ver);
        fclose($fl);
    }
     // ==============================================================================
    function UserInfo() {
        $this->SendMsg('');
        //$tmp = explode(':', $this->userId);
        $this->SendMsg('Zynga id=       ' . $this->zySnuid);
        $this->SendMsg('worldName=' . $this->usern);
        $this->SendMsg('level=    ' . $this->level);
        //$this->SendMsg('wood=' . $this->wood);
        $this->SendMsg('xp=       ' . $this->xp);
        $this->SendMsg('cash=     ' . $this->cash);
        $this->SendMsg('coins=    ' . $this->gold);
        $this->SendMsg('flashRevision=' . $this->flashRevision);
        $this->SendMsg('host=     ' . $this->host);
        $this->SendMsg('x_flash_version=' . $this->x_flash_version);
        $this->SendMsg('energy=   ' . $this->energy);
        $this->SendMsg('energyMax=' . $this->energyMax);
        $this->SendMsg('GoodsMax= ' . $this->MaxGoods);
        $this->SendMsg('Goods=    ' . $this->waresMax);

        $this->SendMsg('');
    }

    // ==============================================================================
    function SendMsg($msg) {
        if ($this->Time2Log<>1){
            echo $msg . "\n";
        }else{
            echo date("H:i:s"). " " .$msg . "\n";
        }
    }

    // ==============================================================================
    function DownloadFile($url, $file_name) {
        $this->SendMsg('Downloading ' . $file_name);
        
        if ($this->GetParamByName("iProxyUse") == 1) {
            GetFile($url, "tmp_dir\\" . $file_name, 1, $this->GetParamByName("sProxyHost"), $this->GetParamByName("iProxyPort"),$this->GetParamByName("sProxyUser"),$this->GetParamByName("sProxyPass"));
        }
        else{
            GetFile($url, "tmp_dir\\" . $file_name, 0, '', '','','');
        }
    }

    // ==============================================================================
    function GetTrueXmlUrl($file_name) {
        $url_from = "http://assets.cityville.zynga.com/" . $this->flashRevision ."/".  $file_name;
        return $url_from;
    }

    // ==============================================================================
    function UpdateXML() {
        $this->SendMsg('UpdateXML');
        if (strcmp($this->flashRevision, $this->GetCurrentRevision()) != 0) {
            //$this->DownloadFile($this->GetTrueXmlUrl('questSettings.xml'), 'questSettings.xml');
            //$this->DownloadFile($this->GetTrueXmlUrl('en.xml'), 'en.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('gameSettings.xml'), 'gameSettings.xml');
            //added to save history versions.
            $this->DownloadFile($this->GetTrueXmlUrl('gameSettings.xml'), $this->flashRevision.'-gameSettings.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('en_US.xml'), 'en_US.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('it_IT.xml'), 'it_IT.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('de_DE.xml'), 'de_DE.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('fr_FR.xml'), 'fr_FR.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('es_ES.xml'), 'es_ES.xml');
            $this->DownloadFile($this->GetTrueXmlUrl('effectsConfig.xml'), 'effectsConfig.xml');

            if (file_exists('tmp_dir\fnames.txt')) {
                unlink('tmp_dir\fnames.txt');
            }
            $this->SetCurrentRevision($this->flashRevision);
        }
        if (!file_exists('tmp_dir\gameSettings.xml')) {
            $this->SendMsg('Cannot find file gameSettings.xml');
            $this->SendMsg('Exit');
            exit;
        }
        $this->xmlsOb = new xmlsOb();
        $this->xmlsOb->GenerateFnames();
    }

//==============================================================================
//== Function for Neighbor Plugin
//== Function to load Neighbors world
//==============================================================================
    function NLoadWorld($nuid, $name) {
        if($nuid == $this->zyUid) {$this->SendMsg('Contineu ' ); return "";}
        //$this->SendMsg('Load world id: '.$nuid . '  name: ' . $name);
        //$this->statistics['load world'][]=1;
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['functionName'] = "VisitorService.initialVisit";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "neighborVisit";
        $amf->_bodys[0]->_value[1][0]['params'][1]=Array();
        $amf->_bodys[0]->_value[1][0]['params'][1]['recipientId'] = $nuid;  // who to visit
        $amf->_bodys[0]->_value[1][0]['params'][1]['senderId'] = $this->zyUid;  // me $this->zyUid

        $amf->_bodys[0]->_value[1][1]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][1]['functionName'] = "WorldService.loadWorld";
        $amf->_bodys[0]->_value[1][1]['params'][0] = $nuid;  // who to visit

        $amf->_bodys[0]->_value[1][2]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][2]['params'][0] = $nuid;
        $amf->_bodys[0]->_value[1][2]['functionName'] = "MissionService.getRandomMission";

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        //$this->Add2Report('load world');

        return $x;
    }
//==============================================================================
//== Function for Neighbor Plugin
//== Function to do work at the neighbor world.
//==============================================================================
    function BlessNeighbor(&$fobj, $hostID) {
        $msg = 'Bless neighbor ';
        if (array_key_exists('className', $fobj))    {$msg .= $fobj['className'] .' ';}
        if (array_key_exists('itemName', $fobj))     {$msg .= $fobj['itemName'] .' ' ; }
        if (array_key_exists('contractName', $fobj)) {$msg .= $fobj['contractName'] .' '; }
        if (array_key_exists('targetBuildingName', $fobj)) {$msg .= $fobj['targetBuildingName'] .' '; }
        $this->SendMsg( $msg );
        //$this->StreakBonus();

        if($fobj['className'] == "Residence") $action = "residenceCollectRent";
        if($fobj['className'] == "Business")  $action = "businessSendTour";
        if($fobj['className'] == "Plot" && $fobj['state'] == "grown")  $action = "plotHarvest";
        if($fobj['className'] == "Plot" && $fobj['state'] == "planted")  $action = "plotWater";
        if($fobj['className'] == "Plot" && $fobj['state'] == "withered")  $action = "plotWater";
        if($fobj['className'] == "ConstructionSite")  $action = "constructionSiteConstruct";
        if($fobj['className'] == "Wilderness")  $action = "wildernessClear";

        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[1][0]['functionName'] = "VisitorService.help";
        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['params'][0] = "visitorHelp";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $action ;  
        $amf->_bodys[0]->_value[1][0]['params'][2]=Array();
        $amf->_bodys[0]->_value[1][0]['params'][2]['helpTargets'] = Array();
        $amf->_bodys[0]->_value[1][0]['params'][2]['helpTargets'][0] = $fobj['id'];
        $amf->_bodys[0]->_value[1][0]['params'][2]['orderState']     = "pending";
        $amf->_bodys[0]->_value[1][0]['params'][2]['orderType']      = "order_visitor_help";
        $amf->_bodys[0]->_value[1][0]['params'][2]['recipientID']    = $fobj['VisitorId']; //['itemOwner']; // to who
        $amf->_bodys[0]->_value[1][0]['params'][2]['senderID']       = $this->zyUid;       // me  $this->zyUid
        $amf->_bodys[0]->_value[1][0]['params'][2]['status']         = "";
        $amf->_bodys[0]->_value[1][0]['params'][2]['timeSent']       = microtime(1) * 1000;
        $amf->_bodys[0]->_value[1][0]['params'][2]['transmissionStatus']    = "send";
        $amf->_bodys[0]->_value[2] = 0;

        //add this line to results that does not give dooberitems via AMF, but streak bonus can be collected.
        $this->dooberItems +=2;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        $this->Add2Report('Bless neighbor ' . $fobj['itemName']);
    }

//==============================================================================
//== Function for functionName  UserService.streamPublish
//== Function to publish to facebook wall.
//==============================================================================
    function streamPublish($feed) {
        $this->SendMsg('streamPublish' . $feed );

        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['params'][0] = $nuid;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.streamPublish";
        $amf->_bodys[0]->_value[1][0]['params'][0] = $feed; // example: energy_feed  wishlist_request qm_holiday_snow_snowball
        $amf->_bodys[0]->_value[1][0]['params'][1] = (object) "";
        $amf->_bodys[0]->_value[1][0]['params'][2] = $this->zyUid;  // me $this->zyUid

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        $this->Add2Report('Publish Requests');

        return $x;
    }
//==============================================================================
//== Function for Neighbor Plugin
//== Function to load Neighbors world in details (neighbors)
//==============================================================================
    function getRandomMission($nuid) {
        //$this->SendMsg('Still looking to find energy for you.... ' );
        //$this->statistics['load world'][]=1;
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['params'][0] = $nuid;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "MissionService.getRandomMission";

        //$amf->_bodys[0]->_value[1][1]['sequence'] = $this->GetSequense();
        //$amf->_bodys[0]->_value[1][1]['functionName'] = "UserService.updateEnergy";
        //$amf->_bodys[0]->_value[1][1]['params'] = array();

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        //$this->Add2Report('load world');

        return $x;
    }
//==============================================================================
//== Function for SpecialItem              by fortunes
//== Function to Accept Orders for empty Lot
//==============================================================================
     function SpecialItem($resourceType,$lotID,$name){            
          $this->SendMsg('SpecialItem starting...');
          unset($this->error_msg);
          $amf = new AMFObject("");
          $amf->_bodys[0] = new MessageBody();
          $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
          $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
          $amf->_bodys[0]->responseURI = '';
          $amf->_bodys[0]->_value[2] = 0;
          $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
          $amf->_bodys[0]->_value[1][0]['functionName'] = "LotOrderService.acceptOrder";
          $amf->_bodys[0]->_value[1][0]['params'][0]['offsetX'] = '0';
          $amf->_bodys[0]->_value[1][0]['params'][0]['offsetY'] = '0';         
          $amf->_bodys[0]->_value[1][0]['params'][0]['resourceType'] = $resourceType;
          $amf->_bodys[0]->_value[1][0]['params'][0]['senderID'] = -1; //$this->zyUid;
          $amf->_bodys[0]->_value[1][0]['params'][0]['recipientID'] = $this->zyUid;         
          $amf->_bodys[0]->_value[1][0]['params'][0]['orderResouceName'] = $name;
          $amf->_bodys[0]->_value[1][0]['params'][0]['lotId'] = $lotID;
          $amf->_bodys[0]->_value[1][0]['params'][0]['constructionCount'] = '0';
          $serializer = new AMFSerializer();
          $result = $serializer->serialize($amf);
          $x = $this->SendRequest($result);
          $aa = array('bus_','res_','mun_','_'); 
          $item = str_replace($aa," ",$resourceType);
          $this->SendMsg ("place special item ".$item);
          $this->Add2Report('SpecialItem');    
    } 
    
//  =========================================================
//  |             Map + Tree Function by p627               |
//  =========================================================
    function clearWilderness($obj) {
        $this->SendMsg('Cut Tree ' . $obj['contractName'] . " x=" . $obj['position']['x'] . " y=" . $obj['position']['y'] . " id:" .$obj["id"]);

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "clear";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2] = null;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Cut Tree');
    }

    // ==============================================================================
    function collectBusiness($obj, $silent = true) {
        if($silent)$this->SendMsg('Collect business building success');

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = $this->xmlsOb->GetBuildingsProductCount($obj['itemName']);
        $amf->_bodys[0]->_value[1][0]['params'][2][1] = 0;

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('Collect business buildings');
    }

    // ==============================================================================
    function collectMu($obj) {
        $this->SendMsg('collect municipal building success');

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('collect municipal');
    }

    // ==============================================================================
    function collectLM($obj) {
        $this->SendMsg('collect landmark building success');

        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "harvest";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2][0] = '';

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        $this->Add2Report('collect landmark');
    }

    function TreeSell($Tree_array) { 
        unset($this->error_msg); 
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;
        $msg = "Tree : cut tree ";
        $id = 63000 ;
        $i = 0;
        foreach ($Tree_array as $obj){
            $tmp = explode("|", $obj);
            $id = trim($tmp[0]);
            $xx = trim($tmp[1]);
            $yy = trim($tmp[2]);
            $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense();
            $amf->_bodys[0]->_value[1][$i]['functionName'] = "WorldService.performAction";
            $amf->_bodys[0]->_value[1][$i]['params'][0] = "clear";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['direction'] = 0;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['className'] = "Wilderness";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['id'] = $id;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['itemName'] = "wilderness";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['tempId'] = "NaN";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['x'] = $xx;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['y'] = $yy;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['z'] = 0;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['component'] = "";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['deleted'] = "false";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['state'] = "static";
            $amf->_bodys[0]->_value[1][$i]['params'][2] = Array();
            $id++;
            $i++;
            $msg = $msg . "(" . $xx . "," . $yy . ") "; 
        }     
        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
//      $this->SendMsg($msg);
        $x = $this->SendRequest($result);
        $this->SendMsg ("Cut tree...");
        return $x;  
    }

//==============================================================================
//== Function for Sell Plugin
//== Function to Sell items from the world.
//==============================================================================
    function SellObject($obj) {
        $this->SendMsg('Plugin Sell is selling obj id: '.$obj['id']);
        //$this->statistics['load world'][]=1;
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['functionName'] = "WorldService.performAction";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "sell";
        $amf->_bodys[0]->_value[1][0]['params'][1] = $obj;
        $amf->_bodys[0]->_value[1][0]['params'][2] = Array();

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        //$this->Add2Report('load world');

        return $x;
    }
    // ------------------------------------------------------------------------------
    function NPostInit() {
        $this->SendMsg('PostInit');
        //SendMsg('PostInit');

        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/1';
        $amf->_bodys[0]->_value[2] = 0;

        $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
        $amf->_bodys[0]->_value[1][0]['functionName'] = "UserService.initUser";
        $amf->_bodys[0]->_value[1][0]['params'][0] = "name";   // $this->zyUid;       // me

        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);

        $x = $this->SendRequest($result);
        return $x;
    }
//==============================================================================
//== Function for Train Plugin
//== Function to Send train
   // ==============================================================================
    function sendTrain($mission, $goods) {
                    $this->SendMsg('Send Train');
                    unset($this->error_msg);
                    $amf = new AMFObject("");
                    $amf->_bodys[0] = new MessageBody();
                    $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

                    $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
                    $amf->_bodys[0]->responseURI = '';
                    $amf->_bodys[0]->_value[2] = 0;

                    $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();
                    $amf->_bodys[0]->_value[1][0]['functionName'] = "TrainService.placeInitialOrder";
                    $amf->_bodys[0]->_value[1][0]['params'][0]['recipientID'] = '-1';
                    $amf->_bodys[0]->_value[1][0]['params'][0]['amountFinal'] = 'false';
                    $amf->_bodys[0]->_value[1][0]['params'][0]['orderType'] = 'order_train';
                    $amf->_bodys[0]->_value[1][0]['params'][0]['amountProposed'] = $goods;
                    $amf->_bodys[0]->_value[1][0]['params'][0]['senderID'] = $this->zyUid;
                    $amf->_bodys[0]->_value[1][0]['params'][0]['timeSent'] = time();
                    $amf->_bodys[0]->_value[1][0]['params'][0]['trainItemName'] = $mission;
                    $amf->_bodys[0]->_value[1][0]['params'][0]['orderCommodity'] = 'goods';
                    $amf->_bodys[0]->_value[1][0]['params'][0]['orderAction'] = 'buy';

                    $serializer = new AMFSerializer();
                    $result = $serializer->serialize($amf);
                    $x = $this->SendRequest($result);
                    $this->Add2Report('TrainSend');
      //  }
    }
   // ==============================================================================
    function receiveTrain() {
      //  if($this->GetParamByName_train("receive") == 1)
      //  {
                unset($this->error_msg);
                $amf = new AMFObject("");
                $amf->_bodys[0] = new MessageBody();
                $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();

                $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
                $amf->_bodys[0]->responseURI = '';
                $amf->_bodys[0]->_value[2] = 0;

                $amf->_bodys[0]->_value[1][0]['sequence'] = $this->GetSequense();;
                $amf->_bodys[0]->_value[1][0]['functionName'] = "TrainService.completeAllSentOrders";

        //add this line to results that does not give dooberitems via AMF, but streak bonus can be collected.
        $this->dooberItems +=2;

                $serializer = new AMFSerializer();
                $result = $serializer->serialize($amf);
                $x = $this->SendRequest($result);
                $this->Add2Report('TrainReceive');

      //  }
    }
   // ==============================================================================
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
    // ==========================================================================
//  ================================================
//  ==           Coin2 Function by fortunes       ==
//  ================================================
    function CoinPlaceLot($coin_array) {
        unset($this->error_msg);
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '/';
        $amf->_bodys[0]->_value[2] = 0;
        $msg = "Coin : Place lot ";
        $id = 63000 ;
        $i = 0;
        foreach ($coin_array as $obj){
            $tmp = explode("|", $obj);
            $xx = trim($tmp[1]);
            $yy = trim($tmp[2]);           
            $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense() ;
            $amf->_bodys[0]->_value[1][$i]['functionName'] = "WorldService.performAction" ;
            $amf->_bodys[0]->_value[1][$i]['params'][0] = "place" ;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['itemName'] = "biz_lotsite_4x4" ; 
            $amf->_bodys[0]->_value[1][$i]['params'][1]['state'] = "static" ;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['tempId'] = "-1" ; 
            $amf->_bodys[0]->_value[1][$i]['params'][1]['components'] = "" ;        
            $amf->_bodys[0]->_value[1][$i]['params'][1]['id'] = $id ; 
            $amf->_bodys[0]->_value[1][$i]['params'][1]['direction'] = "1" ; 
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['x'] = $xx ;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['y'] = $yy ;        
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['z'] = "0" ;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['className'] = "LotSite" ; 
            $amf->_bodys[0]->_value[1][$i]['params'][1]['deleted'] = "false" ;                                       
            $amf->_bodys[0]->_value[1][$i]['params'][2] = Array() ;
            $amf->_bodys[0]->_value[1][$i]['params'][2][0]['mapOwner'] = $this->zyUid ; 
            $amf->_bodys[0]->_value[1][$i]['params'][2][0]['energyCost'] = "0" ; 
            $amf->_bodys[0]->_value[1][$i]['params'][2][0]['itemOwner'] = $this->zyUid ;
            $id++;
            $i++;
            $msg = $msg . "(" . $xx . "," . $yy . ") ";
        }                           
        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
//        $this->SendMsg($msg);
        $x = $this->SendRequest($result);
        return $x;
    }

     function CoinPlaceTower($coin_array){
          unset($this->error_msg) ;                    
          $amf = new AMFObject("") ;
          $amf->_bodys[0] = new MessageBody() ;
          $amf->_bodys[0]->_value[0] = $this->GetAmfHeader() ;
          $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch' ;
          $amf->_bodys[0]->responseURI = '' ;
          $amf->_bodys[0]->_value[2] = 0;
          $msg = "Coin : Place Tower " ;
          $i = 0;
          foreach ($coin_array as $obj){
              $tmp = explode("|", $obj);
              $id = trim($tmp[0]);
              $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense() ;
              $amf->_bodys[0]->_value[1][$i]['functionName'] = "LotOrderService.acceptOrder" ;
              $amf->_bodys[0]->_value[1][$i]['params'][0]['offsetX'] = "0" ;
              $amf->_bodys[0]->_value[1][$i]['params'][0]['offsetY'] = "0" ;         
              $amf->_bodys[0]->_value[1][$i]['params'][0]['resourceType'] = "lm_pearltower" ;
              $amf->_bodys[0]->_value[1][$i]['params'][0]['senderID'] = $this->zyUid ;//"31226733682" ;  
              $amf->_bodys[0]->_value[1][$i]['params'][0]['recipientID'] = $this->zyUid ;         
              $amf->_bodys[0]->_value[1][$i]['params'][0]['orderResouceName'] = "Pearl Tower" ;
              $amf->_bodys[0]->_value[1][$i]['params'][0]['lotId'] = $id ;
              $amf->_bodys[0]->_value[1][$i]['params'][0]['constructionCount'] = "0" ;
              $i++;
              $msg = $msg . "(" . $id . ") " ;
          }
          $serializer = new AMFSerializer() ;
          $result = $serializer->serialize($amf) ;
//          $this->SendMsg($msg) ;
          $x = $this->SendRequest($result) ;
          return $x;         
    }  

    function CoinSellTower($coin_array,$time) {
        unset($this->error_msg);   
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;
        $msg = "Coin : Sell Tower ";
        $i = 0;
        foreach ($coin_array as $obj){
            $tmp = explode("|", $obj);
            $id= trim($tmp[0]);
            $xx = trim($tmp[1]);
            $yy = trim($tmp[2]);
            $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense();
            $amf->_bodys[0]->_value[1][$i]['functionName'] = "WorldService.performAction";
            $amf->_bodys[0]->_value[1][$i]['params'][0] = "sell";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['direction'] = 1;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['className'] = "Landmark";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['id'] = $id;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['itemName'] = "lm_pearltower";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['tempId'] = "NaN";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['plantTime'] = $time;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['buildTime'] = $time;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['x'] = $xx;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['y'] = $yy;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['z'] = 0;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['component'] = "";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['deleted'] = "false";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['state'] = "planted";
            $amf->_bodys[0]->_value[1][$i]['params'][2] = Array();
            $i++;
            $msg = $msg . "(" . $xx . "," . $yy . ") "; 
        }     
        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
//        $this->SendMsg($msg);
        $x = $this->SendRequest($result);
        return $x;
    }
    
    function CoinSellLot($coin_array) {
        unset($this->error_msg);   
        $amf = new AMFObject("");
        $amf->_bodys[0] = new MessageBody();
        $amf->_bodys[0]->_value[0] = $this->GetAmfHeader();
        $amf->_bodys[0]->targetURI = 'BaseService.dispatchBatch';
        $amf->_bodys[0]->responseURI = '';
        $amf->_bodys[0]->_value[2] = 0;
        $i = 0;
        foreach ($coin_array as $obj){
            $tmp = explode("|", $obj);
            $id= trim($tmp[0]);
            $xx = trim($tmp[1]);
            $yy = trim($tmp[2]);
            $amf->_bodys[0]->_value[1][$i]['sequence'] = $this->GetSequense();
            $amf->_bodys[0]->_value[1][$i]['functionName'] = "WorldService.performAction";
            $amf->_bodys[0]->_value[1][$i]['params'][0] = "sell";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['direction'] = 1;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['className'] = "LotSite";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['id'] = $id;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['itemName'] = "biz_lotsite_4x4";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['tempId'] = -1;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['x'] = $xx;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['y'] = $yy;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['position']['z'] = 0;
            $amf->_bodys[0]->_value[1][$i]['params'][1]['component'] = "";
            $amf->_bodys[0]->_value[1][$i]['params'][1]['deleted'] = "false";
            $amf->_bodys[0]->_value[1][$i]['params'][2] = Array();
            $i++; 
        }     
        $serializer = new AMFSerializer();
        $result = $serializer->serialize($amf);
        $x = $this->SendRequest($result);
        return $x;
    }



}

?>
<?php
class xmlConfig
{
    function xmlConfig()
    {

    }

    function writeXml($file, $array)
    {
       // write XML config file
       try {
             $file = "Profiles/".$file;
             $config = new Zend_Config_Writer_Xml();
             $config->write($file, new Zend_Config($array));
              //echo 'Configuration successfully written to file.';
             $info = "OK";
              
           } catch (Exception $e)
           {
             $info =  $e->getMessage();
           }
      return $info;
    }

}
// ==============================================================================
class xmlsOb {

    var $gs;
    var $fnames;
    var $gsXML;
    var $flXML;
	var $size;


    // ==============================================================================
    function xmlsOb() {
        $this->gsXML = simplexml_load_file('tmp_dir\gameSettings.xml');
    }

    // ==============================================================================
    function GenerateFnames() {
        if (!file_exists('tmp_dir\fnames.txt')) {
            $fn = 'tmp_dir\gameSettings.xml';
            $fl = fopen($fn, 'r');
            $s = fread($fl, filesize($fn));
            fclose($fl);
            $xml = simplexml_load_string($s);

            foreach ($xml->items->item as $item) {
                if(($item['type']=='plot_contract') && ($item['buyable']=='true')){
                    $zname=(string)$item["name"];
                    $fname = substr($zname, 5, strlen($zname));
                    $fnames['crops'][$zname]['name'] = $zname;
                    $fnames['crops'][$zname]['fname'] = $fname;
                }
            }
            $fl = fopen('tmp_dir\fnames.txt', 'w');
            fwrite($fl, serialize($fnames));
            fclose($fl);
        } else {
            $fl = fopen('tmp_dir\fnames.txt', 'r');
            $this->fnames = unserialize(fread($fl, filesize('tmp_dir\fnames.txt')));
            fclose($fl);
        }
    }
	// ==============================================================================//
    function GenerateSize() {
        if (!file_exists('tmp_dir\itemsize.txt')) {
            $fn = 'tmp_dir\gameSettings.xml';
            $fl = fopen($fn, 'r');
            $s = fread($fl, filesize($fn));
            fclose($fl);
            $xml = simplexml_load_string($s);

            foreach ($xml->items->item as $item) {
                if(($item->sizeX) && ($item->sizeY)){
                    $size[(string)$item["name"]][x] = (string)$item->sizeX;
					$size[(string)$item["name"]][y] = (string)$item->sizeY;
			}
                    
            }
            $fl = fopen('tmp_dir\itemsize.txt', 'w');
            fwrite($fl, serialize($size));
            fclose($fl);
        } else {
            $fl = fopen('tmp_dir\itemsize.txt', 'r');
            $this->size = unserialize(fread($fl, filesize('tmp_dir\itemsize.txt')));
            fclose($fl);
        }
    }

// ==============================================================================//
    function GetBuildingsProductCount($name) {
        foreach ($this->gsXML->items->item as $item) {
            if ($item['name'] == $name){
                $res = (string)$item->commodityReq;
                break;
            }
        }
        return $res;
    }

} // end class

class transl {
    var $langXML;

// ==========================================================================
//    $langauge can be: en de it fr es     these are the offical supported lang
// ==========================================================================
function transl($langauge)
    {
     $this->langXML = $this->translXML($langauge);
    }

function translXML($langauge)
    {
        $XMLlang2 = array();
        $file = '';
      if($langauge == 'en'){ $file = 'tmp_dir\en_US.xml';       }
      if($langauge == 'de'){ $file = 'tmp_dir\de_DE.xml';       }
      if($langauge == 'es'){ $file = 'tmp_dir\es_ES.xml';       }
      if($langauge == 'fr'){ $file = 'tmp_dir\fr_FR.xml';       }
      if($langauge == 'it'){ $file = 'tmp_dir\it_IT.xml';       }
      if($file == '')      { $file = 'tmp_dir\en_US.xml'; } //langauge not found set to en

        $LangXML1 = $this->LangXMLload($file, $langauge);
        foreach ($LangXML1->pkg as $pakage)
        { $pkgName =  $pakage['name'];
          foreach($pakage->string as $strings ){ $XMLlang2[(string)$strings['key']]=  (string)$strings->original; }
        }

   return $XMLlang2;
}
// ==========================================================================
function LangXMLload($file, $code)
    {
      $temp = file_get_contents($file);  //&#xD;

      $trans=Array('&#xA0;' =>'  ',   '&#xA1;' =>'i','&#xA2;' =>'c','&#xA3;' =>'£','&#xA4;' =>'0','&#xA5;' =>'Y','&#xA6;' =>'|',
'&#xA7;' =>'S','&#xA8;' =>'~','&#xA9;' =>'(c)','&#xAA;' =>'2','&#xAB;' =>'<<','&#xAC;' =>'-','&#xAD;' =>'-','&#xAE;' =>'(r)',
'&#xAF;' =>'-','&#xB0;' =>'0','&#xB1;' =>'+-','&#xB2;' =>'2','&#xB3;' =>'3','&#xB4;' =>'" ','&#xB5;' =>' u','&#xB6;' =>' P',
'&#xB7;' =>'.','&#xB8;' =>' ','&#xB9;' =>'1','&#xBA;' =>'O','&#xBB;' =>'>>','&#xBC;' =>'1/4','&#xBD;' =>'1/2','&#xBE;' =>'3/4',
'&#xBF;' =>'?','&#xC0;' =>'A','&#xC1;' =>'A','&#xC2;' =>'A','&#xC3;' =>'A','&#xC4;' =>'A','&#xC5;' =>'A','&#xC6;' =>'AE',
'&#xC7;' =>'C','&#xC8;' =>'E','&#xC9;' =>'E','&#xCA;' =>'E','&#xCB;' =>'E','&#xCC;' =>'I','&#xCD;' =>'I','&#xCE;' =>'I',
'&#xCF;' =>'I','&#xD0;' =>'D','&#xD1;' =>'N','&#xD2;' =>'O','&#xD3;' =>'O','&#xD4;' =>'O','&#xD5;' =>'O','&#xD6;' =>'O',
'&#xD7;' =>'X','&#xD8;' =>'0','&#xD9;' =>'U','&#xDA;' =>'U','&#xDB;' =>'U','&#xDC;' =>'U','&#xDD;' =>'Y','&#xDE;' =>' ',
'&#xDF;' =>'B','&#xE0;' =>'a','&#xE1;' =>'a','&#xE2;' =>'a','&#xE3;' =>'a','&#xE4;' =>'a','&#xE5;' =>'a','&#xE6;' =>'ae',
'&#xE7;' =>'c','&#xE8;' =>'e','&#xE9;' =>'e','&#xEA;' =>'e','&#xEB;' =>'e','&#xEC;' =>'i','&#xED;' =>'i','&#xEE;' =>'i',
'&#xEF;' =>'i','&#xF0;' =>'o','&#xF1;' =>'n','&#xF2;' =>'o','&#xF3;' =>'o','&#xF4;' =>'o','&#xF5;' =>'o','&#xF6;' =>'o',
'&#xF7;' =>'÷ ','&#xF8;' =>'o','&#xF9;' =>'u','&#xFA;' =>'u','&#xFB;' =>'u','&#xFC;' =>'u','&#xFD;' =>'y','&#xFE;' =>'p','&#xFF;' =>'y', '&#xD;' => '');

   //$temp = html_entity_decode($temp, ENT_NOQUOTES, 'ISO-8859-1');
   //$temp = htmlspecialchars($temp);
   //$temp = htmlspecialchars_decode($temp, ENT_NOQUOTES);
   //$temp = htmlentities($temp, ENT_NOQUOTES, 'UTF-8');
      $trans=Array('&' =>'&amp;');
   $temp = strtr($temp,$trans);

   return simplexml_load_string($temp);

    }





}

?>
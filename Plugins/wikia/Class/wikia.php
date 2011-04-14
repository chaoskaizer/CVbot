<?php
class wikia
{
  var $XML;
  var $items; //  all item objects
  var $RMT;   //  Random Modifier Tables
  
  var $item;  //  Costom Item data
	var $menu;  //  Costom Menu
	var $RT;    //	Costom Random Modifier Table
  
	var $Form;  //  Show  Form Mode
  var $bug;   //  Show  Debug Mode
  var $IMAGE; //  Show  Image Mode
	function Init()
	{
    $this->debug('Init() - start');
    $state = $this->findfile('gameSettings.xml');
		if ($state)
    {
      $this->LoadXML($state);
      $this->Proc();
      $this->showMneu();
      $this->showItem();
    }
    $this->debug('Init() - end');
	}
  function LoadXML($path)
  {
    $this->debug('Load XML Object - start');
    $this->XML = simplexml_load_file($path,'SimpleXMLElement',LIBXML_NOCDATA);
    $this->items = $this->XML->items;
    $this->RMT = $this->XML->randomModifierTables;
    $this->CDATA = $this->XML->assetIndex[0];
    $this->LoadCDATA();
    $this->LoadRMT();
    $this->debug('Load XML Object - end');
  }
  function LoadCDATA()
  {
    $this->debug('LoadCDATA() - start');
    $CDATA = explode("\n",$this->CDATA);
    unset($this->CDATA);
    for($i=0;$i<count($CDATA);$i++)
    {
      $tmp = explode(':',$CDATA[$i]);
      $this->CDATA[$tmp[1]] = $tmp[0];
    }
    $this->debug('LoadCDATA() - end');
  }
	function LoadRMT()
	{
    $this->debug('LoadRMT() - start');  
		foreach($this->RMT->randomModifierTable as $RT)
		{
			$type = strval($RT->attributes()->type);
			$name = strval($RT->attributes()->name);
      if (empty($type)) $type = 'food';//strval($RT->attributes()->_zexp);
			foreach($RT->roll as $roll)
			{
				$per = strval($roll->attributes()->percent);
        $sum = 0;
				foreach($roll->$type as $kind)
				{
					$value = ($type=='item' || $type =='collectable')?'name':'amount';
          if ($value == 'amount') $sum += strval($kind->attributes()->$value);
          else $sum = strval($kind->attributes()->$value);
				}
        $num = count($this->CRT->$name->$type->per[$per]);
	      $this->debug('LoadRMT() - name '.$name.' type = '.$type  );  
        $this->CRT->$name->$type->per[$per][$num] = $sum;
			}
    }
    $this->debug('LoadRMT() - end');  
	}
	function Proc()
	{
    $this->debug('Load Menu & Item Object - start');
    
    foreach($this->items->item as $item)
    {
      $name = strval($item->attributes()->name);
      
      //  Load Attributes value
      foreach($item->attributes() as $attr => $value) 
      {
        $s_attr = strval($attr);
        $this->item[$name]->attr->$s_attr = strval($value);
      }
      
      //  Load Etc tag value
      foreach($item as $tag => $value)
      {
        $s_tag = strval($tag);
        $this->item[$name]->tag->$s_tag = strval($value);
      }
      
      //  Load Random Modifier Tables
      foreach($item->randomModifiers as $RMT)
      {
        foreach($RMT as $key)
        {
          $rt_type = strval($key->attributes()->type);
          $rt_tbn = strval($key->attributes()->tableName);
          $this->item[$name]->RMT->$rt_type = $rt_tbn;
        }
      }
      
      $type = $this->item[$name]->attr->type;
      
      //  Load Image      
      foreach($item->image as $img)   //  Use bad method, edit the method later
      {
        $img_Name = strval($img->attributes()->name);
        if ($type == "wilderness")
        {
          $url = strval($img->image->attributes()->url);
          $this->item[$name]->url = $url;
          break;
        }
        else if ($type == "ConstructionSite")
        {
          if ($img_Name == "stage_0")
          {
            $url = strval($img->attributes()->url);
            $this->item[$name]->url = $url;
            break;
          }
        }
        else if ($type == "coin" || $type == "food" || $type =="xp" || $type == "goods")
        {
          if ($img_Name == "initial" || $img_Name == "icon")
          {
            $url = strval($img->attributes()->url);
            $this->item[$name]->url = $url;
            break;
          }
        }
        else
        {
          if ($img_Name == "icon")
          {
            $url = strval($img->attributes()->url);
            $this->item[$name]->url = $url;
            break;
          }
        }
      }
      
      //  Load Menu
      if (!empty($type))  $this->menu->all[$type]->key = $type;
      else
      {
        $this->item[$name]->type = 'map';
        $this->menu->all['map']->key = 'map';
      }
    }
    $this->debug('Load Menu & Item Object - end');      
  }
  function showMneu($mode='all')
  {
    $this->debug('Show Menu - Start');
    
		$this->PushForm('<div id="menu">');
    foreach($this->menu->$mode as $type => $key) $this->PushForm(sprintf('<span id="link" class="btnOFF" onclick="showdiv(this)">%s</span>',$type));
		$this->PushForm('</div>');
    
    $this->debug('Show Menu - end');
	}
	function showItem($mode='all')
	{
    foreach($this->menu->$mode as $type => $key)
    {
      $this->PushForm('<div id="data" style="display:none;">');
      foreach($this->item as $name => $value)
      {
        if ($type == $value->attr->type)
        {
          $path = $this->findfile($value->url);
          if ($path)
          {
            $this->PushForm('<div id="%s" class="body">',$value->name);
            
            $this->PushForm(sprintf('<span class="Pic"><img src="%s" /></span>',$path));
            
            $this->PushForm('<span class="Inf">');
            $this->showAttr($value->attr);
            $this->PushForm('</span>');
            /*
            $this->PushForm('<span class="Inf">');
            $this->showTag($value->tag,$type);
            $this->PushForm('</span>');
                                    */

            $this->showRMT($value->RMT);


            $this->PushForm('</div>');

          }
        }
      }
      $this->PushForm('</div>');
    }
	}
  function showAttr($obj)
  {
    $this->PushForm(sprintf('Name  : %s',$obj->name).'<br/>');
    $this->PushForm(sprintf('Code  : %s',$obj->code).'<br/>');
    $this->PushForm(sprintf('Gift  : %s',$obj->giftable).'<br/>');
    $this->PushForm(sprintf('Buy   : %s',$obj->buyable).'<br/>');
    $this->PushForm(sprintf('Place : %s',$obj->placeable).'<br/>');
  }
  function showTag($obj,$type)
  {
  /*
    switch($type)
    {
      case 'business':
      case 'decoration':
      case 'landmark':
      case 'residence':
    }
            $this->PushForm(sprintf('Buy  : %s x %s',$value->name).'<br/>');
            $this->PushForm(sprintf('Sell : %s',$value->code).'<br/>');
            $this->PushForm(sprintf('Size : %s',$value->giftable).'<br/>');
            $this->PushForm(sprintf('Buy   : %s',$value->buyable).'<br/>');
            $this->PushForm(sprintf('Place : %s',$value->placeable).'<br/>');  
  */
  }
	function showRMT($obj)
	{
    foreach((array)$obj as $kind => $name)
    {
      if ($kind == 'collectable') $this->PushForm('<span class="Inf_large">');
      else $this->PushForm('<span class="Inf">');
      $this->PushForm('Kind : '.$kind.'<br/>');
      foreach ((array)$this->CRT->$name->$kind->per as $per => $res)
      {
        $this->PushForm('<dt>Per  :' . $per . '%</dt><dd>');
        for ($i = 0;$i < count($res) ;$i++)
        {
          if ($kind == 'collectable')
          {
            $path = $this->findfile($this->item[$res[$i]]->url);
            if ($path)  $this->PushForm(sprintf('<img src="%s" width="30px" height="30px">',$path));
          }
          else $this->PushForm($res[$i].' ');
        }
        $this->PushForm('</dd>');
      }
      $this->PushForm('</span>');
    }
	}
  function showRemotePic()
  {
    $this->debug('show Img() - start');
    $this->menu->img;
    
    $this->showImg('<div id="menu">','txt');
    foreach($this->CDATA as $key => $hash)
    {
      $path = $this->findfile($key);
      $location = explode('/',$key);
      if ($path)
      {
        $where = strval($location[1]);
        $num = count($this->menu->img[$where]);
        $this->menu->img[$where][$num] = $path;
        if (count($this->menu->img[$where])==1)
          $this->showImg(sprintf('<span id="link" class="btnOFF" onclick="showdiv(this)">%s</span>',$where),'txt');
      }
    }
    $this->showImg('</div>','txt');
    
    foreach ($this->menu->img as $node => $hash)
    {
      $this->showImg('<div id="data" style="display:none;">','txt');
      $max = count($hash)-1;
      for($i = 0 ; $i < $max ; $i++) $this->showImg($hash[$i],'img');  //count($hash)
      //$this->showImg($hash[0],'img');
      $this->showImg('</div>','txt');
    }
    $this->debug('show Img() - end');
  }
  function findfile($path)
  {
    //$this->debug('findfile() - start');
    //$this->debug('findfile() - find '.$path);
    $flag = false;
    $url = dirname(dirname(dirname(dirname(__FILE__)))).'\\';
    $PATH = array($url,$url.'tmp_dir\\','http://assets.cityville.zynga.com/hashed/');
    $F_info = explode('.',$path);
    $last = count($F_info)-1;
    if (!empty($F_info[$last]))
    {
      for($i = 0;$i< count($PATH) ; $i++)
      {
        if ($i == count($PATH)-1)
        {
          //  remote location
          $Full_Path = $PATH[$i] . $this->CDATA[$path] . '.' . $F_info[$last];
          if ($this->CDATA[$path]!="")  $flag = $Full_Path;
          /*  Check remote file
          $fp = @fopen($Full_Path,'r');
          if ($fp)
          {
            $flag = $Full_Path;
            fclose($fp);
          }
          */
        }
        else
        {
          //  local location
          $Full_Path = $PATH[$i].$path;
          if (file_exists($Full_Path))
            $flag = $Full_Path;
        }
        if ($flag)  break;
      }
    }
    //$this->debug('findfile() - flag = '.$flag);
    //$this->debug('findfile() - end');
    return $flag;
  }
	function show($mode='')
	{
    $body = 0;
    switch($mode)
    {
      case 'debug':
        $this->showRemotePic();
        $body = $this->bug; break;
      case 'pic':   
        $this->showRemotePic();
        $body = $this->IMAGE; break;
      default:
        $body = $this->Form;
    }
		return $body;
	}
  function debug($msg)
  {
    $this->bug .= $msg.'<br/>';
  }
  function PushForm($txt)
  {
    $this->Form .= $txt;
  }
  function showImg($msg,$mode)
  {
    switch($mode)
    {
      case 'txt':
        $this->IMAGE .= $msg;
        break;
      case 'img':
        $this->IMAGE .= sprintf('<span class="Pic"><img src="%s" /></span>',$msg);
        break;
    }
    
  }
}
?>
<?php
class MapPlugin 
{
    var $ld;
        var $rep;
        var $xmlsOb;
		var $matriz;
		var $itemes;

    // ==========================================================================
	function DrawRectangle($posx,$posy,$width,$height,$value)
	{
		for ($i = 0; $i < $width; $i++){
			for ($j = 0 ; $j < $height; $j++){
			$this->matriz[$posx-$i][$posy-$j] = $value;
			}
		}
	}
    function MapPlugin()
    {
        $this->ld = new LocalData();
        $this->xmlsOb = new xmlsOb();
        $this->xmlsOb->GenerateFnames();
		$this->xmlsOb->GenerateSize();
    }
    // ==========================================================================
    function GetInfoFromDB()
    {
		
        $res=$this->ld->GetSelect("select * from world where name='sizeX'");
		$this->matriz = Array();
		$i =0;
		while ($i < 170) {
			$j = 0;
			$linha = Array();
			while ($j < 170) {				
				array_push($linha,0);								
								
				$j++;
				}
			array_push($this->matriz,$linha);
			$i++;
		}	
        $res=$this->ld->GetSelect("select * from objects");
        foreach ($res as $val)
		{
            $v=(string)$val[1];
            $obj=unserialize(base64_decode($v));        
			$this->itemes[] = $obj['itemName']." ".$obj['direction'];
			echo $obj['itemsize'];
			$x = 159-($obj['position']['x']+45);
			$y = 159-($obj['position']['y']+45);
			switch ($obj['className'])
			{
					case "Road":$type = 1; break;
					case "Sidewalk":$type =2;break;
					case "Business":$type =3;break;
					case "Residence":$type =4;break;
					case "Decoration":$type =5;break;
					case "Plot":$type =6;break;
					case "Wilderness":$type =6;break;
					case "LotSite":$type =6;break;
					case "Municipal":$type =7;break;
					case "TrainStation":$type =8;break;
					case "Ship":$type = 8; break;
					case "Headquarter":$type =9;break;
					case "Storage":$type =10;break;
					case "Pier":$type =10;break;
					default: $type = 1; 
			}
			if($obj['direction'] == 1  or $obj['direction'] == 3)
			{
				$this->DrawRectangle($x,$y,$this->xmlsOb->size[$obj['itemName']][y],$this->xmlsOb->size[$obj['itemName']][x],$type);
			}
			elseif($obj['direction'] == 0 or $obj['direction'] == 2 )
			{
				$this->DrawRectangle($x,$y,$this->xmlsOb->size[$obj['itemName']][x],$this->xmlsOb->size[$obj['itemName']][y],$type);
			}
		}
    }	
	    
	
        
    // ==========================================================================
    function GetForm()
    {
         echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
    <head>
<style type="text/css">
	p.pos_fixed
	 {position:fixed;top:30px;right:5px;}
</style></head><body>
<var><h5><p class="pos_fixed"><bdo dir="rtl">!!!726p yb</bdo></p></h5></var>
        <title>Neighbors helper</title>
        <script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
        <script src="..\..\codebase-php\jquery.json-2.2.min.js"></script>
        <style>
            body{
                background-color: rgb(200, 200, 300);
                font-size: 12pt;
            }
			img {
			-ms-interpolation-mode : nearest-neighbor;
			margin: 0;
			padding: 0;
			} 
			div#line { display: block;
			margin: 0;
			padding: 0;
			}
			br{
				margin: 0;
			}
            .zag{
                font-size: 15pt;
                font-weight: bold;
            }
            .zag2{
                font-size: 13pt;
                font-weight: bold;
                width: 300px;
            }
			div#map
			{
			width: 510px;
			height: 510px;
			}
			
                        #maint{
                            //border-style: solid;
                        }
        </style>


        <script>
        //======================================================================
            $(document).ready(function(){
  
            });
        //======================================================================
        </script>
       </head>
        <body>
                <div class="zag" height="35">Map 1.6 (2011-02-05)<hr></div>


                <div style="margin-left: 20px">';
		$colors = Array("dot.jpg", "grey_dot.jpg","citySideWalk_dot.jpg","business_dot.jpg","residence_dot.jpg","decor_dot.jpg","plot_dot.jpg","red_dot.jpg","black_dot.jpg","hq_dot.jpg","com_dot.jpg","other_dot.jpg");
		$cwd = getcwd();

		$x = 170;
		$y = 170;
		
		$gd = imagecreatetruecolor($x, $y);
		$colorsb = Array( imagecolorallocate($gd, 255, 255, 255), imagecolorallocate($gd, 127, 127, 127), imagecolorallocate($gd, 195, 195, 195), imagecolorallocate($gd, 104, 154, 207), imagecolorallocate($gd, 0, 255, 0), imagecolorallocate($gd, 255,165,0 ), imagecolorallocate($gd, 185, 122, 87), imagecolorallocate($gd, 255, 0, 0), imagecolorallocate($gd, 0, 0, 0), imagecolorallocate($gd, 0, 0, 255), imagecolorallocate($gd, 255, 0, 128), imagecolorallocate($gd, 252, 127, 123) );
		$rojo = imagecolorallocate($gd, 255, 0, 0); 
		
		$x = 0;
		$y = 0;
		foreach ($this->matriz as $line)
		{
		foreach ($line as $column)
			{
				$color = $colorsb[$column];
				imagesetpixel($gd,$x,$y,$color);
				$x = $x +1;
			}
		$y = $y + 1;
		$x = 0 ;
		}
		$filename  = $cwd.'\Plugins\Map\Map.png';
		imagepng($gd, $filename );
		
		echo '<img width="510px" height="510px" src="'.$cwd.'\Plugins\Map\Map.png" />'; 
		
	echo '<br><br>
		<li><img src="' . $cwd . '\Plugins\Map\Color\grey_dot.jpg" alt="grey_dot" />' . 'Grey color is = CitySideWalk and Road<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[3] . '" alt="' . $colors[3] . '" />Blue Gray color is = Business<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[4] . '" alt="' . $colors[4] . '" />Green color is = House<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[5] . '" alt="' . $colors[5] . '" />Orange color is = Decoration<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[6] . '" alt="' . $colors[6] . '" />Brown color is = Plot , tree & empty lot<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[7] . '" alt="' . $colors[7] . '" />Red color is = Community Buildings<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[8] . '" alt="' . $colors[8] . '" />Black color is = Train station & Ships<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[9] . '" alt="' . $colors[9] . '" />DodgerBlue4 color is = HQ<br></li>
		<li><img src="' . $cwd . '\Plugins\Map\Color\\' . $colors[10] . '" alt="' . $colors[10] . '" />Pink color is = Storage & Pier<br></li>
   </body>
</html>';
echo'<br><hr><div class="zag"><br><a href="http://bit.ly/eW6S7j" class="postlink" target="blue_black">
   IF YOU LIKE MY PLUGIN CLICK AND SENT ME 1 ENERGY!!!<br>
     <a href="http://bit.ly/eW6S7j" class="postlink" target="blue_black"><br>
   <img src="http://www.ginopaoli.co.za/images/facebook.gif" alt="Image"></a>
     <hr>
</body></html> ';
    }
}

?>
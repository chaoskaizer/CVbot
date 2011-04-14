<?php
class gz_work{

	function decompress($fd){
	    $fr = fopen($fd, 'r');
        $fl = fread($fr, filesize($fd));
        fclose($fr);

        $fn = strrev($fd);
        $fn = substr($fn, 6, strlen($fn)-3);
        $fn = strrev($fn);

        $fw = fopen($fn, 'w');
        fwrite($fw, gzuncompress($fl));
        fclose($fw);
        echo 'Decompressing finished' . "\n";
	}

	function compress($fd){
        $fr = fopen($fd, 'r');
        $fl = fread($fr, filesize($fd));
        fclose($fr);

        $fn = $fd . '.gz';
        $fw = fopen($fn, 'w');
        fwrite($fw, '02GM' . base64_encode(gzcompress($fl)));
        fclose($fw);
        echo 'Compressing finished' . "\n";
	}
}
?>
<?php

// ------------------------------------------------------------------------------
//function fullread($sd, $len) {
//    $ret = '';
//    $read = 0;
//
//    while ($read < $len && ($buf = fread($sd, $len - $read))) {
//        $read += strlen($buf);
//        $ret .= $buf;
//    }
//
//    return $ret;
//}
//// ==========================================================================
//function fullwrite($sd, $buf) {
//    $total = 0;
//    $len = strlen($buf);
//
//    while ($total < $len && ($written = fwrite($sd, $buf))) {
//        $total += $written;
//        $buf = substr($buf, $written);
//    }
//
//    return $total;
//}

// ==========================================================================
//download file though proxy
function GetFile($from, $where, $proxy_use, $host, $port, $user, $pass) {
    $host2 = substr($from, 7, strlen($from));
    if (strpos($host2, "/") !== false) {
        $host2 = substr($host2, 0, strpos($host2, "/"));
    }

    $request="GET " . $from . " HTTP/1.0\r\nHost: " . $host2 . "\r\n";
    if ($proxy_use == 1) {
        $fp = fsockopen($host, (int) $port);
        $authorization = base64_encode($user . ':' . $pass);
        $request.="Proxy-Authorization: Basic $authorization" . "\r\n";
    } else {
        $fp = fsockopen($host2, 80);
    }
    $request.="\r\n\r\n";
    
    if ($fp) {
        fputs($fp, $request);
        while (!feof($fp)) {
            $answer.=fgets($fp, 128);
        }
        fclose($fp);

        $pos = strpos($answer, "\r\n\r\n");
        if ($pos !== false) {
            $answer = substr($answer, $pos + 4, strlen($answer));
        }
        $fl = fopen($where, 'w');
        fwrite($fl, $answer);
        fclose($fl);
    }
}

// ==========================================================================
//get GET params
function SetGetParams($arg) {
    global $getP;

    if (isset($arg)) {
        $par = explode('&', $arg);
        foreach ($par as $val){
            $tmp=explode('=', $val);
            $getP[$tmp[0]]=$tmp[1];
        }
    }

    if (isset($rez)) $getP=(int)$rez[1];
}
//function GetParams($arg) {
//    $tmp = explode('&', $arg);
//    foreach ($tmp as $line) {
//        $pos = strpos($line, '=');
//        $name = trim(substr($line, 0, $pos));
//        $val = trim(substr($line, $pos + 1, strlen($line)));
//        $params[$name] = $val;
//    }
//    return $params;
//}
// ==========================================================================
//get port for sending API
function SetUdpPort($arg) {
    global $udpport;
    
    if (isset($arg)) {
        $rez = explode('=', $arg);
    }

    if (isset($rez)) $udpport=(int)$rez[1];
}
// ==========================================================================
//get userId
function SetUserId($arg) {
    global $CurrentUserId;
    
    if (isset($arg)) {
        $rez = explode('=', $arg);
    }

     if (isset($rez)) $CurrentUserId=$rez[1];
}

// ==========================================================================
//get POST params
function SetPost($arg) {
    $tmp=explode('=', $arg);
    
    global $postdata;
    $postdata=json_decode(base64_decode($tmp[1]));

//    $data = '';
//    $fname = 'tmp_dir\\' . $userId . '_post.data';
//    if (file_exists($fname)) {
//        $fl = fopen($fname, 'r');
//        $data = fread($fl, filesize($fname));
//        fclose($fl);
//        $res = json_decode($data);
//    }
//    return $res;
//    if (isset($arg)) {
//        $rez = explode('=', $arg);
//    }
//
//     if (isset($rez)) $postdata=$rez[1];
}
// ==========================================================================
//get all arg from stdin
function AutoStart($argv) {
    SetGetParams($argv[1]);
    SetUserId($argv[2]);
    SetPost($argv[3]);
    SetUdpPort($argv[4]);

    
    if(isset($argv[5])){
        global $headers_file, $amfbin_file;
        $headers_file=$argv[5];
        $amfbin_file=$argv[6];
    }
}

?>
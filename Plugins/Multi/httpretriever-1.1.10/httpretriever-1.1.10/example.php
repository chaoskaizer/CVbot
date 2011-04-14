<?php

// HTTPRetriever usage example
require_once("class_HTTPRetriever.php");
$http = &new HTTPRetriever();


// Example GET request:
// ----------------------------------------------------------------------------
$keyword = "blitzaffe code"; // search Google for this keyword
if (!$http->get("http://www.google.com/search?hl=en&q=%22".urlencode($keyword)."%22&btnG=Search&meta=")) {
echo "HTTP request error: #{$http->result_code}: {$http->result_text}";
return false;
}
echo "HTTP response headers:<br><pre>";
var_dump($http->response_headers);
echo "</pre><br>";

echo "Page content:<br><pre>";
echo $http->response;
echo "</pre>";
// ----------------------------------------------------------------------------


// Example POST request:
// ----------------------------------------------------------------------------
$keyword = "blitzaffe code"; // search Google for this keyword
$values = array(
"hl"=>"en",
"q"=>"%22".urlencode($keyword)."%22",
"btnG"=>"Search",
"meta"=>""
);
// Note: This example is just to demonstrate the POST equivalent of the GET
// example above; running this script will return a 501 Not Implemented, as
// Google does not support POST requests.
if (!$http->post("http://www.google.com/search",$http->make_query_string($values))) {
echo "HTTP request error: #{$http->result_code}: {$http->result_text}";
return false;
}
echo "HTTP response headers:<br><pre>";
var_dump($http->response_headers);
echo "</pre><br>";

echo "Page content:<br><pre>";
echo $http->response;
echo "</pre>";
// ----------------------------------------------------------------------------


?>

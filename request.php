<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  $method = $_SERVER['REQUEST_METHOD'];
  $url = null;
  $data = null;
  $urlEncoded = false;

  // get data from the request
  if ($method == 'GET') {
    $data = $_GET;
  } else { // POST, PUT, DELETE
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
  }

  // get url to request
  $url = (isset($data['url']) && !empty($data['url'])) ? $data['url'] : null;

  if ($url != null) {
    unset($data['url']);

    // get http headers from the request and prepare it for curl
    $headersFromRequest = getallheaders();
    $headersForCurl = array();
    foreach ($headersFromRequest as $key => $value) {
      if ($key == 'Accept' || $key == 'Authorization' || $key == 'Content-Type') {
        $headersForCurl[] = $key . ': ' . $value;
      }

      if ($key == 'Content-Type' && strrpos($value, 'application/x-www-form-urlencoded') !== false) {
        $urlEncoded = true;
      }
    }

    // prepare data for request
    if ($urlEncoded) {
      $data = http_build_query($data);
    } else {
      $data = json_encode($data);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Curl');

    switch ($method) {
      case 'GET':

        break;

      case 'POST':
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        break;

      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        break;
        
      case 'DELETE':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headersForCurl);

    // Send the request & save response to $resp
    $resp = curl_exec($ch);

    if (curl_errno($ch)) {
        print "Error: " . curl_error($ch);
    } else {
        // Show me the result
        echo $resp;
        curl_close($ch);
    }
  }
?>
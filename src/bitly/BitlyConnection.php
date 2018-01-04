<?php

class BitlyConnection {

  private $url = "https://api-ssl.bitly.com";
  private $token;

  public function __construct ($token) {

    $this->token = $token;

  }

  public function request ($method, $params = []) {
    $url = "$this->url/$method";
    $params['access_token'] = $this->token;
    $content = null;
    if (!empty($params)) {
      $content = http_build_query($params);
    }
    $options = array(
      'http' => array(
        'method' => 'GET',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $content
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
  }

}

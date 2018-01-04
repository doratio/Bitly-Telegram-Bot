<?php
namespace bitly;

require("bitly/BitlyConnection.php");
require_once("exceptions/ShortLinkNotFounded.php");

class RestApi {

  private $connection;

  public function __construct () {
    $config = \parse_ini_file("bitly/config.ini");
    $this->connection = new \BitlyConnection($config['token']);
  }

  public function getExistLinks ($offset = 0) {
    return $this->connection->request("v3/user/link_history", [
      'offset' => $offset,
      'limit' => 10
    ])['data']['link_history'];
  }

  public function expand ($shortUrl) {
    return $this->connection->request("v3/link/info", [
      'link' => $shortUrl
    ])['data']['canonical_url'];
  }

  public function createBitlink ($url) {
    return $this->connection->request("v3/user/link_save", [
      'longUrl' => $url
    ])['data']['link_save']['link'];
  }

}

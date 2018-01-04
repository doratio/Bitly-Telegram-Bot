<?php
namespace bitly;

require("bitly/BitlyConnection.php");

class RestApi {

  private $connection;

  public function __construct () {
    $config = \parse_ini_file("bitly/config.ini");
    $this->connection = new \BitlyConnection($config['token']);
  }

  public function getExistLinks ($offset = 0) {
    return $this->connection->request("v3/user/link_history", array(
      'offset' => $offset,
      'limit' => 10
    ))['data'];
  }

}

<?php

namespace bitly;

use exceptions\ShortLinkNotFoundedException;

require_once(__DIR__ . "/BitlyConnection.php");
require_once(__DIR__ . "/../exceptions/ShortLinkNotFoundedException.php");

/**
 * Class RestApi
 *
 * Предоставляет методы для доступа
 * к Bitly API
 *
 * @package bitly Содержит все классы для работы с Bitly API
 */
class RestApi
{

    /**
     *
     * Экземпляр класса BitlyConnection
     *
     * Предоставляет общие методы для работы
     * с подключением к Bitly API
     *
     * @var \BitlyConnection
     */
    private $connection;

    /**
     * RestApi конструктор.
     *
     * Создает экземпляр BitlyConnection
     *
     */
    public function __construct()
    {
        $config = \parse_ini_file(__DIR__ . "/config.ini");
        $this->connection = new \BitlyConnection($config['token']);
    }

    /**
     *
     * Возвращает список ссылок и их сокращений
     *
     * @param int $offset Количество пропускаемых с начала данных
     * @return array Массив ссылок
     */
    public function getExistLinks($offset = 0)
    {
        echo $offset;
        return $this->connection->request("v3/user/link_history", [
            'offset' => $offset,
            'limit' => 3
        ])['data']['link_history'];
    }

    /**
     *
     * Возвращает исходную ссылку, для которой создавалась
     * сокращенная bitly ссылка
     *
     * @param $shortUrl Сокращенная ссылка bit.ly или другого вида
     * @return string Исходная ссылка, для которой создавалась сокращенная
     * @throws ShortLinkNotFoundedException
     */
    public function expand($shortUrl)
    {
        $link = $this->connection->request("v3/link/info", [
            'link' => $shortUrl
        ])['data']['canonical_url'];
        if (empty($link)) {
            throw new ShortLinkNotFoundedException('Ссылка не найдена');
        }
        return $link;
    }

    /**
     *
     * Возвращает ссылку с добавленным протоколом http
     *
     * @param string $url Ссылка
     * @return string Ссылка с добавленным протоколом
     */
    public function addHttp($url)
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            return "http://" . $url;
        }
        return $url;
    }

    /**
     *
     * Создает сокращенную ссылку и возвращает ее.
     * Если сокращенная ссылка уже создана, просто возращает ее.
     *
     * @param string $url Ссылка
     * @return string Сокращенная ссылка
     */
    public function createBitlink($url)
    {
        $res = $this->connection->request("v3/user/link_save", [
            'longUrl' => $this->addHttp($url)
        ]);
        return $res['data']['link_save']['link'];
    }

}

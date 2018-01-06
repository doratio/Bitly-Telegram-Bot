<?php

namespace telegram;

/**
 * Class Connection
 *
 * Предоставляет общие методы для доступа
 * к Telegram API
 *
 * @package telegram
 */
class Connection
{

    /**
     * @var string Ссылка на Telegram API
     */
    private $url;

    /**
     * Bot constructor.
     * @param $token Telegram Access Token
     * @param $url Ссылка на Telegram API
     */
    public function __construct($token, $url)
    {
        $this->url = $url . $token;
    }

    /**
     *
     * Отправляет запрос к методу с параметрами
     *
     * @param $method Метод Telegram API
     * @param array $params Отправляемые параметры
     * @return array Массив ответа
     */
    public function request($method, $params = [])
    {
        $url = $this->url . "/$method";
        $vars = null;
        if (!empty($params)) {
            $vars = http_build_query($params);
        }

        $options = array(
            'http' => array(
                'method' => 'POST',  // метод передачи данных
                'header' => 'Content-type: application/x-www-form-urlencoded',  // заголовок
                'content' => $vars,  // переменные
            )
        );

        $context = stream_context_create($options);  // создаём контекст потока
        $result = file_get_contents($url, false, $context); //отправляем запрос

        return json_decode($result, true);
    }
}
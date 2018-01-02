<?php
/**
 * Created by PhpStorm.
 * User: doratio
 * Date: 02.01.2018
 * Time: 19:15
 */

class Connection
{

    private $url;

    /**
     * Bot constructor.
     * @param $token
     * @param $urlTelegram
     */
    public function __construct($token, $url)
    {
        $this->url = $url.$token;
    }

    public function request($method, $params=[])
    {
        $vars = null;
        if (!empty($params)) {
            $vars = http_build_query($params);
        }

        $options = array(
            'http' => array(
                'method'  => 'POST',  // метод передачи данных
                'header'  => 'Content-type: application/x-www-form-urlencoded',  // заголовок
                'content' => $vars,  // переменные
            )
        );

        $context  = stream_context_create($options);  // создаём контекст потока
        $result = file_get_contents("$this->url/$method", false, $context); //отправляем запрос

        return json_decode($result);
    }


}
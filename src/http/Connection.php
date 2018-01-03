<?php
class Connection
{

    private $url;

    /**
     * Bot constructor.
     * @param $token
     * @param $url
     */
    public function __construct($token, $url)
    {
        $this->url = $url.$token;
    }

    public function request($method, $params=[])
    {
        $url=$this->url."/$method";
        $vars = null;
        if (!empty($params)) {
            $vars = http_build_query($params);
            $this->url.="?$vars";
        }

        $options = array(
            'http' => array(
                'method'  => 'GET',  // метод передачи данных
                'header'  => 'Content-type: application/x-www-form-urlencoded',  // заголовок
//                'content' => $vars,  // переменные
            )
        );

        $context  = stream_context_create($options);  // создаём контекст потока
        $result = file_get_contents($url, false, $context); //отправляем запрос

        return json_decode($result);
    }


}
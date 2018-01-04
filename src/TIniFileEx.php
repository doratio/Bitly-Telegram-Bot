<?php
if (!defined('_BR_'))
    /**
     *
     */
    define('_BR_',chr(13).chr(10));

/**
 * Class TIniFileEx
 */
class TIniFileEx {
    /**
     * @var string Название файла
     */
    public $filename;
    /**
     * @var array
     */
    public $arr;

    /**
     * TIniFileEx constructor.
     * @param bool $file
     */
    function __construct($file = false){
        if ($file)
            $this->loadFromFile($file);
    }

    /**
     *  Парсит файл c расширением ini
     */
    function initArray(){
        $this->arr = parse_ini_file($this->filename, true);
    }

    /**
     * Оболочка метода initArray, для проверки на существование файла
     * @param $file
     * @return bool
     */
    function loadFromFile($file){
        $result = true;
        $this->filename = $file;
        if (file_exists($file) && is_readable($file)){
            $this->initArray();
        }
        else
            $result = false;
        return $result;
    }

    /**
     * Читает из ini файла определенное значение
     * @param $section
     * @param $key
     * @param string $def значение возвращающие по умолчанию
     * @return string
     */
    function read($section, $key, $def = ''){
        if (isset($this->arr[$section][$key])){
            return $this->arr[$section][$key];
        } else
            return $def;
    }

    /**
     * Записывает в ini файл новое значение или изменяет старое;
     * @param $section
     * @param $key
     * @param $value
     */
    function write($section, $key, $value){
        if (is_bool($value))
            $value = $value ? 1 : 0;
        $this->arr[$section][$key] = $value;
    }

    /**
     * Записывает все строки в файл
     * @return bool
     */
    function updateFile(){
        $result = '';
        foreach ($this->arr as $sname=>$section){
            $result .= '[' . $sname . ']' . _BR_;
            foreach ($section as $key=>$value){
                $result .= $key .'='.$value . _BR_;
            }
            $result .= _BR_;
        }
        $file_handle = fopen($this->filename, "w");
        fwrite($file_handle, $result);
        fclose($file_handle);
        return true;
    }

    /**
     *TIniFileEx destruct
     */
    function __destruct(){
        $this->updateFile();
    }
}
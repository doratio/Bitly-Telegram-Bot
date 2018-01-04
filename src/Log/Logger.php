<?php

/**
 * Class Logger
 *
 * Абстрактный класс логгера
 *
 */
abstract class Logger
{
    /**
     *
     * Абстрактная функция логгирования
     *
     * @param string $textLog Сообщение
     * @return void
     */
    abstract public function log($textLog);
}
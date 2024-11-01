<?php

namespace Stidner\Exceptions;

use Stidner\Init;

class UserException extends \Exception
{

    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        Init::log($this->getFile() . ':' . $this->getLine() . ' - ' . $message, \WC_Log_Levels::CRITICAL);
        parent::__construct($message, $code, $previous);
    }

}
<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

/**
 * Class PaysonApiException
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class PaysonApiException extends \Exception
{
    /**
     * @var array
     */
    private $errors = array();

    /**
     * PaysonApiException constructor.
     *
     * @param string $message
     * @param array  $errors
     */
    public function __construct($message, array $errors = array())
    {
        $this->errors = $errors;
        if (count($errors)) {
            $message .= "\n\n";
            foreach ($errors as $error) {
                $message .= "\n" . $error . "\n";
            }
            $message .= "\n\n";
        }
        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getErrorList()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        $r = '';
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $r .= '<pre>' . $error->message . ($error->parameter ? '  --  Parameter: ' . $error->parameter : '') . '</pre>';
            }
        } else {
            $r .= $this->getMessage();
        }

        return $r;
    }

}
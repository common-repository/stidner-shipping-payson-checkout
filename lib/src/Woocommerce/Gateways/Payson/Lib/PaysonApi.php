<?php

namespace Stidner\Woocommerce\Gateways\Payson\Lib;

use Stidner\Init;

/**
 * Class PaysonApi
 * @package Stidner\Woocommerce\Gateways\Payson\Lib
 */
class PaysonApi
{

    /**
     *
     */
    const PAYSON_HOST = "api.payson.se/2.0/";
    /**
     *
     */
    const ACTION_CHECKOUTS = "Checkouts/";
    /**
     *
     */
    const ACTION_ACCOUNTS = "Accounts/";
    /**
     * @var array
     */
    public $paysonResponseErrors = array();
    /**
     * @var
     */
    private $merchantId;
    /**
     * @var
     */
    private $apiKey;
    /**
     * @var string
     */
    private $protocol = "https://%s";
    /**
     * @var null
     */
    private $paysonMerchant = null;
    /**
     * @var null
     */
    private $payData = null;
    /**
     * @var null
     */
    private $customer = null;
    /**
     * @var array
     */
    private $allOrderData = array();
    /**
     * @var null
     */
    private $gui = null;
    /**
     * @var bool|null
     */
    private $useTestEnvironment = null;
    /**
     * @var null
     */
    private $checkoutId = null;
    /**
     * @var null
     */
    private $paysonResponse = null;

    /**
     * PaysonApi constructor.
     *
     * @param      $merchantId
     * @param      $apiKey
     * @param bool $useTestEnvironment
     *
     * @throws PaysonApiException
     */
    public function __construct($merchantId, $apiKey, $useTestEnvironment = false)
    {
        $this->useTestEnvironment = $useTestEnvironment;
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;

        if (!function_exists('curl_exec')) {
            throw new PaysonApiException('Curl not installed. Is required for PaysonApi.');
        }
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param Checkout $checkout
     *
     * @return array|null|string
     * @throws PaysonApiException
     */
    public function CreateCheckout(Checkout $checkout)
    {
        $result = $this->doCurlRequest('POST', $this->getUrl(self::ACTION_CHECKOUTS), $checkout->toArray());
        $checkoutId = $this->extractCheckoutId($result);
        if (!$checkoutId) {
            throw new PaysonApiException(__('Checkout Id not received of unclear reason', Init::TEXT_DOMAIN));
        }


        return $checkoutId;
    }

    /**
     * @param $method
     * @param $url
     * @param $postfields
     *
     * @return mixed|string
     * @throws PaysonApiException
     */
    private function doCurlRequest($method, $url, $postfields)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->authorizationHeader());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields ? json_encode($postfields) : null);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /* This class of status codes indicates the action requested by the client was received, understood, accepted and processed successfully
         * 200 OK
         * 201 Created
         * 202 Accepted
         * 203 Non-Authoritative Information (since HTTP/1.1)
         */
        if ($response_code == 200) {
            return $body;
        } elseif ($response_code == 201) {
            return $result;
        } elseif ($result == false) {
            throw new PaysonApiException('Curl error: ' . curl_error($ch));
        } else {
            $errors = array();

            $data = json_decode($body, true);
            $errors[] = new PaysonApiError('HTTP status code: ' . $response_code . ', ' . $data['message'], null);

            if (isset($data['errors']) && count($data['errors'])) {
                $errors = array_merge($errors, $this->parseErrors($data['errors'], $response_code));
            }

            throw new PaysonApiException(__('Payson API errors', Init::TEXT_DOMAIN), $errors);
        }
    }

    /**
     * @return array
     */
    private function authorizationHeader()
    {
        if (!isset($this->merchantId, $this->apiKey)) {
            $settings_page = '<a target="_blank" href="' .
                admin_url('admin.php?page=wc-settings&tab=checkout&section=paysoncheckout')
                . '">'.__('PaysonCheckout plugin settings page', Init::TEXT_DOMAIN).'</a>';
            throw new PaysonApiException(sprintf(__('You didn\'t provide your Payson credentials. Please go to %s and specify the credentials there.', Init::TEXT_DOMAIN),
                $settings_page));
        }

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: Basic ' . base64_encode($this->merchantId . ':' . $this->apiKey);

        return $header;
    }

    /**
     * @param $responseErrors
     * @param $response_code
     *
     * @return array
     */
    private function parseErrors($responseErrors, $response_code)
    {
        $errors = array();
        foreach ($responseErrors as $error) {
            $errors[] = new PaysonApiError($error['message'], (isset($error['property']) ? $error['property'] : null));
        }

        return $errors;
    }

    /**
     * @param $action
     *
     * @return string
     */
    private function getUrl($action)
    {
        return (sprintf($this->protocol, ($this->useTestEnvironment ? 'test-' : '')) . self::PAYSON_HOST . $action);
    }

    /**
     * @param $result
     *
     * @return array|null|string
     */
    private function extractCheckoutId($result)
    {
        $checkoutId = null;
        if (preg_match('#Location: (.*)#', $result, $res)) {
            $checkoutId = trim($res[1]);
        }
        $checkoutId = explode('/', $checkoutId);
        $checkoutId = $checkoutId[count($checkoutId) - 1];

        return $checkoutId;
    }

    /**
     * @param $checkoutId
     *
     * @return Checkout
     * @throws PaysonApiException
     */
    public function GetCheckout($checkoutId)
    {
        $result = $this->doCurlRequest('GET', $this->getUrl(self::ACTION_CHECKOUTS) . $checkoutId, null);

        return Checkout::create(json_decode($result));
    }

    /**
     * @param Checkout $checkout
     *
     * @return mixed
     * @throws PaysonApiException
     */
    public function ShipCheckout(Checkout $checkout)
    {
        $checkout->status = 'shipped';

        return $this->UpdateCheckout($checkout);
    }

    /**
     * @param $checkout
     *
     * @return mixed
     * @throws PaysonApiException
     */
    public function UpdateCheckout($checkout)
    {
        if (!$checkout->id) {
            throw new PaysonApiException(__('Checkout object which should be updated must have id property set', Init::TEXT_DOMAIN));
        }
        $this->doCurlRequest('PUT', $this->getUrl(self::ACTION_CHECKOUTS) . $checkout->id, $checkout->toArray());

        return $checkout;
    }

    /**
     * @param Checkout $checkout
     *
     * @return mixed
     * @throws PaysonApiException
     */
    public function CancelCheckout(Checkout $checkout)
    {
        $checkout->status = 'canceled';

        return $this->UpdateCheckout($checkout);
    }

    /**
     * @return Account
     * @throws PaysonApiException
     */
    public function Validate()
    {
        $result = $this->doCurlRequest('GET', $this->getUrl(self::ACTION_ACCOUNTS), null);

        return Account::create(json_decode($result));
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->allOrderData['status'] = $status;
    }


}
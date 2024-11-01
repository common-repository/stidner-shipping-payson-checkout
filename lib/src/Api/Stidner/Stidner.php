<?php

namespace Stidner\Api\Stidner;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Api\Stidner\Objects\PackageRequest;
use Stidner\Api\Stidner\Objects\OrderProducts;
use Stidner\Exceptions\ApiException;
use Stidner\Init;

/**
 * Class Stidner
 *
 * @package Stidner\Api\Stidner
 */
class Stidner
{
    /**
     *
     */
    const METHOD_POST = 'POST';

    /**
     *
     */
    const METHOD_GET = 'GET';

    /**
     *
     */
    const METHOD_PUT = 'PUT';

    /**
     *
     */
    const LIVE = 'https://shipping-api.stidner.com';

    /**
     *
     */
    const ENDPOINT_ORDER_CREATE = '/api/v1/order/widget';

    /**
     *
     */
    const ENDPOINT_ORDER_GET = '/api/v1/order/{order_uuid}';

    /**
     *
     */
    const ENDPOINT_ORDER_UPDATE = '/api/v1/order/{order_uuid}';

    /**
     *
     */
    const ENDPOINT_IMPORT_SHIPPING_OPTIONS = '/api/v1/order/{order_uuid}/options';

    /**
     *
     */
    const ENDPOINT_PACKAGE_CREATE = '/api/v1/order/{order_uuid}/package';

    /**
     *
     */
    const ENDPOINT_TRACKING = '/tracking/{shipment_number}';

    /**
     *
     */
    const ENDPOINT_PICKUP = '/api/v1/pickup';

    /**
     * @var
     */
    private $merchant_id;

    /**
     * @var
     */
    private $api_key;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $test;

    /**
     * Stidner constructor.
     *
     * @param $merchant_id
     * @param $api_key
     * @param $test boolean
     *
     */
    public function __construct($merchant_id, $api_key, $test)
    {

        $this->merchant_id = $merchant_id;
        $this->api_key = $api_key;
        $this->test = $test;

        $this->url = self::LIVE;
    }

    /**
     * @param Order $order
     *
     * @return Order
     * @throws \Exception
     */
    public function orderCreate(Order $order)
    {

        try {
            $data = $order->toArray();
            $data['async'] = true;

            $result = $this->send($this->parseEndpoint(self::ENDPOINT_ORDER_CREATE), self::METHOD_POST,
                json_encode($data));

            return new Order($result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param        $url
     * @param        $method
     *
     * @param string $data
     *
     * @return mixed
     * @throws \Exception
     * @throws \Stidner\Exceptions\ApiException
     */
    private function send($url, $method, $data = '')
    {

        Init::log(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

        if (!isset($this->merchant_id, $this->api_key)) {
            $settings_page = '<a target="_blank" href="' .
                admin_url('admin.php?page=wc-settings&tab=shipping&section=stidner')
                . '">' . __('Stidner Shipping plugin settings page', Init::TEXT_DOMAIN) . '</a>';
            throw new ApiException(sprintf(__('You didn\'t provide your Stidner credentials. Please go to %s and specify the credentials there.',
                Init::TEXT_DOMAIN), $settings_page));
        }

        if ($this->test) {
            Init::log('Request: ' . $method . ' ' . $url . ' : ' . $data);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . base64_encode($this->merchant_id . ':' . $this->api_key),
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new ApiException(curl_error($curl));
        }

        curl_close($curl);

        if ($this->test) {
            Init::log('Response: ' . $method . ' ' . $url . ' : ' . $response);
        }

        try {
            return $this->response($response);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $response
     *
     * @return mixed
     * @throws ApiException
     */
    private function response($response)
    {

        $response = json_decode($response);

        if (isset($response->error)) {
            throw new ApiException($this->error($response));
        }

        if (isset($response->data)) {
            return $response->data;
        }

        throw new ApiException(__('Stidner Error', Init::TEXT_DOMAIN));
    }

    private function error($response)
    {

        $settings_page = '<a target="_blank" href="' .
            admin_url('admin.php?page=wc-settings&tab=shipping&section=stidner')
            . '">Stidner Shipping plugin settings page</a>';

        $dashboard_page = '<a target="_blank" href="https://dashboard.stidner.com">Stidner Dashboard</a>';

        $errors = [
            'e_AUTH_FAILED'           => [
                'r_MERCHANT_CREDENTIALS_MISSING_IN_REQUEST' => sprintf(__('You didn\'t provide your Stidner credentials. Please go to %s and specify the credentials there.',
                    Init::TEXT_DOMAIN), $settings_page),
                'r_MERCHANT_NOT_FOUND'                      => sprintf(__('Merchant with specified API credentials is not found. Please go to %s and check if you specified correct credentials. You can obtain your API credentials at dashboard.stidner.com',
                    Init::TEXT_DOMAIN), $settings_page),
                'r_MERCHANT_NOT_LIVE'                       => sprintf(__('You are using Live API Key, but your Stidner account is not in live mode yet. Please go to %s and specify sandbox API Key. You can obtain your API credentials at dashboard.stidner.com',
                    Init::TEXT_DOMAIN), $settings_page),
            ],
            'e_PICKUP_REQUEST_FAILED' => [
                'r_CANNOT_PERFORM_PICKUP_REQUEST_ON_TEST_MODE_ORDERS' => sprintf(__('This order is in Test mode, and thus we can\'t request a pickup for it.',
                    Init::TEXT_DOMAIN)),
            ],
            'e_PACKAGE_FALIED'        => [
                'r_CANNOT_SHIP_PACKAGE_ON_NOT_VALID_ORDER' => sprintf(__('This order is missing some information. Please go to %s and correct this order\'s information.',
                    Init::TEXT_DOMAIN), $dashboard_page), // TODO: link directly to the order on dashboard, and/or show exact problem
            ]
        ];

        $string = (isset($response->reasons[0]) and isset($errors[$response->error][$response->reasons[0]])) ? $errors[$response->error][$response->reasons[0]] : json_encode($response);

        return $string;
    }

    /**
     * @param        $endpoint
     * @param string $order_uuid
     * @param string $shipment_number
     *
     * @return string
     */
    private function parseEndpoint($endpoint, $order_uuid = '', $shipment_number = '')
    {

        $endpoint = str_replace('{order_uuid}', $order_uuid, $endpoint);
        $endpoint = str_replace('{shipment_number}', $shipment_number, $endpoint);

        return trim($this->url . $endpoint);
    }

    /**
     * @param $order_uuid
     *
     * @return Order
     * @throws \Exception
     */
    public function orderGet($order_uuid)
    {

        try {
            $result = $this->send($this->parseEndpoint(self::ENDPOINT_ORDER_GET, $order_uuid), self::METHOD_GET);

            return new Order($result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param       $order_uuid
     *
     * @return Order
     * @throws \Exception
     */
    public function orderUpdate(Order $order, $order_uuid)
    {

        try {
            $result = $this->send($this->parseEndpoint(self::ENDPOINT_ORDER_UPDATE, $order_uuid), self::METHOD_PUT,
                $order->toJson());

            return new Order($result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param OrderProducts  $shippingOption
     * @param                $order_uuid
     *
     * @return array|\WP_Error
     * @throws \Exception
     */
    public function importShippingOptions(OrderProducts $shippingOption, $order_uuid)
    {

        try {
            return $this->send($this->parseEndpoint(self::ENDPOINT_IMPORT_SHIPPING_OPTIONS, $order_uuid),
                self::METHOD_PUT, $shippingOption->toJson());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $shipment_number
     *
     * @return mixed
     * @throws \Exception
     */
    public function tracking($shipment_number)
    {
        try {
            return $this->send($this->parseEndpoint(self::ENDPOINT_TRACKING, '', $shipment_number), self::METHOD_GET);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param PackageRequest $package
     * @param                $order_uuid
     *
     * @return mixed
     * @throws \Exception
     */
    public function createPackage(PackageRequest $package, $order_uuid)
    {
        try {
            return $this->send($this->parseEndpoint(self::ENDPOINT_PACKAGE_CREATE . '/' . $package->getDirection(), $order_uuid), self::METHOD_POST,
                json_encode(['items' => $package->getPackageItems()]) );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $packages
     *
     * @return mixed
     * @throws \Exception
     */
    public function requestPickup($packages)
    {
        try {
            return $this->send($this->parseEndpoint(self::ENDPOINT_PICKUP), self::METHOD_POST, json_encode($packages));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

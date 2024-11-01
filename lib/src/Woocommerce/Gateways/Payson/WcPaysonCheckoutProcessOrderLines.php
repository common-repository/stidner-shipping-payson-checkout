<?php

namespace Stidner\Woocommerce\Gateways\Payson;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Init;
use Stidner\Woocommerce\Gateways\Payson\Lib\OrderItemType;

/**
 * Class WcPaysonCheckoutProcessOrderLines
 *
 * @package Stidner\Woocommerce\Gateways\Payson
 */
class WcPaysonCheckoutProcessOrderLines
{
    /**
     * Get order lines from order or cart
     *
     *
     * @param Order $order
     *
     * @return array $order_lines
     */
    public function getOrderLines(Order $order)
    {
        switch ($order->getCurrency()) {
            case 'SEK':
                $pay_data = new Lib\PayData(Lib\CurrencyCode::SEK);
                break;

            case 'EUR':
                $pay_data = new Lib\PayData(Lib\CurrencyCode::EUR);
                break;

            default:
                throw new \Exception('Currency "'.$order->getCurrency().'" is not supported by PaysonCheckout. Please choose a different currency.');
        }

        $wc_order_id = WC()->session->get('ongoing_payson_order');

        $wc_order = wc_get_order($wc_order_id);

        if (count(WC()->cart->cart_contents) > 0) {
            foreach ($wc_order->get_items(['line_item', 'fee']) as $item_key => $item) {
                $pay_data->AddOrderItem(
                    $this->getProductItemFromOrder(
                        $wc_order, $item
                    )
                );
            }
        }

        if ($order->getShippingPrice() > 0) {
            $pay_data->AddOrderItem($this->getShippingItem($order));
        }

        // Process fees.
        if (WC()->cart->fee_total > 0) {
            foreach (WC()->cart->get_fees() as $cart_fee) {
                $pay_data->AddOrderItem($this->getFees($cart_fee));
            }
        }

        return $pay_data;
    }

    /**
     * @param \WC_Order $order
     * @param           $item
     *
     * @return Lib\OrderItem
     */
    private function getProductItemFromOrder(\WC_Order $order, $item)
    {

        if ($item['type'] == 'fee') {
            return new  Lib\OrderItem(
                'fee', $item['line_total'], 1, 0, $item->get_id()
            );
        } else {
            $product = $item->get_product();
            $tax = new \WC_Tax();
            $tax_class = $tax->get_rates($product->get_tax_class());
            $rates = array_shift($tax_class);
            $rate_percentage = round(array_shift($rates));
            $item_tax_rate = ($rate_percentage > 0) ? $rate_percentage / 100 : 0;
            $sku = $product->get_id();

            $shipping_total = (float)$order->get_shipping_total() + (float)$order->get_shipping_tax();

            // items total including tax
            $items_total = $order->get_total() + $order->get_total_discount(false) - $shipping_total;

            // discount amount in currency
            $discount = $order->get_total_discount(false);

            // calculate discount rate
            $discount_rate = round($discount / $items_total, 2);

            // get item subtotal including tax
            $item_subtotal = $order->get_item_subtotal($item, true, false);
            return new  Lib\OrderItem(
                $item['name'], $item_subtotal, $item['qty'], $item_tax_rate, $sku, OrderItemType::PHYSICAL,
                $discount_rate
            );

        }
    }

    /**
     * @param Order $order
     *
     * @return Lib\OrderItem
     */
    private function getShippingItem(Order $order)
    {

        return new  Lib\OrderItem(
			__('Stidner Shipping', Init::TEXT_DOMAIN), round($order->getShippingPrice() / 100, 2), 1, 0.25, Init::SHIPPING_REFERENCE
        );
    }

    /**
     * @param $cart_fee
     *
     * @return Lib\OrderItem
     */
    private function getFees($cart_fee)
    {
        $cart_fee_tax = array_sum($cart_fee->tax_data);

        return new  Lib\OrderItem(
            $cart_fee->label, round(($cart_fee->amount + $cart_fee_tax), 2), 1,
            round($cart_fee_tax / $cart_fee->amount, 2), 'Fee'
        );
    }
}
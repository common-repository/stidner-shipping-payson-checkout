<?php

namespace Stidner\Woocommerce;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Init;
use WC_Order_Item_Shipping;

/**
 * Class WcOrderActions
 *
 * @package Stidner\Woocommerce
 */
class WcOrderActions
{
	/**
	 * @return bool|\WC_Order|\WP_Error
	 * @throws \Exception
	 */
	public static function updateOrCreateWcOrder()
	{

		if (WC()->session->get('ongoing_payson_order') && wc_get_order(WC()->session->get('ongoing_payson_order'))) {
			$order_id = WC()->session->get('ongoing_payson_order');
			$order    = wc_get_order($order_id);
		} else {
			$order = self::createOrder();
			WC()->session->set('ongoing_payson_order', $order->get_id());
		}

		if (! $order instanceof \WC_Order) {
			throw new \Exception(__('Error: Unable to create order. Please try again.', 'woocommerce'));
		}

		// Need to clean up the order first, to avoid duplicate items.
		$order->remove_order_items();

		// Add order items.
		self::addOrderItems($order);

		// Add order fees.
		self::addOrderFees($order);

		// Add order shipping.
		self::addOrderShipping($order);

		// Add order taxes.
		self::addOrderTaxRows($order);

		// Store coupons.
		self::addOrderCoupons($order);

		// Store payment method.
		self::addOrderPaymentMethod($order);

		// Calculate order totals.
		self::setOrderTotals($order);

		// Add order note to order
		self::addOrderCustomerNote($order);

		do_action('woocommerce_checkout_update_order_meta', $order->get_id(), []);
		
		
		return $order;
	}

	/**
	 * @return \WC_Order|\WP_Error
	 * @throws \Exception
	 */
	private static function createOrder()
	{
		// Customer accounts.
		$customer_id = apply_filters('woocommerce_checkout_customer_id', get_current_user_id());

		// Order data.
		$order_data = [
			'status'      => apply_filters('payson_checkout_incomplete_order_status', 'payson-incomplete'),
			'customer_id' => $customer_id,
			'created_via' => 'payson_checkout',
		];

		// Create the order.
		$order = wc_create_order($order_data);
		if (is_wp_error($order)) {
			throw new \Exception(__('Error: Unable to create order. Please try again.', 'woocommerce'));
		}

		return $order;
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @throws \Exception
	 */
	private static function addOrderItems(\WC_Order $order)
	{
		// Clean up first.
		$order->remove_order_items();

		foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
			$item_id = $order->add_product($values['data'], $values['quantity'], [
					'variation' => $values['variation'],
					'totals'    => [
						'subtotal'     => $values['line_subtotal'],
						'subtotal_tax' => $values['line_subtotal_tax'],
						'total'        => $values['line_total'],
						'tax'          => $values['line_tax'],
						'tax_data'     => $values['line_tax_data'], // Since 2.2.
					],
				]);

			if (! $item_id) {
				throw new \Exception(__('Error: Unable to add order item. Please try again.', 'woocommerce'));
			}

			do_action('woocommerce_add_order_item_meta', $item_id, $values, $cart_item_key);
		}
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @throws \Exception
	 */
	private static function addOrderFees(\WC_Order $order)
	{
		global $woocommerce;
		$order_id = $order->get_id();
		foreach ($woocommerce->cart->get_fees() as $fee_key => $fee) {
			$item_id = $order->add_fee($fee);
			if (! $item_id) {
				throw new \Exception(__('Error: Unable to create order. Please try again.', 'woocommerce'));
			}

			do_action('woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key);
		}
	}

	/**
	 * @param \WC_Order $order
	 *
	 *
	 * @return \WC_Order
	 */
	private static function addOrderShipping(\WC_Order $order)
	{
		if (! defined('WOOCOMMERCE_CART')) {
			define('WOOCOMMERCE_CART', true);
		}

		$stidner_order = (OrderActions::getStidnerOrderFromSession() instanceof Order)
			?OrderActions::getStidnerOrderFromSession()
			:OrderActions::getStidnerOrderFromMeta($order->get_id());


		$shipping = new WC_Order_Item_Shipping();
		$rate = new \WC_Shipping_Rate(
			'stidner',
			'Stidner',
			$stidner_order->getShippingPrice()/100*0.80, // Todo: better support of 25% VAT
			[],
			'stidner'
		);
		$shipping->set_shipping_rate($rate);
		$order->add_item($shipping);

		return $order;


		foreach (WC()->shipping->get_packages() as $package_key => $package) {
			if (isset($package['rates'][Init::PREFIX])) {
				$rate     = $package['rates'][Init::PREFIX];
				$shipping->set_total($rate);
				$shipping->set_method_title(Init::PREFIX);
				$shipping->set_method_id(Init::PREFIX);
				$shipping->set_shipping_rate($rate);
				$order->add_item($shipping);
				break;
			} else {
				continue;
			}
		}

	}

	/**
	 * @param \WC_Order $order
	 *
	 * @throws \Exception
	 */
	private static function addOrderTaxRows(\WC_Order $order)
	{
		foreach (array_keys(WC()->cart->taxes + WC()->cart->shipping_taxes) as $tax_rate_id) {

			$tax = new \WC_Order_Item_Tax;
			$tax->set_rate_id($tax_rate_id);
			$tax->set_tax_total(WC()->cart->get_tax_amount($tax_rate_id));
			$tax->set_shipping_tax_total(WC()->cart->get_shipping_tax_amount($tax_rate_id));

			if ($order->add_item($tax) === false) {
				throw new \Exception(
					sprintf(__('Error %d: Unable to create order. Please try again.', 'woocommerce'), 405)
				);
			}
		}
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @throws \Exception
	 */
	private static function addOrderCoupons(\WC_Order $order)
	{
		foreach (WC()->cart->get_coupons() as $code => $coupon) {
			if (! $order->add_coupon($code, WC()->cart->get_coupon_discount_amount($code))) {
				throw new \Exception(__('Error: Unable to create order. Please try again.', 'woocommerce'));
			}
		}
	}

	/**
	 * @param \WC_Order $order
	 */
	private static function addOrderPaymentMethod(\WC_Order $order)
	{
		global $woocommerce;
		$available_gateways = $woocommerce->payment_gateways->payment_gateways();
		$payment_method     = $available_gateways['paysoncheckout'];
		$order->set_payment_method($payment_method);
	}

	/**
	 * @param \WC_Order $order
	 */
	private static function setOrderTotals(\WC_Order $order)
	{
		if (! defined('WOOCOMMERCE_CHECKOUT')) {
			define('WOOCOMMERCE_CHECKOUT', true);
		}

		if (! defined('WOOCOMMERCE_CART')) {
			define('WOOCOMMERCE_CART', true);
		}

		//WC()->cart->calculate_shipping();
		WC()->cart->calculate_fees();
		WC()->cart->calculate_totals();

		$order->calculate_totals();
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 */
	private static function addOrderCustomerNote(\WC_Order $order)
	{
		if (WC()->session->get('payson_customer_order_note')) {
			$order->set_customer_note(WC()->session->get('payson_customer_order_note'));
			$order->save();
		}
	}
}
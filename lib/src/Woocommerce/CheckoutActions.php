<?php

namespace Stidner\Woocommerce;

use Stidner\Api\Stidner\Objects\Address;
use Stidner\Api\Stidner\Objects\Order;
use Stidner\Exceptions\AdminException;
use Stidner\Exceptions\UserException;
use Stidner\Init;
use Stidner\Woocommerce\Gateways\Payson\WcPaysonCheckoutResponseHandler;
use Stidner\Woocommerce\Gateways\Payson\WcPaysonCheckoutSetupPaysonAPI;

/**
 * Class CheckoutActions
 *
 * @package Stidner\Woocommerce
 */
class CheckoutActions
{
	/**
	 * @param $packages
	 *
	 * @return mixed
	 *
	 */
	public static function calculatePackageRates($packages)
	{
		return $packages;
	}

	/**
	 * Used with ajax
	 *
	 * @throws \Exception
	 */
	public static function stidnerOrderUpdated()
	{
		try {
			$checkout_id   = WC()->session->get('payson_checkout_id');

			$wc_order = (WC()->session->get('ongoing_payson_order'))
				?wc_get_order(WC()->session->get('ongoing_payson_order'))
				:WcOrderActions::updateOrCreateWcOrder();

			if(!$wc_order instanceof \WC_Order){
				$data = [
					'error'   => true,
					'message' => 'Error',
				];
				wp_send_json_error($data);
				die();
			}

			$stidner_order = (OrderActions::getStidnerOrderFromSession() instanceof Order)
				?OrderActions::getStidnerOrderFromSession()
				:OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

			$stidner_order = Init::api()->orderGet($stidner_order->getUuid());
			OrderActions::updateStidnerOrderInSession($stidner_order);
			OrderActions::updateStidnerOrderInMeta($wc_order->get_id(), $stidner_order);
			$payson_api = new WcPaysonCheckoutSetupPaysonAPI();

			$wc_order = WcOrderActions::updateOrCreateWcOrder();

			$checkout   = $payson_api->updateCheckout($stidner_order, $wc_order);

			$data = [
				'error'                 => false,
				'message'               => 'payson order updated',
				'payson_order_replaced' => WC()->session->get('payson_order_replaced', false),
				'new_payson_order'      => $checkout->id,
				'old_payson_order'      => $checkout_id,
			];

			if (WC()->session->get('payson_order_replaced', false)) {
				WC()->session->set('payson_order_replaced', false);
			}

			$it = $checkout->payData->items;

			wp_send_json_success($data);
		} catch (\Exception $e) {
			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];
			wp_send_json_error($data);
		}

		die();

	}

	/**
	 * Used with ajax
	 */
	public static function addressUpdated()
	{
		try {
			$stidner_order = OrderActions::getStidnerOrderFromSession();

			if (! $stidner_order instanceof Order) {
				$data = [
					'error'   => true,
					'message' => 'Error',
				];
				wp_send_json_error($data);
				die();
			}

			$addresses = $stidner_order->getAddresses();

			$payson_api = new WcPaysonCheckoutSetupPaysonAPI();

			$wc_order   = wc_get_order(WC()->session->get('ongoing_payson_order'));

			$checkout = $payson_api->getCheckout(OrderActions::getStidnerOrderFromSession(), $wc_order);

			$payson_handler = new WcPaysonCheckoutResponseHandler;

			$payson_handler->addOrderAddresses(wc_get_order(WC()->session->get('ongoing_payson_order')), $checkout);

			foreach ($addresses as $i => $address) {
				if (isset($address->type) and $address->type == Address::TYPE_RECIPIENT) {
					$address->setContactName($checkout->customer->firstName.' '.$checkout->customer->lastName)
					        ->setCustomerType(Address::CUSTOMER_PERSON)->setType(Address::TYPE_RECIPIENT)
					        ->setName($checkout->customer->firstName.' '.$checkout->customer->lastName)
					        ->setCountryCode($checkout->customer->countryCode)
					        ->setContactEmail($checkout->customer->email)->setContactPhone($checkout->customer->phone)
					        ->setCity($checkout->customer->city)->setPostalCode($checkout->customer->postalCode)
					        ->setAddressLine($checkout->customer->street)->setAddressLine2('');
				}
				unset($address);
			}

			$stidner_order->setAddresses($addresses)->setItems([])->setPaymentSystem('payson')
			              ->setPaymentReference($checkout->id);

			$stidner_order = Init::api()->orderUpdate($stidner_order, $stidner_order->getUuid());

			OrderActions::updateStidnerOrderInSession($stidner_order);

			$data = [
				'error'   => false,
				'message' => 'stidner order updated',
			];

			wp_send_json_success($data);
		} catch (\Exception $e) {
			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];
			wp_send_json_error($data);
		}

		die();
	}

	/**
	 * Add Stidner shipping price to wc cart
	 *
	 * @param \WC_Cart $wc_cart
	 *
	 * @throws AdminException
	 * @throws UserException
	 */
	public static function calculateTotals(\WC_Cart $wc_cart)
	{
		//get order with old price
		$stidner_order = OrderActions::getStidnerOrderFromSession();

		if (! $stidner_order instanceof Order) {
			return;
		}

		if ($wc_cart->prices_include_tax) {
			$amount = $wc_cart->cart_contents_total + $wc_cart->tax_total;
		} else {
			$amount = $wc_cart->cart_contents_total;
		}

		$coupons = $wc_cart->get_applied_coupons();

		if (! empty($coupons) and $amount < 6) {
			$last_coupon_id = count($coupons) - 1;
			$wc_cart->remove_coupon($coupons[$last_coupon_id]);
			$message = __("You can't apply this coupon because price can't be less than 6", Init::TEXT_DOMAIN);
			if (! wc_has_notice($message, 'error')) {
				wc_add_notice($message, 'error');
			}
		}

		return;
	}

	/**
	 * Used with ajax
	 */
	public static function stidnerWidgetAddressUpdated()
	{
		try {
			$stidner_order = OrderActions::getStidnerOrderFromSession();
			$stidner_order = Init::api()->orderGet($stidner_order->getUuid());

			$payson_api = new WcPaysonCheckoutSetupPaysonAPI();

			$wc_order = (WC()->session->get('ongoing_payson_order'))
				?wc_get_order(WC()->session->get('ongoing_payson_order'))
				:WcOrderActions::updateOrCreateWcOrder();

			//$payson_api->updateCheckout($stidner_order, WcOrderActions::updateOrCreateWcOrder(), true);
			$payson_api->updateCheckout($stidner_order, $wc_order, true);

			$data = [
				'error'   => false,
				'message' => 'address updated',
			];
			$code = 200;
		} catch (\Exception $e) {
			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];
			$code = 400;
		}

		wp_send_json($data);
		die();
	}
}
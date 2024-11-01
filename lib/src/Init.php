<?php

namespace Stidner;

/**
 * Class Init
 *
 * @package Stidner
 */
use Stidner\Api\Stidner\Stidner;

/**
 * Class Init
 *
 * @package Stidner
 */
class Init extends Config
{
	/**
	 *  Api helper object
	 * */
	public static $stidner_api;

	/**
	 * @var
	 */
	public static $root_dir;

	/**
	 * @var
	 */
	public static $root_url;

	/**
	 *  Logger object
	 * */
	private static $logger;

	/**
	 * @var array Stidner settings from WooCommerce
	 */
	private static $shipping_options;

	/**
	 * @var array Payson settings from WooCommerce
	 */
	private static $payment_options;

	/**
	 * Init constructor.
	 */
	public function __construct($root_dir, $root_url)
	{
		self::$root_dir = $root_dir;
		self::$root_url = $root_url;


		//TODO add validation of api credentials on options saving.
		self::getOptions();

		self::getPaymentOptions();

		new Hooks();

		if (self::isShippingEnabled()) {

			try {
				new Woocommerce\OrderTable();
			} catch (\Exception $e) {
				self::displayNotice($e->getMessage());
			}
		}
	}

	/**
	 * Get woocommerce options
	 *
	 * @return array
	 */
	public static function getOptions()
	{
		if (is_null(self::$shipping_options)) {
			self::$shipping_options = get_option('woocommerce_'.self::PREFIX.'_settings');
		}

		return self::$shipping_options;
	}

	/**
	 * Get Payson settings
	 *
	 * @return array
	 */
	public static function getPaymentOptions()
	{
		if (is_null(self::$payment_options)) {
			self::$payment_options = get_option('woocommerce_paysoncheckout_settings');
		}

		return self::$payment_options;
	}

	/**
	 * Is stidner enabled
	 *
	 * @return bool
	 */
	public static function isShippingEnabled()
	{
		return true;
	}

	/**
	 * Show standard wp notification
	 *
	 * @param        $message
	 * @param string $type
	 */
	public static function displayNotice($message, $type = 'error')
	{
		add_action(
			'admin_notices',
			function () use ($message, $type) {
				echo '<div class="is-dismissible notice notice-'.$type.'"><p>'.$message.'</p></div>';
			},
			10
		);
	}

	/**
	 * Logging by woocommerce logger
	 *
	 * @param        $message
	 * @param string $level
	 */
	public static function log($message, $level = \WC_Log_Levels::DEBUG)
	{
		if (! self::$logger instanceof \WC_Logger_Interface) {
			self::$logger = wc_get_logger();
		}
		self::$logger->log($level, $message, ['source' => self::LOG_SOURCE]);
	}

	/**
	 *
	 */
	public static function includeAssets()
	{
		if (self::isShippingEnabled() && is_cart()) {

			wp_enqueue_script(Init::PREFIX.'_script', self::$root_url.'assets/app.js', [], '1.0');
			wp_enqueue_style(Init::PREFIX.'_style', self::$root_url.'assets/bootstrap.min.css');
			wp_enqueue_style('bootstrap', self::$root_url.'assets/app.css');

			$js_data = [
				'ajax_url'                       => admin_url('admin-ajax.php'),
				'address_updated'                => 'stidner_address_updated',
				'shipping_option_updated'        => 'stidner_update',
				'stidner_widget_address_updated' => 'stidner_widget_address_updated',
			];
			wp_localize_script(Init::PREFIX.'_script', Init::PREFIX, $js_data);
		}
	}

	/**
	 * Get instance of Stidner api helper
	 *
	 * @return Stidner
	 */
	public static function api()
	{
		if (self::$stidner_api instanceof Stidner) {
			return self::$stidner_api;
		}

		return new Stidner(
			self::$shipping_options['merchant_id'], self::$shipping_options['api_key'], false
		);
	}

	/**
	 * @param $url
	 *
	 * @return false|string
	 */
	public static function setCheckoutUrl($url)
	{
		return (self::isShippingEnabled()) ? self::getCheckoutUrl() : $url;
	}

	/**
	 * @return false|string
	 */
	public static function getCheckoutUrl()
	{
		return wc_get_cart_url();
	}

	/**
	 * Standard woocommerce filter
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	public static function addStidnerShippingMethod($methods)
	{
		$methods[] = 'Stidner\Woocommerce\Shipping';

		return $methods;
	}

	/**
	 * @param $methods
	 *
	 * @return array
	 */
	public static function addPaymentGateways($methods)
	{
		$methods[] = 'Stidner\Woocommerce\Gateways\Payson\WcGatewayPaysonCheckout';

		return $methods;
	}

	/**
	 * @param $template
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 * @return string
	 */
	public static function changeCartTemplate($template, $template_name, $args, $template_path)
	{
		$woocommerce = WC();
		$_template   = $template;
		if (! $template_path) {
			$template_path = $woocommerce->template_url;
		}
		$plugin_path = self::$root_dir.'templates/';
		$template    = locate_template(
			[
				$template_path.$template_name,
				$template_name,
			]
		);

		if (! $template && file_exists($plugin_path.$template_name)) {
			$template = $plugin_path.$template_name;
		}

		if (! $template) {
			$template = $_template;
		}

		return $template;
	}

	/**
	 * Check for necessary plugins on activation
	 */
	public static function activate()
	{

		if(!isset($_GET['activate'])){
			return;
		}

		$dependencies = [
			'Woocommerce' => 'woocommerce/woocommerce.php',
		];

		foreach ($dependencies as $dependency_name => $dependency_path) {
			$rule    = is_admin() && current_user_can('activate_plugins') && ! is_plugin_active($dependency_path);
			$message = sprintf('Sorry, but "Stidner" requires the %s to be installed and active.', $dependency_name);
			if ($rule) {
				self::displayNotice($message);
				deactivate_plugins(plugin_basename('woocommerce-shipping-stidner/stidner.php'));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			}
		}

		//TODO test register default options

		$payson_opt = get_option('woocommerce_paysoncheckout_settings');

		if(empty($payson_opt)){
			update_option('woocommerce_paysoncheckout_settings', [
				'merchant_id' => '4',
				'api_key'     => '2acab30d-fe50-426f-90d7-8c60a7eb31d4',
				'testmode'    => 'yes',
				'show_terms'  => 'no'
			]);
		}

		$stidner_opt = get_option('woocommerce_'.Init::PREFIX.'_settings');

		if(empty($stidner_opt)){
			update_option('woocommerce_'.Init::PREFIX.'_settings', [
				'step_1' => 'Steg 1. Se över er order',
				'step_2' => 'Steg 2. Välj frakt',
				'step_3' => 'Steg 3. Betalning',
			]);
		}
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return \WC_Order
	 * @throws \Exception
	 */
	public static function getWcOrderByStidnerId($key, $value)
	{

		$posts = get_posts(
			[
				'posts_per_page' => 1,
				'meta_query'     => [
					[
						'key'     => $key,
						'value'   => $value,
						'compare' => '=',
					],
				],
			]
		);

		if (! $posts[0] instanceof \WC_Order) {
			throw new \Exception('Order not found');
		}

		return $posts[0];
	}
}


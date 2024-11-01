<?php
/**
 * WooCommerce Stidner Shipping - Payson Checkout
 *
 * @link https://www.stidner.com/
 * @since 1.0
 *
 * @package WC_Gateway_Stidner_Payson
 *
 * @wordpress-plugin
 * Plugin Name:     Stidner Shipping - Payson Checkout
 * Plugin URI:      https://wordpress.org/plugins/stidner-shipping-payson-checkout/
 * Description:     Provides <a href="https://www.stidner.com" target="_blank">Stidner</a> Shipping alongside <a href="https://www.payson.se" target="_blank">PaysonCheckout</a> in WooCommerce.
 * Version:         1.3.7
 * Author:          Stidner Complete AB
 * Author URI:      https://www.stidner.com/
 * Developer:       Stidner Complete AB
 * Developer URI:   https://documentation.stidner.com/
 * Text Domain:     woocommerce-stidner-payson
 * Domain Path:     /languages
 * Copyright:       © 2016-2018 Stidner Complete AB.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

if (! defined('ABSPATH')) {
    exit;
}

require plugin_dir_path(__FILE__).'/lib/vendor/autoload.php';

add_action('init', function () {

    new Stidner\Init(plugin_dir_path(__FILE__), plugin_dir_url(__FILE__));
});

add_action('woocommerce_after_order_itemmeta', function ($item_id, $item, $product) {
    $wc_order = wc_get_order($_GET['post']);

    if (! $product instanceof WC_Product) {
        return;
    }

    if (! $wc_order instanceof WC_Order) {
        return;
    }

    $stidner_order = \Stidner\Woocommerce\OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

    if (! $stidner_order instanceof \Stidner\Api\Stidner\Objects\Order) {
        return;
    }

    if (! $stidner_order->getPackages()) {
        return;
    }

    $item_sku = ($product->get_sku()) ? $product->get_sku() : $product->get_id();

    foreach ($stidner_order->getPackages() as $packages) {
        if ($packages->getDirection() == 'return') {
            continue;
        }
        foreach ($packages->getItems() as $item_uuid) {
            foreach ($stidner_order->getItems() as $_item) {
                if ($_item->getUuid() == $item_uuid) {
                    if ($_item->getArticleNumber() == $item_sku) {
                        echo '<div class="stidner-shipped-label">'.__('Shipped', \Stidner\Init::TEXT_DOMAIN).'</div>';
                    }
                }
            }
        }
    }
}, 10, 3);

add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text, $email) {
    $wc_order = wc_get_order($order);

    if (! $wc_order instanceof WC_Order) {
        return;
    }

    $stidner_order = \Stidner\Woocommerce\OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

    if (! $stidner_order instanceof \Stidner\Api\Stidner\Objects\Order) {
        return;
    }

    foreach ($stidner_order->getPackages() as $package) : ?>

        <div>
            <a href="<?php echo $package->getTrackingUrl() ?>"><?php _e('Track your package', \Stidner\Init::TEXT_DOMAIN) ?></a>
        </div>

    <?php endforeach;
}, 10, 4);

add_filter('woocommerce_order_get_tax_totals', function ($tax_total, $order) {

    $stidner_order = \Stidner\Woocommerce\OrderActions::getStidnerOrderFromMeta($order->get_id());

    if (! $stidner_order instanceof \Stidner\Api\Stidner\Objects\Order) {
        return $tax_total;
    }

    $total = $stidner_order->getShippingPrice();
    if ($total == 0) {
        $shipping_tax = 0;
    } else {
        $shipping_tax = $total/100*0.20; // Price giving included tax, get value of just 25% VAT (TODO: make better)
    }

    foreach ($tax_total as $i => $tax) {
        $rounded = round($tax_total[$i]->amount, 2);
        $tax_total[$i]->amount += $shipping_tax;
        $tax_total[$i]->formatted_amount = str_replace($rounded, round($tax_total[$i]->amount, 2), $tax_total[$i]->formatted_amount);
    }

    return $tax_total;
}, 10, 2);

add_action('in_admin_footer', function () {
    if (is_admin() and isset($_GET['section']) and $_GET['section'] == 'stidner') {
        $options = get_option('woocommerce_'.\Stidner\Init::PREFIX.'_settings');

        if ((! isset($options['merchant_id']) or $options['merchant_id'] == '') or (! isset($options['api_key']) or $options['api_key'] == '')) {
            $email = wp_get_current_user()->user_email;
            ?>
            <script>
                jQuery(document).ready(function ($) {

                    $(".form-table").append(
                        '<iframe width="400" ' +
                        'height="320" ' +
                        'src="https://dashboard.stidner.com/auth/register/external?email=<?php echo $email ?>&amp;website=<?php echo get_home_url() ?>"' +
                        ' id="stidner_iframe"></iframe>'
                    );

                    if ($(window).width() > 1200) {
                        $(".form-table").css({position: 'relative'});
                        $("#stidner_iframe").css({
                            position: 'absolute',
                            top: 0,
                            right: 20
                        })
                    }

                });
            </script>

            <?php
        }
    }
});

add_action('plugins_loaded', 'stidner_load_textdomain');
function stidner_load_textdomain()
{
    load_plugin_textdomain(Stidner\Init::TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)).'/languages/');
}

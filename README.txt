=== Woocommerce Stidner Shipping - Payson Checkout ===
Tags: ecommerce, e-commerce, woocommerce, stidner, shipping, payson, paysoncheckout, frakt
Requires PHP: 5.5; 7.0+ suggested
Requires at least: 4.3
Tested up to: 5.0.1
Requires WooCommerce at least: 3.0
Tested WooCommerce up to: 3.5.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Stable Tag: 1.3.7



== DESCRIPTION ==

Stidner Shipping/Payson Checkout is a plugin that extends WooCommerce, allowing your customers to use Stidner Shipping, and pay with Payson Checkout.

Let your customers choose their shipping option, delivery point, and pay with the convenience of PaysonCheckout.


== FIRST-TIME INSTALLATION ==

1. Download the latest release zip file.
2. If you use the WordPress plugin uploader to install this plugin, skip to step 4.
3. Unzip the plugin and upload the entire plugin directory to your /wp-content/plugins/ directory.
4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
5. Go to WP Admin --> WooCommerce --> Settings --> Shipping --> Stidner, and configure your Stidner Shipping settings.
5. Go to WP Admin --> WooCommerce --> Settings --> Checkout --> Payson, and configure your PaysonCheckout settings.
6. In WP Admin --> WooCommerce --> Settings --> General tab, make sure the "Enable Taxes" feature is enabled.
7. In WP Admin --> WooCommerce --> Settings --> Tax tab, keep all settings on default.
8. In this tax tab, go to the "Standard Rates" tab and set the tax rate for all countries (you can use "*" wildcard under Country Code).
9. Woocommerce's Cart page should automatically display Stidner Shipping and Payson Checkout. If not, make sure Woocommerce's shortcodes are set up properly. If still having trouble, copy the files from this plugins templates into your theme's templates.


== UPDATING ==

If you are updating to a newer version of the plugin, simply follow steps 1, 2, and 3 above. Alternatively, simply check for updates inside Wordpress. Done!

== Changelog ==

= 1.3.7 - 2018-12-17 =
* Send correct product dimensions

= 1.3.6 - 2018-07-05 =
* Support terms checkbox
* Translatable shipping name

= 1.3.5 - 2018-05-30 =
* Further improved theme compatibility

= 1.3.4 - 2018-05-29 =
* Improve compatibility with some themes

= 1.3.3 - 2018-03-28 =
* Further compatibility improvements with multi-currency plugins

= 1.3.2 - 2018-03-14 =
* Fix rare case where checkout would not automatically reload

= 1.3.1 - 2018-03-09 =
* Fix Stidner API hostname

= 1.3.0 - 2018-03-09 =
* Upgraded to the latest version of Stidner's API
* Improved compatibility with multi-currency/language plugins

= 1.2.6 - 2018-01-24 =
* Fix - Improve coupon code accuracy

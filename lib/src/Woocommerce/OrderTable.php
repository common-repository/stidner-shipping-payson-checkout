<?php

namespace Stidner\Woocommerce;

use Stidner\Api\Stidner\Objects\Order;
use Stidner\Api\Stidner\Objects\PackageRequest;
use Stidner\Init;

/**
 * Class OrderTable
 * @package Stidner\Woocommerce
 */
class OrderTable
{

    /**
     * OrderTable constructor.
     */
    public function __construct()
    {
        self::packageNotices();

        if (isset($_GET['post'])) {
            $wc_order = wc_get_order($_GET['post']);
            
            if ($wc_order instanceof \WC_Order) {
                $stidner_order = OrderActions::getStidnerOrderFromMeta($wc_order->get_id());
                if ($stidner_order instanceof Order) {
                    add_action('add_meta_boxes', function () {
                        add_meta_box(
                            'woocommerce-stidner-data',
                            __('Stidner Shipping details', Init::TEXT_DOMAIN),
                            [$this, 'orderPage'],
                            'shop_order',
                            'normal',
                            'high'
                        );
                    });
                }
            }
        }


    }

    /**
     * Show notice after package creation
     */
    public static function packageNotices()
    {

        if (get_option('stidner_admin_notice_error')) {
            if (is_array(get_option('stidner_admin_notice_error'))) {
                foreach (get_option('stidner_admin_notice_error') as $notice) {
                    Init::displayNotice(
                        $notice

                    );
                }
            } else {
                Init::displayNotice(
                    get_option('stidner_admin_notice_error')
                );
            }
            delete_option('stidner_admin_notice_error');
        }
        if (get_option('stidner_admin_notice_success')) {
            if (is_array(get_option('stidner_admin_notice_success'))) {
                foreach (get_option('stidner_admin_notice_success') as $notice) {
                    Init::displayNotice(
                        $notice,
                        'success'
                    );
                }
            } else {
                Init::displayNotice(
                    get_option('stidner_admin_notice_success'),
                    'success'
                );
            }

            delete_option('stidner_admin_notice_success');
        }
    }

    /**
     *
     */
    public static function addressNotices()
    {
        if (!isset($_GET['address_changed'])) {
            return;
        }

        switch ($_GET['address_changed']) {
            case 'success':
                Init::displayNotice(__('Address is successfully updated', Init::TEXT_DOMAIN), 'success');
                break;
            case 'error':
                Init::displayNotice(__('You cannot update address for this order', Init::TEXT_DOMAIN), 'error');
                break;
        }

        return;


    }

    /**
     * @param $column
     * @param $wc_order_id
     */
    public static function columnContent($column, $wc_order_id)
    {
        if ($column != Init::WOOCOMMERCE_COLUMN_SLUG) {
            return;
        }

        $stidner_order = OrderActions::getStidnerOrderFromMeta($wc_order_id);

        if (!$stidner_order instanceof Order) {
            return;
        }

        $wc_order = wc_get_order($wc_order_id);

        $is_order_completed =
            $stidner_order->getShippingStatus() == Order::SHIPPING_STATUS_SHIPPED and $wc_order->has_status('completed');

        if ($is_order_completed) {
            $wc_order->update_status('completed', 'Stidner Status');
        }

        if (!$stidner_order instanceof Order) {
            return;
        }

        $shipment_status = ($stidner_order->getShippingStatus() == Order::SHIPPING_STATUS_NOT_SHIPPED)
            ? __('Pending', Init::TEXT_DOMAIN)
            : ucfirst(str_replace('_', ' ', $stidner_order->getShippingStatus()));

        $shipping_option = $stidner_order->getSelectedShippingOption();
        $message = '';

        $type_handle = $shipping_option->getType();


        $type_name = self::getTypeString($type_handle);

        $message .= '<div class="stidner-info">' .
            '<b>' . __('Type', Init::TEXT_DOMAIN) . ':</b> ' .
            ucfirst(str_replace('_', ' ', $type_name)) .
            '<br><hr>';

        if ($shipping_option->getCarrierLogo()) {
            $message .= '<img src="' . $shipping_option->getCarrierLogo() . '">' .
                '<br><hr>';
        }

        $message .= '<b>' . __('Status', Init::TEXT_DOMAIN) . ':</b> ' .
            __($shipment_status, Init::TEXT_DOMAIN) .
            '<br><hr>';


        if (self::hasPackage($stidner_order)) {
            foreach ($stidner_order->getPackages() as $package) {
                $message .= ($package->getDirection() != 'shipment') ?
                    '<b>' . __('Direction',
                        Init::TEXT_DOMAIN) . ':</b><br>' . ucfirst($package->getDirection()) . '<br>'
                    : '';

                if ($package->getShipmentNumber()) {
                    $message .= '<b>' . __('Tracking number',
                            Init::TEXT_DOMAIN) . ':</b><br><a target="_blank" href="' .
                        $package->getTrackingUrl() . '">' .
                        $package->getShipmentNumber() .
                        '</a><br>';
                }

                $pickup_status = $package->getPickupStatus();


                $pickup_status_string = self::getStatusString($pickup_status);

                $message .= "<b>" . __('Pickup status',
                        Init::TEXT_DOMAIN) . ":</b><br> " . $pickup_status_string . '<br>';

                if ($package->getPickupIdentifier() != null) {
                    $message .= "<b>" . __('Pickup identifier',
                            Init::TEXT_DOMAIN) . ":</b><br> " . $package->getPickupIdentifier() . '<br>';
                }

                if ($package->getPickupErrors() != null) {
                    $message .= "<b>" . __('Pickup errors',
                            Init::TEXT_DOMAIN) . ":</b><br> " . $package->getPickupErrors() . '<br>';
                }

                $message .= "<b>" . __('Shipped at',
                        Init::TEXT_DOMAIN) . ":</b><br> " . $package->getShippedAt() . '<br><hr>';

            }
        }
        $message .= '</div>';

        echo $message;


    }

    /**
     * @param $type_handle
     *
     * @return string
     */
    private static function getTypeString($type_handle)
    {
        $type_name = $type_handle;
        if ($type_handle === 'address') {
            $type_name = __('Express delivery', Init::TEXT_DOMAIN);

            return $type_name;
        } elseif ($type_handle === 'service_point') {
            $type_name = __('Service point', Init::TEXT_DOMAIN);

            return $type_name;
        } elseif ($type_handle === 'mail') {
            $type_name = __('Mail delivery', Init::TEXT_DOMAIN);

            return $type_name;
        } elseif ($type_handle === 'store_pickup') {
            $type_name = __('Store pickup', Init::TEXT_DOMAIN);

            return $type_name;
        }

        return $type_name;
    }

    /**
     * @param Order $stidner_order
     *
     * @return bool
     */
    private static function hasPackage(Order $stidner_order)
    {
        return !empty($stidner_order->getPackages());
    }

    /**
     * @param $pickup_status
     *
     * @return string
     */
    private static function getStatusString($pickup_status)
    {
        switch ($pickup_status) {
            case null:
                $pickup_status_string = __('Not requested yet', Init::TEXT_DOMAIN);
                break;
            case 'scheduled':
                $pickup_status_string = __('Scheduled', Init::TEXT_DOMAIN);
                break;
            case 'processing':
                $pickup_status_string = __('Processing', Init::TEXT_DOMAIN);
                break;
            case 'fully_processed':
                $pickup_status_string = __('Processed', Init::TEXT_DOMAIN);
                break;
            case 'partially_processed':
                $pickup_status_string = __('Processed with errors', Init::TEXT_DOMAIN);
                break;

            case 'no_pickup':
                $pickup_status_string = __('Pickup not supported', Init::TEXT_DOMAIN);
                break;
            case 'pending':
                $pickup_status_string = __('Not requested yet', Init::TEXT_DOMAIN);
                break;
            case 'requested':
                $pickup_status_string = __('Requested', Init::TEXT_DOMAIN);
                break;
            case 'failure':
                $pickup_status_string = __('Failed', Init::TEXT_DOMAIN);
                break;

            default:
                $pickup_status_string = $pickup_status;
                break;
        }

        return $pickup_status_string;
    }

    /**
     * @param $actions
     *
     * @return array
     */
    public static function orderActions($actions)
    {
        global $the_order;
        $wc_order = wc_get_order($the_order);

        $stidner_order = OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

        if (!$stidner_order instanceof Order) {
            return $actions;
        }

        if (!self::hasPackage($stidner_order) and self::needPackage($stidner_order, $wc_order)) {
            $url = wp_nonce_url(admin_url('admin-ajax.php?action=create_stidner_package&order_id=' . $wc_order->get_id()),
                'create_stidner_package');
            $actions[] = [
                'url'    => $url,
                'name'   => 'Ship order',
                'action' => 'view package'
            ];
        }


        return $actions;
    }

    /**
     * @param Order     $stidner_order
     * @param \WC_Order $wc_order
     *
     * @return bool
     */
    private static function needPackage(Order $stidner_order, \WC_Order $wc_order)
    {
        return $stidner_order->isInfoComplete() and $wc_order->has_status('processing');
    }

    /**
     * Set button icon
     */
    public static function orderTableStyle()
    {
        echo '<style>.view.package::after { content: "\f312";!important; }'
            . '.stidner-info{padding-left:5px;border-left:3px solid #26ac95;}'
            . '.stidner-title{color:#26ac95;}</style>';
    }

    /**
     * @param $columns
     *
     * @return mixed
     */
    public static function columns($columns)
    {
        $columns[Init::WOOCOMMERCE_COLUMN_SLUG] = '<span class="stidner-title">' .
            __('Stidner shipping information', Init::TEXT_DOMAIN)
            . '</span>';

        return $columns;
    }

    /**
     * Send "create package" request
     */
    public static function createStidnerPackage()
    {

        if (!current_user_can('edit_shop_orders')) {
            die("Access denied");
        }

        $items = (isset($_GET['items'])) ? array_count_values($_GET['items']) : [];

        $location = admin_url('/post.php?post=' . $_GET['order_id'] . '&action=edit');

        try {

            $wc_order = wc_get_order(absint($_GET['order_id']));

            $direction = isset($_GET['direction']) ? $_GET['direction'] : 'shipment';

            $stidner_order = OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

            if (!$stidner_order instanceof Order) {
                update_option(
                    'stidner_admin_notice_error',
                    __(
                        'Error creating labels, please see Wordpress log for more information.',
                        Init::TEXT_DOMAIN
                    )
                );
                wp_redirect($location);
                exit;
            }

            switch ($direction) {
                case 'shipment':
                case 'return':
                    Init::api()->createPackage(
                        self::createPackageRequest($direction, $items),
                        $stidner_order->getUuid());
                    update_option(
                        'stidner_admin_notice_success',
                        __(
                            'Labels were successfully created, you can download them from the table below.',
                            Init::TEXT_DOMAIN
                        )
                    );

                    break;
                case 's_r': //shipment+return
                    Init::api()->createPackage(
                        self::createPackageRequest('shipment', $items),
                        $stidner_order->getUuid());

                    Init::api()->createPackage(
                        self::createPackageRequest('return', $items),
                        $stidner_order->getUuid());


                    update_option(
                        'stidner_admin_notice_success',
                        __(
                            'Labels were successfully created, you can download them from the table below.',
                            Init::TEXT_DOMAIN
                        )
                    );

                    break;
                default:
                    update_option(
                        'stidner_admin_notice_error',
                        __(
                            'Error creating labels, please see Wordpress log for more information.',
                            Init::TEXT_DOMAIN
                        )
                    );

                    break;
            }

            $stidner_order = Init::api()->orderGet(
                $stidner_order->getUuid()
            );

            if ($stidner_order->getShippingStatus() == Order::SHIPPING_STATUS_SHIPPED) {
                $wc_order->update_status('completed', 'Stidner Status');
            }

            OrderActions::updateStidnerOrderInMeta(
                $wc_order->get_id(),
                $stidner_order
            );


        } catch (\Exception $e) {
            update_option(
                'stidner_admin_notice_error',
                [
                    __(
                        'Error creating labels, please see Wordpress log for more information.',
                        Init::TEXT_DOMAIN
                    ),
                    $e->getMessage()
                ]
            );
        }

        wp_redirect($location);
        exit;
    }

    /**
     * @param $direction
     * @param $items
     *
     * @return PackageRequest
     */
    private static function createPackageRequest($direction, $items)
    {
        $request = new PackageRequest();
        $request->setPackage(self::setPackage($direction, $items));

        return $request;
    }

    /**
     * @param $direction
     * @param $items
     *
     * @return \stdClass
     */
    private static function setPackage($direction, $items)
    {

        $package = new \stdClass();

        $package->direction = $direction;

        if (!empty($items)) {
            $package->items = $items;
        }

        return $package;
    }

    /**
     * @param $post_ID
     * @param $post_after
     * @param $post_before
     */
    public static function onAddressUpdated($post_ID, $post_after, $post_before)
    {

        $wc_order = wc_get_order($post_ID);

        if (!$wc_order instanceof \WC_Order) {
            return;
        }

        try {
            $stidner_order = OrderActions::getStidnerOrderFromMeta($wc_order->get_id());

            if (!$stidner_order instanceof Order) {
                return;
            }

            if (!empty($stidner_order->getPackages())) {
                update_option(
                    'stidner_admin_notice_error',
                    __('You cannot update address for this order', Init::TEXT_DOMAIN)
                );

                return;
            }

            $stidner_order->setAddresses(
                OrderActions::updateAddress($stidner_order, $wc_order)
            );

            $new_stidner_order = Init::api()->orderUpdate($stidner_order, $stidner_order->getUuid());

            OrderActions::updateStidnerOrderInMeta($wc_order->get_id(), $new_stidner_order);

            update_option(
                'stidner_admin_notice_success',
                __('Address is successfully updated', Init::TEXT_DOMAIN)
            );

        } catch (\Exception $e) {
            update_option(
                'stidner_admin_notice_error', [
                    __('You cannot update address for this order', Init::TEXT_DOMAIN),
                    $e->getMessage()
                ]
            );
        }
    }

    public static function requestPickup()
    {

        if (!isset($_GET['package']) || !isset($_GET['order_id'])) {
            update_option(
                'stidner_admin_notice_error',
                [
                    __('Error booking pickup, please see Wordpress log for more information.', Init::TEXT_DOMAIN),
                    'Error: unset package/order_id'
                ]
            );
            $location = admin_url('edit.php?post_type=shop_order');
            wp_redirect($location);
            exit;
        }

        $package = $_GET['package'];

        $order_id = $_GET['order_id'];

        $location = admin_url('/post.php?post=' . $order_id . '&action=edit');

        $stidner_order = OrderActions::getStidnerOrderFromMeta($order_id);

        if (!$stidner_order instanceof Order) {
            update_option(
                'stidner_admin_notice_error',
                [
                    __('Error booking pickup, please see Wordpress log for more information.', Init::TEXT_DOMAIN),
                    'Error: stidner_order instanceof Order'
                ]
            );
        }

        $data = new \stdClass();

        $data->packages = [$package];

        try {
            Init::api()->requestPickup($data);

            update_option(
                'stidner_admin_notice_success',
                __(
                    'Pickup booking created successfully!',
                    Init::TEXT_DOMAIN
                )
            );

            OrderActions::updateStidnerOrderInMeta(
                $order_id,
                Init::api()->orderGet($stidner_order->getUuid())
            );

        } catch (\Exception $e) {
            update_option(
                'stidner_admin_notice_error',
                [
                    __('Error booking pickup, please see Wordpress log for more information.', Init::TEXT_DOMAIN),
                    $e->getMessage()
                ]
            );
        }

        wp_redirect($location);
        exit;
    }

    /**
     * Content of meta box
     */
    public function orderPage()
    {
        $wc_order = wc_get_order($_GET['post']);

        $stidner_order = \Stidner\Woocommerce\OrderActions::getStidnerOrderFromMeta($wc_order->get_id());
        if (!$stidner_order instanceof Order) {
            return;
        }
        ?>

        <script>
            jQuery(document).ready(function ($) {
                //jQuery("tr.shipping>td.line_tax>div.view").html(
                //    "<?php echo get_woocommerce_currency_symbol(get_woocommerce_currency())?>" +
                //    "<?php echo $this->calculateTax($stidner_order)?>"
                //);
                <?php if(!empty($stidner_order->getPackages())): ?>
                jQuery(".edit_address").unbind('click');
                <?php endif; ?>

                $(".stidner-ship-btn").on('click', function () {

                    $(".stidner-ship-btn").each(function (index) {
                        var href = $(this).attr('href');

                        var items = $(".stidner-ship-item:checked").map(function () {
                            href += ( href.indexOf('?') >= 0 ? '&' : '?' ) + 'items[]=' + $(this).val();
                        }).get();

                        $(this).attr('href', href);
                    });

                });

                $(".stidner-return-btn").on('click', function () {

                    $(".stidner-return-btn").each(function (index) {
                        var href = $(this).attr('href');

                        var items = $(".stidner-return-item:checked").map(function () {
                            href += ( href.indexOf('?') >= 0 ? '&' : '?' ) + 'items[]=' + $(this).val();
                        }).get();

                        $(this).attr('href', href);
                    });

                });


            });


        </script>

        <style>
            .stidner_data_column_container {
                clear: both;
            }

            .stidner_data_column {
                width: 30%;
                padding: 0 2% 0 0;
                float: left;
            }

            .stidner_carrier_logo {
                position: absolute;
                right: 10px;
                top: 0;
            }

            a.button-primary > img {
                margin: 5px 2px 0 2px;
            }

            .stidner-shipped-label {
                padding: 5px 0;
                display: inline-block;
                color: #26ac95;
                font-weight: 600;
            }
        </style>

        <div class="stidner_data_column_container">
            <div class="stidner_data_column">
                <h3><?php _e('Carrier and price', Init::TEXT_DOMAIN) ?></h3>
                <?php echo $this->recipientSectionContent($stidner_order); ?>
                <?php echo $this->carrierSectionContent($stidner_order); ?>
            </div>
            <div class="stidner_data_column">
                <?php $buttons_html = $this->shipOrderButtons($wc_order, $stidner_order) ?>

                <?php if ($buttons_html): ?>
                    <h3><?php _e('Ship order', Init::TEXT_DOMAIN) ?></h3>

                    <?php echo $buttons_html; ?><?php endif; ?>
            </div>
            <div class="stidner_data_column">
                <?php $buttons_html = $this->returnOrderButtons($wc_order, $stidner_order) ?>

                <?php if ($buttons_html): ?>
                    <h3><?php _e('Return order', Init::TEXT_DOMAIN) ?></h3>

                    <?php echo $buttons_html; ?><?php endif; ?>
            </div>
            <div style="clear: both"></div>
        </div>
        <div class="stidner_data_column_container">
            <?php
            $pakket = $stidner_order->getPackages();
            if (!empty($pakket)) {
                echo "<h3>" . __('Labels', Init::TEXT_DOMAIN) . ":</h3>";
                echo $this->printShippingPackages($stidner_order);
            }
            ?>

        </div>

        <?php
    }

    /**
     * @param Order $stidner_order
     *
     * @return float|int
     */
    private function calculateTax(Order $stidner_order)
    {
        $total = $stidner_order->getShippingPrice();
        if ($total == 0) {
            return 0;
        }


        return $total/100*0.20; // TODO: handle 25% VAT better
    }

    /**
     * @param Order $stidner_order
     *
     * @return string
     */
    private function recipientSectionContent(Order $stidner_order)
    {
        $line = '';

        $shipping_option = $stidner_order->getSelectedShippingOption();

        if ($shipping_option->getType() == 'service_point') {
            $service_point = $stidner_order->getSelectedServicePoint();

            $service_point_line = array(
                $service_point->getName(),
                $service_point->getHandle(),
                $service_point->getCountryCode(),
                $service_point->getCity(),
                $service_point->getAddressLine()
            );
            foreach ($service_point_line as $key => $value) {
                if (!$value) {
                    unset($service_point_line[$key]);
                }
            }
            $service_point_line = implode(', ', $service_point_line);

            $line .= "<p><b>" . __('Service Point', Init::TEXT_DOMAIN) . "</b>: " . $service_point_line . "</p>";
        } elseif ($shipping_option->getType() == 'store_pickup') {
            $first_service_point = $shipping_option->getServicePoints()[0];

            $first_service_point_line = array(
                $first_service_point->getName(),
                $first_service_point->getCountryCode(),
                $first_service_point->getPostalCode(),
                $first_service_point->getCity(),
                $first_service_point->getAddressLine()
            );
            foreach ($first_service_point_line as $key => $value) {
                if (!$value) {
                    unset($first_service_point_line[$key]);
                }
            }
            $first_service_point_line = implode(', ', $first_service_point_line);

            $line .= "<p><b>" . __('Pick-up at', Init::TEXT_DOMAIN) . "</b>: " . $first_service_point_line . "</p>";
        }


        return $line;
    }

    /**
     * @param Order $stidner_order
     *
     * @return array|string
     */
    private function carrierSectionContent(Order $stidner_order)
    {
        $line = '';
        $shipping_option = $stidner_order->getSelectedShippingOption();
        $type_handle = $shipping_option->getType();
        $img = "<img src='{$shipping_option->getCarrierLogo()}'>";

        $type_name = $type_handle;
        if ($type_handle === 'address') {
            $type_name = __('Express delivery', Init::TEXT_DOMAIN);
        } elseif ($type_handle === 'service_point') {
            $type_name = __('Service point', Init::TEXT_DOMAIN);
        } elseif ($type_handle === 'mail') {
            $type_name = __('Mail delivery', Init::TEXT_DOMAIN);
        } elseif ($type_handle === 'store_pickup') {
            $type_name = __('Store pickup', Init::TEXT_DOMAIN);
        }

        switch ($stidner_order->getShippingStatus()) {
            case Order::SHIPPING_STATUS_NOT_SHIPPED :
                $translated_shipment_status = __('Pending', Init::TEXT_DOMAIN);
                break;
            case Order::SHIPPING_STATUS_SHIPPED:
                $translated_shipment_status = __('Shipped', Init::TEXT_DOMAIN);
                break;
            default :
                $translated_shipment_status = ucfirst(str_replace('_', ' ', $stidner_order->getShippingStatus()));

        }


        $currency = ($stidner_order->getCurrency() === 'SEK') ? 'kr' : $stidner_order->getCurrency();

        $shipment_price = $stidner_order->getShippingPrice()
            ? $stidner_order->getShippingPrice() / 100 . ' ' . $currency
            : 'Free';

        $line = [
            '<b>' . __('Status', Init::TEXT_DOMAIN) . ':</b> ' . $translated_shipment_status,
            '<b>' . __('Type', Init::TEXT_DOMAIN) . ':</b> ' . $type_name,
            '<b>' . __('Price', Init::TEXT_DOMAIN) . ':</b> ' . $shipment_price,
            $type_handle !== 'store_pickup' ? $img : null,
        ];
        foreach ($line as $key => $value) {
            if (!$value) {
                unset($line[$key]);
            }
        }

        $line = implode('<br>', $line);

        return $line;
    }

    /**
     * @param \WC_Order $wc_order
     * @param Order     $stidner_order
     *
     * @return string
     */
    private function shipOrderButtons(\WC_Order $wc_order, Order $stidner_order)
    {

        $line = '';
        $button_url = wp_nonce_url(
            admin_url(
                'admin-ajax.php?action=create_stidner_package&direction=__direction__&order_id=' . $wc_order->get_id()
            ),
            'create_stidner_package'
        );

        $has_shipping_package = false;
        $has_return_package = false;
        $has_unshipped_items = false;
        $shipped_items = [];
        $returned_items = [];

        foreach ($stidner_order->getPackages() as $package) {
            if ($package->getDirection() === 'shipment') {
                $has_shipping_package = true;
                foreach ($package->getItems() as $key => $value) {
                    $shipped_items[] = $value;
                }
            }
            if ($package->getDirection() === 'return') {
                $has_return_package = true;
                foreach ($package->getItems() as $key => $value) {
                    $returned_items[] = $value;
                }
            }
        }

        foreach ($stidner_order->getItems() as $item) {
            for ($i = 0; $i < $item->getQuantity(); $i++) {
                if (!in_array($item->getUuid(), $shipped_items)) {
                    $line .= '<input checked class="stidner-ship-item" type="checkbox" value="' .
                        $item->getUuid() . '">' . $item->getName() . '<br>';
                    $has_unshipped_items = true;
                }
            }
        }

        $img_ship = Init::$root_url . 'assets/img/package.svg';
        $img_return = Init::$root_url . 'assets/img/box.svg';

        if ($has_unshipped_items) {
            $url = str_replace('__direction__', 'shipment', $button_url);
            $line .= '<p><a class="stidner-ship-btn button-primary" href="' . $url . 'ship"><img width="15" height="15" src="' . $img_ship . '">  ' . __('Ship',
                    Init::TEXT_DOMAIN) . '</a></p>';

            if (!$has_return_package) { // Don't show Return button, if return column should exist to the right
                $url = str_replace('__direction__', 'return', $button_url);
                $line .= '<p><a class="stidner-ship-btn button-primary" href="' . $url . 'return"><img width="15" height="15" src="' . $img_return . '"> ' . __('Return',
                        Init::TEXT_DOMAIN) . '</a></p>';
            }

            $url = str_replace('__direction__', 's_r', $button_url); // Allow ship+return, even if returns already exist
            $line .= '<p><a class="stidner-ship-btn button-primary" href="' . $url . 's_r"><img width="15" height="15" src="' . $img_ship . '"><img width="15" height="15" src="' . $img_return . '">' . __('Shipment + Return',
                    Init::TEXT_DOMAIN) . '</a></p>';
        } else {
            $line .= '<i>' . __('Order is fully shipped', Init::TEXT_DOMAIN) . '</i>';
        }

        return trim($line);

    }

    /**
     * @param \WC_Order $wc_order
     * @param Order     $stidner_order
     *
     * @return string
     */
    private function returnOrderButtons(\WC_Order $wc_order, Order $stidner_order)
    {

        $line = '';
        $button_url = wp_nonce_url(
            admin_url(
                'admin-ajax.php?action=create_stidner_package&direction=__direction__&order_id=' . $wc_order->get_id()
            ),
            'create_stidner_package'
        );

        $has_shipping_package = false;
        $has_return_package = false;
        $has_unreturned_items = false;
        $shipped_items = [];
        $returned_items = [];

        //echo var_dump($stidner_order->getPackages(),1);
        foreach ($stidner_order->getPackages() as $package) {
            if ($package->getDirection() === 'shipment') {
                $has_shipping_package = true;
                foreach ($package->getItems() as $key => $value) {
                    $shipped_items[] = $value;
                }
            }
            if ($package->getDirection() === 'return') {
                $has_return_package = true;
                foreach ($package->getItems() as $key => $value) {
                    $returned_items[] = $value;
                }
            }
        }

        foreach ($stidner_order->getItems() as $item) {
            for ($i = 0; $i < $item->getQuantity(); $i++) {
                if (!in_array($item->getUuid(), $returned_items) && in_array($item->getUuid(), $shipped_items)) {
                    $line .= '<input class="stidner-return-item" type="checkbox" value="' .
                        $item->getUuid() . '">' . $item->getName() . '<br>';
                    $has_unreturned_items = true;
                }
            }
        }

        $img_ship = Init::$root_url . 'assets/img/package.svg';
        $img_return = Init::$root_url . 'assets/img/box.svg';

        if ($has_unreturned_items) {
            $url = str_replace('__direction__', 'return', $button_url);
            $line .= '<p><a class="stidner-return-btn button-primary" href="' . $url . 'return"><img width="15" height="15" src="' . $img_return . '"> ' . __('Return',
                    Init::TEXT_DOMAIN) . '</a></p>';
        } elseif ($has_shipping_package) { // Only show message if order has been shipped
            $line .= '<i>' . __('No more items to return', Init::TEXT_DOMAIN) . '</i>';
        }


        return trim($line);

    }

    /**
     * @param Order $stidner_order
     *
     * @return string
     */
    private function printShippingPackages(Order $stidner_order)
    {

        ob_start();


        foreach ($stidner_order->getPackages() as $package) : ?>

            <?php
            switch ($package->getDirection()) {
                case 'shipment':
                    $translated_direction = __('Shipment', Init::TEXT_DOMAIN);
                    break;
                case 'return':
                    $translated_direction = __('Return', Init::TEXT_DOMAIN);
                    break;
                default:
                    $translated_direction = $package->getDirection();

            }

            $pickup_status = $package->getPickupStatus();

            $pickup_status_string = self::getStatusString($pickup_status);

            $request_pickup_url = admin_url(
                'admin-ajax.php?action=stidner_request_pickup&package=' . $package->getUuid() . '&order_id=' . (isset($_GET['post']) ? $_GET['post'] : '')
            );

            ?>

            <b><?php _e('Tracking number', Init::TEXT_DOMAIN) ?>:</b>            <a target="_blank"
                                                                                    href="<?php echo $package->getTrackingUrl() ?>"><?php echo $package->getShipmentNumber(); ?></a>
            <br>            <b><?php _e('Direction', Init::TEXT_DOMAIN) ?>:</b> <?php echo $translated_direction; ?>
            <br>            <b><?php _e('Label url', Init::TEXT_DOMAIN) ?>:</b>            <a target="_blank"
                                                                                                href="<?php echo $package->getLabelUrl(); ?>"><?php _e('Link',
                    Init::TEXT_DOMAIN) ?></a>            <br>            <b><?php _e('Shipped at',
                    Init::TEXT_DOMAIN) ?>
                :</b> <?php echo $package->getShippedAt(); ?>
            <br>            <b><?php _e('Pickup status', Init::TEXT_DOMAIN) ?>
                :</b> <?php echo $pickup_status_string; ?><?php if (is_null($package->getPickupStatus())): ?>
                <br>                <a href="<?php echo $request_pickup_url ?>"><?php _e('Request Pickup',
                        Init::TEXT_DOMAIN) ?></a>
            <?php endif; ?>
            <hr>
        <?php endforeach;

        return ob_get_clean();

    }

}

<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Stidner\\Api\\Stidner\\Objects\\AbstractObject' => $baseDir . '/src/Api/Stidner/Objects/AbstractObject.php',
    'Stidner\\Api\\Stidner\\Objects\\Address' => $baseDir . '/src/Api/Stidner/Objects/Address.php',
    'Stidner\\Api\\Stidner\\Objects\\Agreement' => $baseDir . '/src/Api/Stidner/Objects/Agreement.php',
    'Stidner\\Api\\Stidner\\Objects\\Item' => $baseDir . '/src/Api/Stidner/Objects/Item.php',
    'Stidner\\Api\\Stidner\\Objects\\Marketplace' => $baseDir . '/src/Api/Stidner/Objects/Marketplace.php',
    'Stidner\\Api\\Stidner\\Objects\\Merchant' => $baseDir . '/src/Api/Stidner/Objects/Merchant.php',
    'Stidner\\Api\\Stidner\\Objects\\Order' => $baseDir . '/src/Api/Stidner/Objects/Order.php',
    'Stidner\\Api\\Stidner\\Objects\\Package' => $baseDir . '/src/Api/Stidner/Objects/Package.php',
    'Stidner\\Api\\Stidner\\Objects\\PackageRequest' => $baseDir . '/src/Api/Stidner/Objects/PackageRequest.php',
    'Stidner\\Api\\Stidner\\Objects\\Product' => $baseDir . '/src/Api/Stidner/Objects/Product.php',
    'Stidner\\Api\\Stidner\\Objects\\ServicePoint' => $baseDir . '/src/Api/Stidner/Objects/ServicePoint.php',
    'Stidner\\Api\\Stidner\\Objects\\ShippingOption' => $baseDir . '/src/Api/Stidner/Objects/ShippingOption.php',
    'Stidner\\Api\\Stidner\\Objects\\TrackingEvent' => $baseDir . '/src/Api/Stidner/Objects/TrackingEvent.php',
    'Stidner\\Api\\Stidner\\Objects\\User' => $baseDir . '/src/Api/Stidner/Objects/User.php',
    'Stidner\\Api\\Stidner\\Objects\\UserGrant' => $baseDir . '/src/Api/Stidner/Objects/UserGrant.php',
    'Stidner\\Api\\Stidner\\Stidner' => $baseDir . '/src/Api/Stidner/Stidner.php',
    'Stidner\\Config' => $baseDir . '/src/Config.php',
    'Stidner\\Exceptions\\AdminException' => $baseDir . '/src/Exceptions/AdminException.php',
    'Stidner\\Exceptions\\ApiException' => $baseDir . '/src/Exceptions/ApiException.php',
    'Stidner\\Exceptions\\InvalidCredentialsException' => $baseDir . '/src/Exceptions/InvalidCredentialsException.php',
    'Stidner\\Exceptions\\UserException' => $baseDir . '/src/Exceptions/UserException.php',
    'Stidner\\Hooks' => $baseDir . '/src/Hooks.php',
    'Stidner\\Init' => $baseDir . '/src/Init.php',
    'Stidner\\Woocommerce\\CheckoutActions' => $baseDir . '/src/Woocommerce/CheckoutActions.php',
    'Stidner\\Woocommerce\\CheckoutPage' => $baseDir . '/src/Woocommerce/CheckoutPage.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\Account' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/Account.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\Checkout' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/Checkout.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\CurrencyCode' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/CurrencyCode.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\Customer' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/Customer.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\Gui' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/Gui.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\Merchant' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/Merchant.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\OrderItem' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/OrderItem.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\OrderItemType' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/OrderItemType.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\PayData' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/PayData.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\PaysonApi' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/PaysonApi.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\PaysonApiError' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/PaysonApiError.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\Lib\\PaysonApiException' => $baseDir . '/src/Woocommerce/Gateways/Payson/Lib/PaysonApiException.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\WcGatewayPaysonCheckout' => $baseDir . '/src/Woocommerce/Gateways/Payson/WcGatewayPaysonCheckout.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\WcPaysonCheckoutProcessOrderLines' => $baseDir . '/src/Woocommerce/Gateways/Payson/WcPaysonCheckoutProcessOrderLines.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\WcPaysonCheckoutResponseHandler' => $baseDir . '/src/Woocommerce/Gateways/Payson/WcPaysonCheckoutResponseHandler.php',
    'Stidner\\Woocommerce\\Gateways\\Payson\\WcPaysonCheckoutSetupPaysonAPI' => $baseDir . '/src/Woocommerce/Gateways/Payson/WcPaysonCheckoutSetupPaysonAPI.php',
    'Stidner\\Woocommerce\\OrderActions' => $baseDir . '/src/Woocommerce/OrderActions.php',
    'Stidner\\Woocommerce\\OrderTable' => $baseDir . '/src/Woocommerce/OrderTable.php',
    'Stidner\\Woocommerce\\Shipping' => $baseDir . '/src/Woocommerce/Shipping.php',
    'Stidner\\Woocommerce\\WcOrderActions' => $baseDir . '/src/Woocommerce/WcOrderActions.php',
);

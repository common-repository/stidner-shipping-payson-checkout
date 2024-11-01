<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="woocommerce-order">

	<?php if ( $order ) : ?>

		<h3><?php _e( 'Order Received - №', \Stidner\Init::TEXT_DOMAIN ) ?><?php echo ': ' . $order->get_id() ?></h3>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php endif; ?>

</div>

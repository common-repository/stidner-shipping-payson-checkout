<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.1.0
 */

if (! defined('ABSPATH')) {
	exit;
}

wc_print_notices();

do_action('woocommerce_before_cart');

?>

<style>
	.stidner-table {
		border-collapse: collapse;
		margin: 0 0 1.5em;
		width: 100%;
	}

	.stidner-table > thead > tr {
		border-bottom: 1px solid #eee;
	}

	.stidner-table > thead > tr > th {
		border-bottom: 2px solid #bbb;
		padding-bottom: 0.5em;
	}

	.stidner-table > thead > tr > th {
		border-bottom: 2px solid #bbb;
		padding-bottom: 0.5em;
		text-align: left;
	}

	.stidner-table > thead > tr > th > th:first-child {
		padding-left: 0;
	}


</style>

<div class="stidner row">
	<form class="woocommerce-cart-form stidner-woocommerce-cart-form col-xs-12 col-sm-12 col-md-12 col-lg-12"
	      action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
		<div class="checkout-step">
			<h2><?php echo \Stidner\Init::getOptions()['step_1']; ?></h2>
		</div>
		<?php do_action('woocommerce_before_cart_table'); ?>
		<table class="stidner-table shop_table shop_table_responsive cart woocommerce-cart-form__contents"
		       cellspacing="0">
			<thead>
			<tr>
				<th class="product-remove">&nbsp;</th>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name"><?php _e('Product', 'woocommerce'); ?></th>
				<th class="product-price"><?php _e('Price', 'woocommerce'); ?></th>
				<th class="product-quantity"><?php _e('Quantity', 'woocommerce'); ?></th>
				<th class="product-subtotal"><?php _e('Total', 'woocommerce'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				$_product   = apply_filters(
					'woocommerce_cart_item_product',
					$cart_item['data'],
					$cart_item,
					$cart_item_key
				);
				$product_id = apply_filters(
					'woocommerce_cart_item_product_id',
					$cart_item['product_id'],
					$cart_item,
					$cart_item_key
				);

				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters(
						'woocommerce_cart_item_visible',
						true,
						$cart_item,
						$cart_item_key
					)
				) {
					$product_permalink = apply_filters(
						'woocommerce_cart_item_permalink',
						$_product->is_visible() ? $_product->get_permalink(
							$cart_item
						) : '',
						$cart_item,
						$cart_item_key
					);
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(
						apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)
					); ?>">

						<td class="product-remove">
							<?php
							echo apply_filters(
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
									esc_url(WC()->cart->get_remove_url($cart_item_key)),
									__('Remove this item', 'woocommerce'),
									esc_attr($product_id),
									esc_attr($_product->get_sku())
								),
								$cart_item_key
							);
							?>
						</td>

						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters(
								'woocommerce_cart_item_thumbnail',
								$_product->get_image(),
								$cart_item,
								$cart_item_key
							);

							if (! $product_permalink) {
								echo $thumbnail;
							} else {
								printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
							}
							?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
							<?php
							if (! $product_permalink) {
								echo apply_filters(
										'woocommerce_cart_item_name',
										$_product->get_name(),
										$cart_item,
										$cart_item_key
									).'&nbsp;';
							} else {
								echo apply_filters(
									'woocommerce_cart_item_name',
									sprintf(
										'<a href="%s">%s</a>',
										esc_url($product_permalink),
										$_product->get_name()
									),
									$cart_item,
									$cart_item_key
								);
							}

							// Meta data
							echo WC()->cart->get_item_data($cart_item);

							// Backorder notification
							if ($_product->backorders_require_notification() && $_product->is_on_backorder(
									$cart_item['quantity']
								)
							) {
								echo '<p class="backorder_notification">'.esc_html__(
										'Available on backorder',
										'woocommerce'
									).'</p>';
							}
							?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
							<?php
							echo apply_filters(
								'woocommerce_cart_item_price',
								WC()->cart->get_product_price($_product),
								$cart_item,
								$cart_item_key
							);
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
							<?php
							if ($_product->is_sold_individually()) {
								$product_quantity = sprintf(
									'1 <input type="hidden" name="cart[%s][qty]" value="1" />',
									$cart_item_key
								);
							} else {
								$product_quantity = woocommerce_quantity_input(
									[
										'input_name'  => "cart[{$cart_item_key}][qty]",
										'input_value' => $cart_item['quantity'],
										'max_value'   => $_product->get_max_purchase_quantity(),
										'min_value'   => '0',
									],
									$_product,
									false
								);
							}

							echo apply_filters(
								'woocommerce_cart_item_quantity',
								$product_quantity,
								$cart_item_key,
								$cart_item
							);
							?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
							<?php
							echo apply_filters(
								'woocommerce_cart_item_subtotal',
								WC()->cart->get_product_subtotal($_product, $cart_item['quantity']),
								$cart_item,
								$cart_item_key
							);
							?>
						</td>
					</tr>
					<?php
				}
			}
			?>
			<?php do_action('woocommerce_cart_contents'); ?>
			<tr>
				<td colspan="6" class="actions">
					<?php if (wc_coupons_enabled()) { ?>
						<div class="coupon">
							<label for="coupon_code"><?php _e('Coupon:', 'woocommerce'); ?></label>
							<input type="text"
							       name="coupon_code"
							       class="input-text"
							       id="coupon_code"
							       value=""
							       placeholder="<?php esc_attr_e(
								       'Coupon code',
								       'woocommerce'
							       ); ?>"/>
							<input type="submit" class="button" name="apply_coupon"
							       value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"/>
							<?php do_action('woocommerce_cart_coupon'); ?>
						</div>
					<?php } ?>

					<input type="submit" class="button" name="update_cart"
					       value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"/>

					<?php do_action('woocommerce_cart_actions'); ?>

					<?php wp_nonce_field('woocommerce-cart'); ?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php do_action('woocommerce_after_cart_table'); ?>
                
	</form>
    
        <?php if ('yes' == \Stidner\Init::getPaymentOptions()['show_terms']): ?>
            <div id="payment" class="woocommerce-checkout-payment stidner-payson-checkout-payment">
                <div class="form-row place-order stidner-payson-place-order">
                    <?php wc_get_template( 'checkout/terms.php' ); ?>
                </div>
            </div>
        <?php endif ?>
        
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 stidner-payson-iframe-container hide-stidner-payson-iframe-container">
		<?php
		try {
			$shipping_section = \Stidner\Woocommerce\CheckoutPage::shippingSection();
			$payment_section  = \Stidner\Woocommerce\CheckoutPage::paymentSection();
			?>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<div class="checkout-step">
					<h2><?php echo \Stidner\Init::getOptions()['step_2']; ?></h2>
				</div>
				<?php
				echo $shipping_section->getWidgetEmbed();
				?>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<div class="checkout-step">
					<h2><?php echo \Stidner\Init::getOptions()['step_3']; ?></h2>
				</div>
				<?php
				echo $payment_section->snippet;
				?>
			</div>

			<?php WC()->session->set('old_payson_id', null); ?>
			<?php WC()->session->set('new_payson_id', null); ?>

			<?php
		} catch (Stidner\Exceptions\InvalidCredentialsException $e) {
			echo $e->getMessage();
		} catch (Exception $e) {
			echo '<div class="woocommerce-error">'.$e->getMessage().'</div>';
		}
		?>
	</div>
	<div class="cart-collaterals col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<?php
		do_action('woocommerce_cart_collaterals');
		?>
	</div>
</div>


<?php do_action('woocommerce_after_cart'); ?>

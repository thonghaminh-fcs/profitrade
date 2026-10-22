<?php
/**
 * Checkout Form — AI Zippy Override
 *
 * Card-based layout matching the React checkout design.
 * Numbered sections: 1. Contact & Billing, 2. Order Summary, 3. Payment
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout az-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<div class="zc-steps" aria-label="<?php esc_attr_e( 'Checkout progress', 'ai-zippy' ); ?>">
			<div class="zc-steps__item is-done">
				<span class="zc-steps__num">1</span>
				<span class="zc-steps__label"><?php esc_html_e( 'Shopping cart', 'ai-zippy' ); ?></span>
				<span class="zc-steps__line" aria-hidden="true"></span>
			</div>
			<div class="zc-steps__item is-active" aria-current="step">
				<span class="zc-steps__num">2</span>
				<span class="zc-steps__label"><?php esc_html_e( 'Checkout details', 'ai-zippy' ); ?></span>
				<span class="zc-steps__line" aria-hidden="true"></span>
			</div>
			<div class="zc-steps__item">
				<span class="zc-steps__num">3</span>
				<span class="zc-steps__label"><?php esc_html_e( 'Order complete', 'ai-zippy' ); ?></span>
			</div>
		</div>

		<div class="az-checkout__layout">

			<!-- LEFT COLUMN: Form sections -->
			<div class="az-checkout__form">

				<!-- 1. Contact & Billing -->
				<div class="az-checkout__card">
					<div class="az-checkout__card-header">
						<span class="az-checkout__card-num">1.</span>
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Contact & Billing', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
					</div>
				</div>

				<!-- 2. Additional Information -->
				<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
				<div class="az-checkout__card">
					<div class="az-checkout__card-header">
						<span class="az-checkout__card-num">2.</span>
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Additional information', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
						<?php if ( $checkout->get_checkout_fields( 'order' ) ) : ?>
							<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
								<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- RIGHT COLUMN: Order summary -->
			<div class="az-checkout__sidebar">
				<div class="az-checkout__card az-checkout__card--sticky">
					<div class="az-checkout__card-header">
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Order summary', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
						<div class="az-checkout__items">
							<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
								$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
							?>
							<div class="az-checkout__item" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
								<div class="az-checkout__item-img">
									<?php echo wp_kses_post( $_product->get_image( 'woocommerce_thumbnail' ) ); ?>
								</div>
								<div class="az-checkout__item-detail">
									<div class="az-checkout__item-top">
										<span class="az-checkout__item-name">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
										</span>
										<span class="az-checkout__item-total">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
										</span>
									</div>
									<div class="az-checkout__item-bottom">
										<span class="az-checkout__item-meta"><?php printf( esc_html__( 'Quantity : %s', 'ai-zippy' ), esc_html( $cart_item['quantity'] ) ); ?></span>
										<div class="az-checkout__item-qty">
											<button type="button" class="az-checkout__qty-btn az-checkout__qty-btn--minus" aria-label="<?php esc_attr_e( 'Decrease', 'ai-zippy' ); ?>">−</button>
											<span class="az-checkout__qty-val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
											<button type="button" class="az-checkout__qty-btn az-checkout__qty-btn--plus" aria-label="<?php esc_attr_e( 'Increase', 'ai-zippy' ); ?>">+</button>
										</div>
									</div>
								</div>
							</div>
							<?php endif; endforeach; ?>
						</div>

						<?php
						// EPOS CRM's membership-point widget defaults to
						// woocommerce_before_checkout_form (outside <form>,
						// above the step indicator) — moved here, next to
						// the coupon/voucher block it belongs with visually,
						// by calling it directly instead of through that hook.
						// See CheckoutAssets::removeEposPointInformationDefaultHook().
						// Buffered so the wrapper (with its divider/spacing)
						// only prints when the widget actually has something
						// to show — render_point_information() echoes nothing
						// at all when the customer isn't an EPOS member,
						// isn't SSO-logged-in, or has a zero point balance.
						$az_points_html = '';
						if ( class_exists( '\EPOS_CRM\Src\Web\Epos_Crm_Web' ) ) {
							ob_start();
							\EPOS_CRM\Src\Web\Epos_Crm_Web::get_instance()->render_point_information();
							$az_points_html = trim( ob_get_clean() );
						}
						if ( $az_points_html !== '' ) :
							?>
							<div class="az-checkout__points">
								<?php echo $az_points_html; // phpcs:ignore WordPress.Security.EscapeOutput -- plugin-rendered markup, already escaped by EPOS CRM's own template. ?>
							</div>
						<?php endif; ?>

						<!-- Coupon -->
						<?php if ( wc_coupons_enabled() ) : ?>
						<div class="az-checkout__coupon">
							<div class="az-checkout__coupon-row">
								<input type="text" name="az_coupon_code" class="az-checkout__coupon-input" placeholder="<?php esc_attr_e( 'Coupon code', 'ai-zippy' ); ?>" id="az-coupon-code" />
								<button type="button" class="az-checkout__coupon-btn" id="az-apply-coupon"><?php esc_html_e( 'Apply', 'ai-zippy' ); ?></button>
							</div>
							<div class="az-checkout__coupon-msg" id="az-coupon-msg"></div>
						</div>
						<?php endif; ?>

						<div class="az-checkout__totals" id="az-checkout-totals">
							<?php \AiZippy\Checkout\CheckoutAssets::renderTotals(); ?>
						</div>

						<!-- Payment & Place Order -->
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
					</div>
				</div>
			</div>

		</div>

	<?php endif; ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
// ---- Loading overlays (custom spinner, in place of jQuery blockUI's plain
// grey box) ----
//
// azShowLoading(el)   — pins a small spinner over one element (e.g. the
//                        totals block while it recalculates).
// azShowFullLoading()  — pins a spinner over the whole viewport and locks
//                        page scroll, for the place-order submit: the
//                        customer shouldn't be able to scroll away from (or
//                        re-trigger) a payment that's already in flight.
var azShowLoading = function(el) {
	if (!el || el.querySelector(':scope > .az-loading-overlay')) return;
	if (getComputedStyle(el).position === 'static') {
		el.classList.add('az-loading-anchor');
	}
	var overlay = document.createElement('div');
	overlay.className = 'az-loading-overlay';
	overlay.innerHTML = '<span class="az-loading-spinner" aria-hidden="true"></span>';
	overlay.setAttribute('role', 'status');
	overlay.setAttribute('aria-label', '<?php echo esc_js( __( 'Loading…', 'ai-zippy' ) ); ?>');
	el.appendChild(overlay);
};

var azHideLoading = function(el) {
	if (!el) return;
	var overlay = el.querySelector(':scope > .az-loading-overlay');
	if (overlay) overlay.remove();
	el.classList.remove('az-loading-anchor');
};

var azShowFullLoading = function(message) {
	if (document.querySelector('.az-loading-overlay--full')) return;
	var overlay = document.createElement('div');
	overlay.className = 'az-loading-overlay az-loading-overlay--full';
	overlay.setAttribute('role', 'status');
	overlay.setAttribute('aria-label', message || '<?php echo esc_js( __( 'Loading…', 'ai-zippy' ) ); ?>');
	overlay.innerHTML = '<span class="az-loading-spinner" aria-hidden="true"></span>';
	document.body.appendChild(overlay);
	document.body.classList.add('az-scroll-locked');
};

// ---- Totals refresh (shared by coupon apply/remove and address changes) ----
// WC()->customer's address only reflects what's already saved (page load or
// last order); typing into the form doesn't touch it until this posts the
// current field values, same as WooCommerce's own update_order_review AJAX.
var azRefreshTotals = (function() {
	var totalsEl = document.getElementById('az-checkout-totals');
	var form = document.querySelector('form.checkout');

	return function(cb) {
		var data = { action: 'az_get_checkout_totals' };
		if (form) {
			var get = function(name) {
				var el = form.querySelector('[name="' + name + '"]');
				return el ? el.value : '';
			};
			data.country            = get('billing_country');
			data.state              = get('billing_state');
			data.postcode           = get('billing_postcode');
			data.city               = get('billing_city');
			data.address            = get('billing_address_1');
			data.ship_to_different_address = form.querySelector('#ship-to-different-address-checkbox:checked') ? 1 : 0;
			data.s_country          = get('shipping_country');
			data.s_state            = get('shipping_state');
			data.s_postcode         = get('shipping_postcode');
			data.s_city             = get('shipping_city');
			data.s_address          = get('shipping_address_1');
		}

		if (totalsEl) azShowLoading(totalsEl);

		jQuery.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: data,
			success: function(res) {
				if (res && res.success && res.data && totalsEl) {
					// Replaces the overlay along with everything else in
					// totalsEl — nothing left to explicitly hide here.
					totalsEl.innerHTML = res.data.html;
				} else if (totalsEl) {
					azHideLoading(totalsEl);
				}
				if (typeof cb === 'function') cb();
			},
			error: function() {
				if (totalsEl) azHideLoading(totalsEl);
				if (typeof cb === 'function') cb();
			}
		});
	};
})();

// ---- update_checkout: third-party hook point ----
// WooCommerce's own checkout.js normally owns this event, but it's been
// dequeued (see the address-fields comment below), so nothing was listening
// for it — plugins that trigger it to ask for a totals refresh (EPOS CRM's
// voucher and points widgets both do, via document.body.dispatchEvent, which
// jQuery(document.body).on() below does receive) had no effect. azRefreshTotals
// itself must never trigger this event, or applying a voucher would recurse.
jQuery(document.body).on('update_checkout', function() {
	azRefreshTotals();
});

// ---- Address fields: re-run totals/shipping when billing/shipping change ----
// Mirrors WooCommerce's own checkout.js scope (wc-checkout-phone.js explains
// why that script isn't available here to do this itself): select fields
// (country/state) refresh immediately; text fields (postcode/city/address)
// only refresh once required fields in the same row aren't empty, so a
// half-typed postcode doesn't spam the endpoint or show a bogus shipping rate.
(function() {
	var form = document.querySelector('form.checkout');
	if (!form) return;

	var debounceTimer = null;
	function debouncedRefresh() {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(azRefreshTotals, 1000);
	}

	form.addEventListener('change', function(e) {
		// Toggling "Ship to a different address?" switches which address
		// (billing vs shipping) the server should quote a rate against —
		// same trigger-immediately treatment WC core gives this checkbox
		// (checkout.js's #ship-to-different-address selector list).
		if (e.target.id === 'ship-to-different-address-checkbox') {
			azRefreshTotals();
			return;
		}

		var field = e.target.closest('.address-field');
		if (!field) return;

		if (e.target.matches('select')) {
			azRefreshTotals();
			return;
		}

		if (e.target.matches('input.input-text')) {
			var requiredEmpty = field.parentElement
				? Array.prototype.some.call(
					field.parentElement.querySelectorAll('.address-field.validate-required input.input-text'),
					function(el) { return el.value === ''; }
				)
				: false;
			if (!requiredEmpty) azRefreshTotals();
		}
	});

	// Also catch typing (debounced), same as WC core's keydown-based queueing,
	// so a filled-in postcode updates shipping without needing a blur first.
	form.addEventListener('keydown', function(e) {
		if (!e.target.closest('.address-field') || !e.target.matches('input.input-text')) return;
		if (e.key === 'Tab') return;
		debouncedRefresh();
	});
})();

// ---- Place order: processing feedback ----
// This is a real form submit (no AJAX, no preventDefault) — the browser
// navigates away once the server responds. WooCommerce's own checkout.js
// normally blocks the form and disables the button for this same window
// (see submit()/blockOnSubmit() — not available here, same as elsewhere in
// this file), so without this the button gives no feedback between click
// and the page changing, and a slow gateway invites a double submit.
(function() {
	var form = document.querySelector('form.checkout');
	var placeOrderBtn = document.getElementById('place_order');
	if (!form || !placeOrderBtn) return;

	form.addEventListener('submit', function() {
		if (form.classList.contains('processing')) return;
		form.classList.add('processing');

		azShowFullLoading('<?php echo esc_js( __( 'Placing your order…', 'ai-zippy' ) ); ?>');

		// Disabling the clicked submit button synchronously, inside its own
		// submit event, can make the browser drop that button's name=value
		// from the form data it's in the middle of serializing — Chrome does
		// this. Losing woocommerce_checkout_place_order means WooCommerce
		// never sees this as an order submission and just re-renders a blank
		// checkout page instead of processing it. Deferring one tick lets the
		// browser finish building the request first.
		setTimeout(function() {
			placeOrderBtn.disabled = true;
			placeOrderBtn.textContent = '<?php echo esc_js( __( 'Placing order…', 'ai-zippy' ) ); ?>';
		}, 0);
	});
})();

(function() {
	// ---- Coupon: apply + remove (AJAX, no reload) ----
	var btn = document.getElementById('az-apply-coupon');
	var input = document.getElementById('az-coupon-code');
	var msg = document.getElementById('az-coupon-msg');
	var totalsEl = document.getElementById('az-checkout-totals');
	if (!btn || !input) return;

	function setMsg(text, type) {
		msg.textContent = text || '';
		msg.classList.remove('is-success', 'is-error');
		if (text && type) msg.classList.add('is-' + type);
	}

	function applyCoupon() {
		var code = input.value.trim();
		if (!code) return;

		setMsg('', null);
		btn.disabled = true;
		btn.textContent = '<?php echo esc_js( __( 'Applying…', 'ai-zippy' ) ); ?>';

		jQuery.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: {
				action: 'woocommerce_apply_coupon',
				security: '<?php echo esc_js( wp_create_nonce( 'apply-coupon' ) ); ?>',
				coupon_code: code,
			},
			success: function(response) {
				var html = String(response || '');
				var temp = document.createElement('div');
				temp.innerHTML = html;

				// WC may render either an ERROR or SUCCESS notice. Look up
				// each kind by its specific class — never by [role="alert"]
				// (success banners use that role too, so it doesn't disambiguate).
				//   classic error:   <ul class="woocommerce-error"><li>…</li></ul>
				//   classic success: <div class="woocommerce-message">…</div>
				//   blocks  error:   <div class="wc-block-components-notice-banner is-error">
				//   blocks  success: <div class="wc-block-components-notice-banner is-success">
				var errorEl   = temp.querySelector('.woocommerce-error, .wc-block-components-notice-banner.is-error');
				var successEl = temp.querySelector('.woocommerce-message, .wc-block-components-notice-banner.is-success');

				// Trust the explicit markers. If neither exists, fall back to
				// "success if non-empty response with no error class" — WC's
				// classic flow sometimes returns just the message text.
				var ok = !errorEl && (successEl || temp.textContent.trim() !== '');

				var noticeEl = errorEl || successEl || temp.querySelector('li, p');
				var text     = noticeEl ? noticeEl.textContent.trim().replace(/\s+/g, ' ') : '';

				if (ok) {
					input.value = '';
					setMsg(text || '<?php echo esc_js( __( 'Coupon applied!', 'ai-zippy' ) ); ?>', 'success');
					azRefreshTotals();
				} else {
					setMsg(text || '<?php echo esc_js( __( 'Coupon could not be applied.', 'ai-zippy' ) ); ?>', 'error');
				}
			},
			error: function() {
				setMsg('<?php echo esc_js( __( 'Network error. Please try again.', 'ai-zippy' ) ); ?>', 'error');
			},
			complete: function() {
				btn.disabled = false;
				btn.textContent = '<?php echo esc_js( __( 'Apply', 'ai-zippy' ) ); ?>';
			}
		});
	}

	btn.addEventListener('click', applyCoupon);
	input.addEventListener('keydown', function(e) {
		if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
	});

	// Intercept the remove-coupon link so it goes through AJAX too
	// (delegated, since the link is re-rendered on each refresh).
	if (totalsEl) {
		totalsEl.addEventListener('click', function(e) {
			var link = e.target.closest('[data-az-remove-coupon]');
			if (!link) return;
			e.preventDefault();
			var code = link.getAttribute('data-az-remove-coupon');
			link.style.opacity = '0.5';

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
				data: {
					action: 'woocommerce_remove_coupon',
					security: '<?php echo esc_js( wp_create_nonce( 'remove-coupon' ) ); ?>',
					coupon: code,
				},
				success: function() {
					setMsg('', null);
					azRefreshTotals();
				},
				error: function() {
					link.style.opacity = '';
					setMsg('<?php echo esc_js( __( 'Could not remove coupon.', 'ai-zippy' ) ); ?>', 'error');
				}
			});
		});
	}
})();

// ---- Quantity controls ----
(function() {
	var $ = jQuery;
	var updating = false;

	function updateQty(cartKey, newQty) {
		if (updating) return;
		updating = true;

		// Dim the item
		var item = document.querySelector('[data-cart-key="' + cartKey + '"]');
		if (item) item.style.opacity = '0.5';

		$.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: {
				action: 'az_update_checkout_qty',
				cart_key: cartKey,
				quantity: newQty,
				security: '<?php echo esc_js( wp_create_nonce( 'az-checkout-qty' ) ); ?>',
			},
			success: function() {
				// Reload the page to reflect updated cart
				// (WC checkout fragments don't cover our custom sidebar)
				location.reload();
			},
			error: function() {
				if (item) item.style.opacity = '1';
				updating = false;
			}
		});
	}

	document.addEventListener('click', function(e) {
		var item = e.target.closest('.az-checkout__item');
		if (!item) return;
		var key = item.dataset.cartKey;
		if (!key) return;

		var qtyEl = item.querySelector('.az-checkout__qty-val');
		var qty = parseInt(qtyEl.textContent, 10);

		if (e.target.closest('.az-checkout__qty-btn--minus')) {
			// At qty=1, minus removes the item (matches the React checkout behavior).
			// Confirm first so the click can't accidentally drop the line.
			if (qty > 1) {
				updateQty(key, qty - 1);
			} else if (confirm('<?php echo esc_js( __( 'Remove this item from your order?', 'ai-zippy' ) ); ?>')) {
				updateQty(key, 0);
			}
		} else if (e.target.closest('.az-checkout__qty-btn--plus')) {
			updateQty(key, qty + 1);
		} else if (e.target.closest('.az-checkout__qty-remove')) {
			if (confirm('<?php echo esc_js( __( 'Remove this item?', 'ai-zippy' ) ); ?>')) {
				updateQty(key, 0);
			}
		}
	});
})();
</script>

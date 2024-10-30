<?php
/**
 * Jentis Tracking Core
 *
 * @package Jentis
 */

/**
 * WC Intergration Class, All the methods initialized here
 */
class WC_Integration_Jentis_Plugin extends WC_Integration {
	/**
	 * Constructor, sets up all the actions
	 */
	public function __construct() {
		$this->id                 = 'jentis';
		$this->method_title       = __( 'Jentis Settings', 'jentis' );
		$this->method_description = __(
			'Set up the Jentis Tracking for your WooCommerce.',
			'jentis'
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		$this->tracking_domain = $this->get_option( 'tracking_domain' );
		$this->container_id    = $this->get_option( 'container_id' );
		$this->hash            = $this->get_option( 'hash' );
		add_action( 'woocommerce_update_options_integration_' . $this->id, [ &$this, 'process_admin_options' ] );

		add_action( 'wp_footer', 'page_view_track' );
		add_action( 'wp_footer', 'single_page_add_to_cart' );
		add_action( 'wp_footer', 'remove_from_cart' );
		add_action( 'wp_footer', 'ajax_add_to_cart' );
		add_action( 'wp_footer', 'cart_page_viewed' );
		add_action( 'wp_footer', 'product_list_view' );
		add_action( 'wp_footer', 'product_list_click' );
		add_action( 'wp_footer', 'product_view' );
		add_action( 'wp_footer', 'product_searched' );
		add_action( 'wp_footer', 'checkout_order' );

		add_action( 'woocommerce_thankyou', 'completed_purchase' );

	}

	/**
	 * User configurable settings
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters(
			'wc_integration_jentis_plugin_fields',
			[
				'tracking_domain' => [
					'title'       => __( 'Full tracking domain', 'jentis' ),
					'type'        => 'text',
					'description' => __(
						'Full tracking domain from Jentis',
						'jentis'
					),
					'placeholder' => '',
				],
				'container_id'    => [
					'title'       => __( 'Container ID', 'jentis' ),
					'type'        => 'text',
					'description' => __(
						'Container ID from Jentis',
						'jentis'
					),
					'placeholder' => '',
				],
				'hash'            => [
					'title'       => __( 'Hash', 'jentis' ),
					'type'        => 'text',
					'description' => __( 'Hash from Jentis', 'jentis' ),
					'placeholder' => '',
				],
			]
		);
	}
}

/**
 * Page view tracking function for Jentis
 */
function page_view_track() {
	if ( is_product() || is_cart() || is_product_category() || is_shop() || is_checkout() || is_order_received_page() || is_search() || is_order_received_page() ) {
		return;
	}
	?>
    <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
        window.addEventListener('load', call_jts_page_view, true);

        function call_jts_page_view() {
            tjs_tracking = new JtsTracking();
            tjs_tracking.pageView();
        }
    </script>
	<?php
}

/**
 * Adding to cart on single page
 */
function single_page_add_to_cart() {
	if ( ! is_product() ) {
		return;
	}
	global $product, $post;

	$terms = get_the_terms( $post->ID, 'product_cat' );

	$term_collection = [];
	foreach ( $terms as $term ) {
		$sanitized_term_name = validateData( sanitize_text_field( $term->name ) );
		array_push( $term_collection, esc_html( $sanitized_term_name ) );
	}

	$imploded_term_collection = implode( ',', $term_collection );
	?>
    <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
        window.addEventListener('load', call_jts_add_to_cart, true);

        function call_jts_add_to_cart() {
            const productData = {
                name: '<?php echo esc_html( validateData( sanitize_text_field( $product->get_name() ) ) ); ?>',
                id: <?php echo (int) esc_html( validateData( sanitize_text_field( $product->get_id() ) ) ); ?>,
                brutto: <?php echo (float) esc_html( validateData( sanitize_text_field( wc_get_price_including_tax( $product ) ) ) );?>,
                netto: <?php echo (float) esc_html( validateData( sanitize_text_field( wc_get_price_excluding_tax( $product ) ) ) ); ?>,
                // in the below code echo is using to assign the value to the JavaScript object. it is not used to render html content to the page.
                category: <?php echo json_encode( esc_html( $imploded_term_collection ) ); ?>
            }
            console.log(productData.category)
            tjs_tracking = new JtsTracking();
            tjs_tracking.singpleProductAddToCartEventBind(productData);
        }
    </script>
	<?php
}


/**
 * Remove from cart function
 */
function remove_from_cart() {
	?>
    <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
        window.addEventListener('load', call_jts_remove_from_cart, true);

        function call_jts_remove_from_cart() {
            tjs_tracking = new JtsTracking();
            tjs_tracking.removeFromCartEventBind();
        }
    </script>
	<?php
}

/**
 * AJAX Add to Cart Event
 */
function ajax_add_to_cart() {
	?>
    <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
        window.addEventListener('load', call_jts_ajax_add_to_cart, true);

        function call_jts_ajax_add_to_cart() {
            tjs_tracking = new JtsTracking();
            tjs_tracking.ajaxAddToCartClickEvenetBind();
        }
    </script>
	<?php
}

/**
 * Product List View Event
 */
function product_list_view() {
	if ( is_search() && isset( $_GET['s'] ) ) {
		return;
	}

	if ( is_product_category() || is_shop() || is_page( 'shop' ) ) {
		?>
        <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
            window.addEventListener('load', call_jts_product_list_viewed, true);

            function call_jts_product_list_viewed() {
                tjs_tracking = new JtsTracking();
                tjs_tracking.productListViewBind('<?php echo esc_html( validateData( sanitize_text_field( get_the_title() ) ) ); ?>');

            }
        </script>
		<?php
	}
}

/**
 * Cart page viewd event
 */
function cart_page_viewed() {
	if ( ! is_page( 'cart' ) || ! is_cart() ) {
		return;
	}

	$cart_view_data           = [];
	$cart_view_data ['netto'] = (float) esc_html( validateData( sanitize_text_field( WC()->cart->subtotal_ex_tax ) ) );
	$cart_view_data['brutto'] = (float) esc_html( validateData( sanitize_text_field( WC()->cart->subtotal ) ) );
	$cart_view_data['data']   = [];
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = $cart_item['data'];
		array_push( $cart_view_data ['data'], [
				'name'     => esc_html( validateData( sanitize_text_field( $product->get_name() ) ) ),
				'id'       => (int) esc_html( validateData( sanitize_text_field( $cart_item['product_id'] ) ) ),
				'quantity' => (int) esc_html( validateData( sanitize_text_field( $cart_item['quantity'] ) ) ),
				'brutto'   => (float) esc_html( validateData( sanitize_text_field( wc_get_price_including_tax( $product ) ) ) ),
				'netto'    => (float) esc_html( validateData( sanitize_text_field( wc_get_price_excluding_tax( $product ) ) ) ),
				'variants' => $product->get_attributes()
			]
		);
	}
	?>
    <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
        window.addEventListener('load', call_jts_cart_viewed, true);

        function call_jts_cart_viewed() {
            tjs_tracking = new JtsTracking();
            // in the below code echo is using to assign the value to the JavaScript object. it is not used to render html content to the page.
            tjs_tracking.cartViewEventBind(<?php echo json_encode( $cart_view_data ); ?>);
        }
    </script>

	<?php
}

/**
 * Product List Click Event
 */
function product_list_click() {
	if ( is_product_category() || is_shop() || is_page( 'shop' ) ) {
		$page_title = validateData( sanitize_text_field( $slug = basename( get_permalink() ) ) );

		?>
        <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
            window.addEventListener('load', call_jts_product_list_clicked, true);

            function call_jts_product_list_clicked() {
                tjs_tracking = new JtsTracking();
                tjs_tracking.productListClickBind('<?php echo esc_html( $page_title ); ?>');
            }
        </script>

		<?php
	}
}

/**
 * Product View Event
 */
function product_view() {
	if ( is_product() ) {

		global $product;

		$terms          = get_the_terms( $product->get_id(), 'product_cat' );
		$cat_collection = [];
		foreach ( $terms as $term ) {
			array_push( $cat_collection, esc_html( validateData( sanitize_text_field( $term->name ) ) ) );
		}

		$imploded_cat_collection = implode( ',', $cat_collection );
		?>
        <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
            window.addEventListener('load', call_jts_product_viewed, true);

            function call_jts_product_viewed() {
                const productData = {
                    name: '<?php echo esc_html( validateData( sanitize_text_field( $product->get_name() ) ) ); ?>',
                    id: <?php echo (int) esc_html( validateData( sanitize_text_field( $product->get_id() ) ) ); ?>,
                    brutto: <?php echo (float) esc_html( validateData( sanitize_text_field( wc_get_price_including_tax( $product ) ) ) );?>,
                    netto: <?php echo (float) esc_html( validateData( sanitize_text_field( wc_get_price_excluding_tax( $product ) ) ) ); ?>,
                    quantity: 0,
                    category: ' <?php echo esc_html( $imploded_cat_collection );  ?>',
                }
                tjs_tracking = new JtsTracking();
                tjs_tracking.productViewBind(productData);
            }
        </script>

		<?php
	}
}

/**
 * Product Searched Event
 */
function product_searched( $query ) {
	if ( ! is_admin() && is_search() ) {

		?>
        <script>
            window.addEventListener('load', call_jts_search, true);

            function call_jts_search() {
                tjs_tracking = new JtsTracking();
                tjs_tracking.searchBind('<?php echo esc_html( validateData( sanitize_text_field( get_search_query() ) ) ); ?>');
            }
        </script>
		<?php
	}
}

/**
 * Completed the purchase event
 */
function completed_purchase( $order_id ) {
	if ( is_order_received_page() || is_wc_endpoint_url( 'order-received' ) ) {

		$order = wc_get_order( (int) esc_html( validateData( sanitize_text_field( $order_id ) ) ) );

		$order_data                = [];
		$order_data['id']          = (int) esc_html( validateData( sanitize_text_field( $order_id ) ) );
		$order_data['data']        = [];
		$order_data['orderBrutto'] = (float) esc_html( validateData( sanitize_text_field( $order->get_subtotal() + $order->get_total_tax() ) ) );
		$order_data['orderNetto']  = (float) esc_html( validateData( sanitize_text_field( $order->get_subtotal() ) ) );
		$order_data['tax']         = (float) esc_html( validateData( sanitize_text_field( $order->get_total_tax() ) ) );
		$order_data['shipping']    = (float) esc_html( validateData( sanitize_text_field( $order->get_shipping_total() ) ) );
		$order_data['paytype']     = esc_html( validateData( sanitize_text_field( $order->get_payment_method() ) ) );
		$order_data['city']        = esc_html( validateData( sanitize_text_field( $order->get_shipping_city() ) ) );
		$order_data['zip']         = esc_html( validateData( sanitize_text_field( $order->get_shipping_postcode() ) ) );
		$order_data['country']     = esc_html( validateData( sanitize_text_field( $order->get_shipping_country() ) ) );

		$order_data['coupons'] = [];
		$is_have_coupon        = $order->get_coupon_codes();
		if ( isset( $is_have_coupon ) ) {
			foreach ( $order->get_coupon_codes() as $coupon_code ) {
				// Get the WC_Coupon object.
				$coupon = new WC_Coupon( $coupon_code );
				array_push( $order_data ['coupons'], [
					'code'          => esc_html( validateData( sanitize_text_field( $coupon_code ) ) ),
					'value'         => (float) esc_html( validateData( sanitize_text_field( $coupon->get_amount() ) ) ),
					'type'          => esc_html( validateData( sanitize_text_field( $coupon->get_discount_type() ) ) ),
					'appliedAmount' => (float) esc_html( validateData( sanitize_text_field( $coupon->get_amount() ) ) ),
					'name'          => esc_html( validateData( sanitize_text_field( $coupon_code ) ) ),
				] );
			}
		}

		foreach ( $order->get_items() as $item_id => $item ) {
			$_pf      = new WC_Product_Factory();
			$_product = $_pf->get_product( $item->get_product_id() );
			array_push( $order_data['data'], [
					'name'     => esc_html( validateData( sanitize_text_field( $item->get_name() ) ) ),
					'id'       => (int) esc_html( validateData( sanitize_text_field( $item->get_product_id() ) ) ),
					'quantity' => (int) esc_html( validateData( sanitize_text_field( $item->get_quantity() ) ) ),
					'brutto'   => (float) esc_html( validateData( sanitize_text_field( wc_get_price_including_tax( $_product ) ) ) ),
					'netto'    => (float) esc_html( validateData( sanitize_text_field( wc_get_price_excluding_tax( $_product ) ) ) ),
					'position' => (int) esc_html( validateData( sanitize_text_field( $item->get_id() ) ) )
				]
			);
		}
		?>
        <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
            window.addEventListener('load', call_jts_order, true);

            function call_jts_order() {
                tjs_tracking = new JtsTracking();
                // in the below code echo is using to assign the value to the JavaScript object. it is not used to render html content to the page.
                tjs_tracking.orderPageBind( <?php echo json_encode( $order_data ); ?>);
            }
        </script>

		<?php
	}
}

/**
 * Checkout Order Event
 */
function checkout_order() {
	if ( is_checkout() && ! is_order_received_page() && ! is_wc_endpoint_url( 'order-received' ) ) {

		$order_products = [];

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			array_push( $order_products, array(
				'id'       => (int) esc_html( validateData( sanitize_text_field( $cart_item['product_id'] ) ) ),
				'name'     => esc_html( validateData( sanitize_text_field( $product->get_name() ) ) ),
				'brutto'   => (float) esc_html( validateData( sanitize_text_field( $product->get_price() ) ) ),
				'quantity' => (int) esc_html( validateData( sanitize_text_field( $cart_item['quantity'] ) ) )
			) );
		}

		$order_data = array(
			'items'  => $order_products,
			'brutto' => (float) esc_html( sanitize_text_field( WC()->cart->subtotal ) ),
		);

		?>

        <script data-cfasync='false' data-no-optimize='1' data-pagespeed-no-defer>
            window.addEventListener('load', call_jts_checkout, true);

            function call_jts_checkout() {
                tjs_tracking = new JtsTracking();
                // in the below code echo is using to assign the value to the JavaScript object. it is not used to render html content to the page.
                tjs_tracking.checkoutPageBind(<?php echo json_encode( $order_data ); ?>);
            }
        </script>
		<?php
	}
}

add_action( 'wp_enqueue_scripts', 'no_spex_footer_js' );
/**
 * Enqueue the js to the footer
 */
function no_spex_footer_js() {
	wp_enqueue_script(
		'no-spex-footer-js',
		plugins_url( '../scripts.js', __FILE__ ),
		[],
		null,
		false
	);
	wp_localize_script( 'no-spex-footer-js', 'jts_ajax', [
		'jts_ajaxurl' => admin_url( 'admin-ajax.php' ),
	] );

	$Jentis_Tracking = new WC_Integration_Jentis_Plugin();

	$jts_hash        = esc_html( validateData( sanitize_text_field( $Jentis_Tracking->settings['hash'] ) ) );
	$jts_containerID = esc_html( validateData( sanitize_text_field( $Jentis_Tracking->settings['container_id'] ) ) );
	$jts_tr_domain   = esc_html( validateData( sanitize_text_field( $Jentis_Tracking->settings['tracking_domain'] ) ) );

	wp_localize_script( 'no-spex-footer-js', 'jts_data', [
		'jts_hash'            => $jts_hash,
		'jts_container_id'    => $jts_containerID,
		'jts_tracking_domain' => $jts_tr_domain
	] );

}

add_action(
	'wp_ajax_get_product_data_by_product_id',
	'get_product_data_by_product_id'
);
add_action(
	'wp_ajax_nopriv_get_product_data_by_product_id',
	'get_product_data_by_product_id'
);

/**
 * Return product data. Returned list and accept multiple comma separated IDs
 */
function get_product_data_by_product_id() {
	global $woocommerce;
	$product_ids;
	$sanitized_product_ids = validateData( sanitize_text_field( $_POST['productId'] ) );

	if ( strpos( $sanitized_product_ids, ',' ) !== false ) {

		$product_ids = explode( ',', $sanitized_product_ids );
	} else {
		$product_ids = [ $sanitized_product_ids ];
	}

	$product_data = [];
	$position     = 1;
	foreach ( $product_ids as $key => $value ) {
		$product = wc_get_product( $value );

		$terms          = get_the_terms( $product->get_id(), 'product_cat' );
		$cat_collection = [];
		foreach ( $terms as $term ) {
			array_push( $cat_collection, validateData( sanitize_text_field( $term->name ) ) );
		}

		$imploded_cat_collection = implode( ',', $cat_collection );
		array_push( $product_data, [
				'id'       => (int) esc_html( validateData( sanitize_text_field( $value ) ) ),
				'name'     => esc_html( validateData( sanitize_text_field( $product->get_name() ) ) ),
				'brutto'   => (float) esc_html( validateData( sanitize_text_field( wc_get_price_including_tax( $product ) ) ) ),
				'netto'    => (float) esc_html( validateData( sanitize_text_field( wc_get_price_excluding_tax( $product ) ) ) ),
				'category' => esc_html( $imploded_cat_collection ),
				'position' => (int) esc_html( validateData( sanitize_text_field( $position ) ) ),
				'variants' => $product->get_attributes(),
			]
		);
		$position += 1;
	}
	// in the below code echo is using to assign the value to the JavaScript object. it is not used to render html content to the page.
	echo json_encode( $product_data );
	exit();
}

function validateData( $data ): string {
	if ( empty( $data ) ) {
		return "";
	}
	if ( is_string( $data ) || is_float( $data ) || is_int( $data ) ) {
		return $data;
	}

	return "";
}

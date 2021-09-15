<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.author.com
 * @since      1.0.0
 *
 * @package    Productviewcounter
 * @subpackage Productviewcounter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Productviewcounter
 * @subpackage Productviewcounter/admin
 * @author     pushkarUpadhyay <pushkar@gmail.com>
 */
class Productviewcounter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Productviewcounter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Productviewcounter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/productviewcounter-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Productviewcounter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Productviewcounter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/productviewcounter-admin.js', array( 'jquery' ), $this->version, false );

	}



	function woocommerce_product_custom_fields()
	{
		global $woocommerce, $post;
		echo '<div class="product_custom_field">';
		// Custom Product Text Field
		woocommerce_wp_text_input(
			array(
				'id' => '_custom_field',
				'placeholder' => 'Enter Price',
				'label' => __('Festive Price', 'woocommerce'),
				'desc_tip' => 'true'
			)
		);
		echo '</div>';
	}


	function woocommerce_product_custom_fields_save($post_id)
	{
    	// Custom Product Text Field
		 $woocommerce_custom_product_text_field = $_POST['_custom_field'];
		if (!empty($woocommerce_custom_product_text_field)){
			update_post_meta($post_id, '_custom_field', esc_attr($woocommerce_custom_product_text_field));
		}
	}


	



	// function discount_when_weight_greater_than_100() {
	// 	global $post;
	// 	$field = get_post_meta(412,'_custom_field',true);
	// 	echo $field;
	// 	// global $product;
	// 	//$field = get_post_meta(get_the_ID(),'_custom_field',true); 
	// 	if($field){
	// 		$coupon_code = 'justOne';
	// 		$amount = '10'; // Amount
	// 		$discount_type = 'percent'; 

	// 		$coupon = array(
	// 			'post_title' => $coupon_code,
	// 			'post_content' => '',
	// 			'post_status' => 'publish',
	// 			'post_author' => 1,
	// 			'post_type'     => 'shop_coupon'
	// 		);    

	// 		$new_coupon_id = wp_insert_post( $coupon );

	// 		// Add meta
	// 		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
	// 		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
	// 		update_post_meta( $new_coupon_id, 'individual_use', 'no' );
	// 		update_post_meta( $new_coupon_id, 'product_ids', '' );
	// 		update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
	// 		update_post_meta( $new_coupon_id, 'usage_limit', '1' );
	// 		update_post_meta( $new_coupon_id, 'expiry_date', '' );
	// 		update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
	// 		update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
	// 	// 	global $woocommerce;
	// 	// 	global $total_weight;
		
	// 	// //if($field){
	// 	// 		//$coupon_code = 'justOne';
	// 	// 		if (!$woocommerce->cart->add_discount( sanitize_text_field( $coupon_code ))) {
	// 	// 			$woocommerce->show_messages();
	// 	// 		}
	// 	// 		echo '<div class="woocommerce_message"><strong>Your order is over 100 lbs so a 10% Discount has been Applied!</strong> Your total order weight is <strong>' . $total_weight . '</strong> lbs.</div>';
	// 	// //}

	// 	}
		

	// }
	function coupon_function(){
		$coupon_code = 'Festive';
		foreach(WC()->cart->get_cart() as $key => $value){
			$content = get_post_meta($value['product_id'] , '_custom_field', true);
			if(!empty($content))
			{
				WC()->cart->apply_coupon($coupon_code);
				//wc_print_notices();
			}
		}
	}


	function check_single_page(){
		global $post;
        if(is_product()){
            $meta = get_post_meta($post->ID,'_views_count',TRUE);
            $meta = ($meta) ? $meta + 1 : 1;
            update_post_meta($post->ID,'_views_count',$meta);
        }
	}

	function custom_view_counter( $columns ){
		// do something with $columns array
		return array_slice( $columns, 0, 8, true )
		+ array( 'view' => 'Views' );
	}

	function custom_view_populate( $column_name) {
		if( $column_name  == 'view' ) {
			// if you suppose to display multiple brands, use foreach()
			echo get_post_meta( get_the_ID(), '_view_count',true);
		}
	}

	function views_sortable_columns($columns){
		$columns['view'] = 'Views';
    	return $columns;
	}





}

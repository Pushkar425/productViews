<?php  
/** 
 Plugin name: ProductVariation 
*/

require __DIR__ . '/vendor/autoload.php';
 
use Automattic\WooCommerce\Client;
 
$woocommerce = new Client(
    'http://wordpress/shop/', 
    'ck_d5ad681e202f7bd17467bdb1ab3efaa881e2f3f0', 
    'cs_8dcc6c4d2441b50d4350ee179e63b78135a9dc1d',
    [
        'wp_api' => true,
        'version' => 'wc/v2',
    ]
);

function get_attributes_from_json( $json ) {
	$product_attributes = array();
 
	foreach( $json as $key => $pre_product ){
		if ( !empty( $pre_product['attribute_name'] ) && !empty( $pre_product['attribute_value'] ) ){
			$product_attributes[$pre_product['attribute_name']]['terms'][] = $pre_product['attribute_value'];
        }
    }		
 
	return $product_attributes;
 
}

function get_products_and_variations_from_json( $json, $added_attributes ) {
 
	$product = array();
	$product_variations = array();
 
	foreach ( $json as $key => $pre_product ){
 
		if ( $pre_product['type'] == 'simple' ){
			$product[$key]['_product_id'] = (string) $pre_product['product_id'];
 
			$product[$key]['name'] = (string) $pre_product['name'];
			$product[$key]['description'] = (string) $pre_product['description'];
			$product[$key]['regular_price'] = (string) $pre_product['regular_price'];
 
			// Stock
			$product[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
 
			if ( $pre_product['stock'] > 0 ) {
				$product[$key]['in_stock'] = (bool) true;
				$product[$key]['stock_quantity'] = (int) $pre_product['stock'];
            }else {
				$product[$key]['in_stock'] = (bool) false;
				$product[$key]['stock_quantity'] = (int) 0;
            }	
 
        }elseif ( $pre_product['type'] == 'variable' ){
			$product[$key]['_product_id'] = (string) $pre_product['product_id'];
 
			$product[$key]['type'] = 'variable';
			$product[$key]['name'] = (string) $pre_product['name'];
			$product[$key]['description'] = (string) $pre_product['description'];
			$product[$key]['regular_price'] = (string) $pre_product['regular_price'];
 
			// Stock
			$product[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
 
			if ( $pre_product['stock'] > 0 ){
				$product[$key]['in_stock'] = (bool) true;
				$product[$key]['stock_quantity'] = (int) $pre_product['stock'];
            }else{
				$product[$key]['in_stock'] = (bool) false;
				$product[$key]['stock_quantity'] = (int) 0;
            }	
 
			$attribute_name = $pre_product['attribute_name'];
 
			$product[$key]['attributes'][] = array(
					'id' => (int) $added_attributes[$attribute_name]['id'],
					'name' => (string) $attribute_name,
					'position' => (int) 0,
					'visible' => true,
					'variation' => true,
					'options' => $added_attributes[$attribute_name]['terms']
			);
 
        }elseif ( $pre_product['type'] == 'product_variation' ){	
 
			$product_variations[$key]['_parent_product_id'] = (string) $pre_product['parent_product_id'];
 
			$product_variations[$key]['description'] = (string) $pre_product['description'];
			$product_variations[$key]['regular_price'] = (string) $pre_product['regular_price'];
 
			// Stock
			$product_variations[$key]['manage_stock'] = (bool) $pre_product['manage_stock'];
 
			if ( $pre_product['stock'] > 0 ){
				$product_variations[$key]['in_stock'] = (bool) true;
				$product_variations[$key]['stock_quantity'] = (int) $pre_product['stock'];
            }else{
				$product_variations[$key]['in_stock'] = (bool) false;
				$product_variations[$key]['stock_quantity'] = (int) 0;
            }
 
			$attribute_name = $pre_product['attribute_name'];
			$attribute_value = $pre_product['attribute_value'];
 
			$product_variations[$key]['attributes'][] = array(
				'id' => (int) $added_attributes[$attribute_name]['id'],
				'name' => (string) $attribute_name,
				'option' => (string) $attribute_value
			);
 
        }		
    }		
 
	$data['products'] = $product;
	$data['product_variations'] = $product_variations;
 
	return $data;
}

function merge_products_and_variations( $product_data = array(), $product_variations_data = array() ) {
	foreach ( $product_data as $k => $product ){
		foreach ( $product_variations_data as $k2 => $product_variation ){
			if ( $product_variation['_parent_product_id'] == $product['_product_id'] ){
 
				// Unset merge key. Don't need it anymore
				unset($product_variation['_parent_product_id']);
 
				$product_data[$k]['variations'][] = $product_variation;
 
            }
        }
 
		// Unset merge key. Don't need it anymore
		unset($product_data[$k]['_product_id']);
    }
 
	return $product_data;
}

try {
 
	$json = json_decode(file_get_contents('products.json' ),true);
 
	// Import Attributes
	foreach ( get_attributes_from_json( $json ) as $product_attribute_name => $product_attribute ){
 
		$attribute_data = array(
		    'name' => $product_attribute_name,
		    'slug' => 'pa_' . strtolower( $product_attribute_name ),
		    'type' => 'select',
		    'order_by' => 'menu_order',
		    'has_archives' => true
		);
 
		$wc_attribute = $woocommerce->post( 'products/attributes', $attribute_data );
 
		if ( $wc_attribute ){
			// store attribute ID so that we can use it later for creating products and variations
			$added_attributes[$product_attribute_name]['id'] = $wc_attribute['id'];
			
			// Import: Attribute terms
			foreach ( $product_attribute['terms'] as $term ){
 
				$attribute_term_data = array(
					'name' => $term
				);
 
				$wc_attribute_term = $woocommerce->post( 'products/attributes/'. $wc_attribute['id'] .'/terms', $attribute_term_data );
 
				if ( $wc_attribute_term ){
					// store attribute terms so that we can use it later for creating products
					$added_attributes[$product_attribute_name]['terms'][] = $term;
                }	
				
            }
 
        }
 
    }
 
 
	$data = get_products_and_variations_from_json( $json, $added_attributes );
 
	// Merge products and product variations so that we can loop through products, then its variations
	$product_data = merge_products_and_variations( $data['products'], $data['product_variations'] );
 
	// Import: Products
	foreach ( $product_data as $k => $product ){
 
		if ( isset( $product['variations'] ) ){
			$_product_variations = $product['variations']; // temporary store variations array
 
			// Unset and make the $product data correct for importing the product.
			unset($product['variations']);
        }		
 
			$wc_product = $woocommerce->post( 'products', $product );
 
		if ( isset( $_product_variations ) ){
			// Import: Product variations
 
			// Loop through our temporary stored product variations array and add them
			foreach ( $_product_variations as $variation ){
				$wc_variation = $woocommerce->post( 'products/'. $wc_product['id'] .'/variations', $variation );	
            }
 
			// Don't need it anymore
			unset($_product_variations);
        }
 
    }
	
 
} catch ( HttpClientException $e ) {
    echo $e->getMessage(); // Error message
}








?>

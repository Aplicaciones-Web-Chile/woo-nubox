<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.nubox.com/
 * @since             1.0.0
 * @package           Nubox_Integracion
 *
 * @wordpress-plugin
 * Plugin Name:       Nubox Integracion
 * Plugin URI:        http://appstore.nubox.com/
 * Description:       Integracion con API REST de Nubox.
 * Version:           1.0.1
 * Author:            Nubox
 * Author URI:        http://www.nubox.com/
 * WC requires at least: 3.4.0
 * WC tested up to: 5.5.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nubox-integracion
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NUBOX_INTEGRACION_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nubox-integracion-activator.php
 */
function activate_nubox_integracion() {

	if ( !class_exists( 'WooCommerce' ) ) {
		exit('Se necesita tener WooCommerce instalado y activo para poder activar este plugin');
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nubox-integracion-activator.php';
	Nubox_Integracion_Activator::activate();

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nubox-integracion-deactivator.php
 */
function deactivate_nubox_integracion() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nubox-integracion-deactivator.php';
	Nubox_Integracion_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nubox_integracion' );
register_deactivation_hook( __FILE__, 'deactivate_nubox_integracion' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nubox-integracion.php';

/**
 * Comienza la ejecución del plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nubox_integracion() {

	$plugin = new Nubox_Integracion();
	$plugin->run();

}

run_nubox_integracion();

#add_action('woocommerce_payment_complete'      , 'nubox_payment_complete', 20, 1);
add_action('woocommerce_order_status_completed', 'nubox_payment_complete', 20, 1);

add_action('admin_notices', function () {
	
	if (!Nubox_Integracion_Loader::is_valid_for_use()) {
		?>
		<div class="notice notice-error">
			<p><?php _e('Woocommerce debe estar configurado en pesos chilenos (CLP) para habilitar la integracion con Nubox', 'nubox_wc_plugin'); ?></p>
		</div>
		<?php
	}

	$nubox_api_key	=	get_option('nubox_api_key');
	$nubox_mode		=	get_option('nubox_mode');

	if ( empty($nubox_api_key) || empty($nubox_mode)) {
		?>
		<div class="notice notice-error">
			<p>Debes <a href="<?php echo admin_url( 'admin.php?page=nubox_rest', 'https' ); ?>">agregar las credenciales de la API de Nubox</a></p>
		</div>
		<?php
	}

});





add_action('admin_menu', function () {

	//create new top-level menu
	add_submenu_page('woocommerce', __('Configuración de Nubox', 'nubox_wc_plugin'), 'Configuración de Nubox', 'administrator', 'nubox_rest', function () {
		include __DIR__.'/admin/partials/nubox-integracion-admin-display.php';
	}, null);

});


function nubox_woocommerce_billing_fields($fields){

	$fields['billing_nubox_rut']	=	array(
										'label'			=>	__('RUT', 'woocommerce'), // Add custom field label
										'placeholder'	=>	_x('Rut', 'placeholder', 'woocommerce'), // Add custom field placeholder
										'required'		=>	true, // if field is required or not
										'clear'			=>	false, // add clear or not
										'type'			=>	'text', // add field type
										'class'			=>	array('nubox-rut')    // add class name
									);

	$fields['billing_nubox_giro']	=	array(
										'label'			=>	__('Giro comercial (obligatorio en caso de requerir factura)', 'woocommerce'), // Add custom field label
										'placeholder'	=>	_x('Giro', 'placeholder', 'woocommerce'), // Add custom field placeholder
										'required'		=>	false, // if field is required or not
										'clear'			=>	false, // add clear or not
										'type'			=>	'text', // add field type
										'class'			=>	array('nubox-giro')    // add class name
									);

	return $fields;
}


/**
 * Actualizo Rut y Giro de la orden
 */
add_action( 'woocommerce_checkout_update_order_meta', 'nubox_checkout_field_update_order_meta' );
function nubox_checkout_field_update_order_meta( $order_id ) {

	if ( ! empty( $_POST['billing_nubox_rut'] ) ) {
		update_post_meta( $order_id, 'Rut', sanitize_text_field( $_POST['billing_nubox_rut'] ) );
	}

	if ( ! empty( $_POST['billing_nubox_giro'] ) ) {
		update_post_meta( $order_id, 'Giro', sanitize_text_field( $_POST['billing_nubox_giro'] ) );
	}

}

/**
 * Despliego los valores de Rut y Giro
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'nubox_checkout_field_display_admin_order_meta', 10, 1 );
function nubox_checkout_field_display_admin_order_meta($order){
	echo '<p><strong>'.__('Rut').':</strong> ' . get_post_meta( $order->id, 'Rut', true ) . '</p>';
	echo '<p><strong>'.__('Giro').':</strong> ' . get_post_meta( $order->id, 'Giro', true ) . '</p>';
}


function nubox_payment_complete($order_id) {
	global $wpdb;

	$order = wc_get_order( $order_id );
	$order->add_order_note( '#Nubox Comenzando integración' );
	$order->save();

	$array2Api = array();

	try {
		
		if ( $order->get_status() != 'processing' && $order->get_status() != 'completed'  ) {
			$order->add_order_note( '#Nubox No se ha integrado con Nubox, ya que no esta pagado.' );
			$order->save();
			return false;
		}

		$array2Api['token_pi']				=	get_option( 'nubox_api_key');
		$array2Api['documentoReferenciado']	=	(Object)[];

		$billing_firstname	= get_post_meta($order_id, '_billing_first_name', true);
		$billing_lastname	= get_post_meta($order_id, '_billing_last_name', true);
		$billing_phone		= get_post_meta($order_id, '_billing_phone', true);

		$shipping_address1	= get_post_meta($order_id, '_billing_address_1', true);
		$shipping_address2	= get_post_meta($order_id, '_billing_address_2', true);

		$medallion			= strtoupper(get_user_meta($order->user_id, 'loyalty_status', true));
		$order_amount		= get_post_meta($order_id, '_order_total', true);
		$pay_type			= get_post_meta($order_id, '_payment_method', true);

		$rut				= get_post_meta( $order->id, 'Rut', true );

		$giro				= get_post_meta( $order->id, 'Giro', true );
		$giro				= removeSpecialChar($giro);

		$comunaContraparte	= get_post_meta( $order->id, '_billing_state', true );


		/*
			Medio De Pago. Valores posibles:

			CH:Cheque.
			CF:Cheque a fecha.
			LT:letra.
			EF:Efectivo.
			PE:Pago a cta.cte, TC:Tarjeta Credito,
			OT:Otro


			Forma De Pago. Valores posibles:

			1: Contado.
			2: Crédito.
			3: Sin costo
		*/
		if ($pay_type == 'cod') {
			$medioDePago = "OT";
			$formaDePago = "1";
		} else {
			$medioDePago = "TC";
			$formaDePago = "2";
		}

		$items		=	$order->get_items();
		$secuencia	=	1;

		foreach ( $items as $item ) {

			$arrayProducto			=	array();

			$product_name			=	$item->get_name();
			$product_id				=	$item->get_product_id();
			$product_variation_id	=	$item->get_variation_id();
			$quantity				=	$item->get_quantity();
			$total					=	$item->get_total();

			$product				=	wc_get_product( $product_id );
			$descripcion			=	$product->get_description();
			$descripcion			=	removeSpecialChar($descripcion);

			/*
				$product_id			= $item->get_product_id();
				$variation_id		= $item->get_variation_id();
				$product			= $item->get_product();
				$product_name		= $item->get_name();
				$subtotal			= $item->get_subtotal();
				$total				= $item->get_total();
				$tax				= $item->get_subtotal_tax();
				$taxclass			= $item->get_tax_class();
				$taxstat			= $item->get_tax_status();
				$allmeta			= $item->get_meta_data();
				$somemeta			= $item->get_meta( '_whatever', true );
				$product_type		= $item->get_type();
			*/

			# https://developers.nubox.com/emision-venta

			$arrayProducto['rutContraparte']					=	$rut;
			$arrayProducto['razonSocialContraparte']			=	removeSpecialChar( $billing_firstname . ' ' . $billing_lastname );
			$arrayProducto['giroContraparte']					=	removeSpecialChar( $giro );
			$arrayProducto['comunaContraparte']					=	$comunaContraparte;

			$arrayProducto['direccionContraparte']				=	$shipping_address1.' '.$shipping_address2;
			$arrayProducto['direccionContraparte']				=	removeSpecialChar( $arrayProducto['direccionContraparte'] );

			/*
			Código SII del tipo de documento. Valores posibles:

				33: Factura electrónica.
				34: Factura exenta electrónica.
				56: Nota débito electrónica.
				61: Nota de crédito electrónica.
				39: Boleta electrónica.
				41: Boleta exenta electrónica.
			*/
			$arrayProducto['tipo']		 						=	34; #ToDo: obtener desde app de MI
			$arrayProducto['folio']								=	'1';

			$arrayProducto['secuencia']							=	$secuencia;
			$arrayProducto['fecha']								=	date('Y-m-d');

			$arrayProducto['codigoItem']		 				=	'';
			$arrayProducto['producto']							=	$product_name;
			$arrayProducto['descripcion']						=	$descripcion;
			$arrayProducto['cantidad']							=	$quantity;
			$arrayProducto['valor']								=	abs($total);


			$arrayProducto['tipoDeServicio']					=	"3";

			$arrayProducto['fechaPeriodoDesde']					=	date('Y-m-d');
			$arrayProducto['fechaPeriodoHasta']					=	date('Y-m-d');

			$arrayProducto['fechaVencimiento']					=	'';
			$arrayProducto['codigoSucursal']					=	"1";
			$arrayProducto['unidadMedida']						=	"UNID";

			$arrayProducto['formaDePago']						=	$formaDePago;
			$arrayProducto['medioDePago']						=	$medioDePago;

			$arrayProducto['terminosDePagoDias']				=	'';
			$arrayProducto['tipoDeDespacho']					=	'';
			$arrayProducto['comunaDestino']						=	'';
			$arrayProducto['rutTransportista']					=	'';
			$arrayProducto['rutChofer']							=	'';
			$arrayProducto['patente']							=	'';
			$arrayProducto['nombreChofer']						=	'';
			$arrayProducto['direccionDestino']					=	'';
			$arrayProducto['ciudadDestino']						=	'';
			$arrayProducto['precioCambioSujeto']				=	0;
			$arrayProducto['productoCambioSujeto']				=	'';
			$arrayProducto['cantidadMontoCambioSujeto']			=	0;
			$arrayProducto['descuentoMonto']					=	0;
			$arrayProducto['ponderacionDescuento']				=	0;

			$arrayProducto['afecto']		 					=	"NO";

			$arrayProducto['codigoIMP']							=	'';
			$arrayProducto['montoIMP']							=	0;
			$arrayProducto['indicadorDeTraslado']				=	'';
			$arrayProducto['nombreDeContacto']					=	'';
			$arrayProducto['observacion']						=	'';
			$arrayProducto['rutSolicitanteFactura']				=	'';
			$arrayProducto['terminosDePagoCodigo']				=	'';
			$arrayProducto['tipoGlobalAfecto']					=	'';
			$arrayProducto['valorGlobalAfecto']					=	0;
			$arrayProducto['tipoGlobalExento']					=	'';
			$arrayProducto['valorGlobalExento']					=	0;
			$arrayProducto['vendedor']							=	'';
			$arrayProducto['emailContraparte']					=	'';
			#$arrayProducto['referenciaImpuestos']				=	'';

			$array2Api['productos'][]							=	$arrayProducto;
			$secuencia++;
			
		}


		$order->add_order_note( '#Nubox Enviando set de datos: ['. json_encode($array2Api).']' );
		$order->save();

		$response = request2NuboxApi($array2Api, $order_id);

		if( !empty($response) ){

			if ($response['status'] == 200) { 
				$order->add_order_note( '#Nubox Integración exitosa. (Status: '.$response['array2Return']['status'].')');
				$order->save();
				return true;
			}
			else {
				$order->add_order_note( '#Nubox error con integracion (Status: '.$response['array2Return']['status'].'). Respuesta en archivo log/json.' );
				$order->save();
				return false;
			} 
		} else {
			$order->add_order_note( '#Nubox error en respuesta con integración. Respuesta en archivo log/json.');
			$order->save();
		}
	} catch (Exception $e) {
		$order->add_order_note( '#Nubox error en ejecución de integración.  Error: ['. $e->getMessage().']');
		$order->save();
	}

	return true;
}





function request2NuboxApi($data, $order_id){

	$nubox_mode         =   get_option('nubox_mode'); #'testing'; // testing o produccion
	if ($nubox_mode == 'testing') {
		
		if( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'wp-nubox.local' ){
			$url			=	"https://nubox-mi-dashboard.local/webhook/";	
		}else{
			$url			=	"https://devintegraciones.nubox.com/webhook";
		}

	}else{
		$url			=	"https://integraciones.nubox.com/webhook";
	}


	$content		=	json_encode($data);
	$array2Return	=	array();
	$order			=	wc_get_order( $order_id );

	#$jsonValid		=	json_validate($json);
	#p($jsonValid, 'jsonValid');die();

	#$content	=	json_encode($json_array);
	$curl		=	curl_init($url);

	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);


	# Solo para localhost
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

	$array2Return['json_response']	=	json_decode( curl_exec($curl) );
	$array2Return['status']			=	curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	$upload_dir		=	wp_upload_dir();
	$dirJson		=	$upload_dir['basedir'].'/json/';
	$flagDirExists	=	false;

	if ( !is_dir($dirJson) ) {
		$flagDirExists = wp_mkdir_p($dirJson);
	}else{
		$flagDirExists = true;
	}

	$jsonFileName	=	'response-'.date('Y-m-d_H-i-s').'.json';
	$jsonFile		=	$dirJson.$jsonFileName;
	#$order->add_order_note( '#Nubox $jsonFile -> ' . $jsonFile );

	if( $flagDirExists === true ) {

		$fp	=	fopen($jsonFile, 'w');
		$array4file['data']			=	$data;
		$array4file['array2Return']	=	$array2Return;
		fwrite($fp, json_encode($array4file));
		fclose($fp);
		$order->add_order_note( '#Nubox $jsonFile -> ' . $jsonFileName );
	}

	#$a = ( $flagDirExists === true ) ? 'Verdadero' : 'Falso';
	#$order->add_order_note( '#Nubox Valor $flagDirExists -> ' . $a );
	$order->save();

	return $array2Return;
}



if ( !function_exists('removeSpecialChar') ) {

	function removeSpecialChar($str){
		$res = preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$str);
		return $res;
	}

}















































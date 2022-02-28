<?php

/**
 * Formulario en el que se configura la conexión con la App MI
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.nubox.com/
 * @since      1.0.0
 *
 * @package    Nubox_Integracion
 * @subpackage Nubox_Integracion/admin/partials
 */

$flagNubox = false;

if( isset($_POST) && count($_POST) > 0 ){

	if ( !isset( $_POST['nubox_compruebox'] ) 
		|| ! wp_verify_nonce( $_POST['nubox_compruebox'], 'nubox_settings_save' ) 
	) {

		print 'Sorry, your nonce did not verify.';
		exit;

	} else {

	   update_option( 'nubox_api_key', $_POST['nubox_api_key'] );
	   //update_option( 'nubox_secret_key', $_POST['nubox_secret_key'] );
	   update_option( 'nubox_mode', $_POST['nubox_mode'] );

	   $flagNubox = true;


	}

}

$nubox_api_key      =   get_option('nubox_api_key'); #'ValorProvenienteDeAppDeIntegracion';
//$nubox_secret_key   =   get_option('nubox_secret_key'); #'*********************';
$nubox_mode         =   get_option('nubox_mode'); #'testing'; // testing o produccion

?>

<div class="wrap" id="nubox-form-container">
	<h1 class="nubox-logo">Nubox.com</h1>
	<div class="contact-form-editor-panel">

	<form method="post" id="nubox_settings_form" action="">
		<!-- action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" -->

		<div id="universal-message-container">
			<h2>Integración con Nubox </h2>
			<p class="details summary">
				Ingresa aquí las credenciales de tu cuenta Nubox.
			</p>

			<?php if( $flagNubox === true ): ?>
				<div class="updated notice">Las credenciales de tu cuenta Nubox han sido guardadas exitosamente.</div>
			<?php endif; ?>

			<div class="options">
				<p>
					<label>API KEY</label>
					<br />
					<input type="text" name="nubox_api_key" id="nubox_api_key" value="<?php echo $nubox_api_key; ?>" />
				</p>
				<!--<p>
					<label>SECRET KEY</label>
					<br />
					<input type="password" name="nubox_secret_key" id="nubox_secret_key" value="<?php echo $nubox_secret_key; ?>" />
				</p>-->

				<p>
					<label>Modo.</label>
					<br />
					<select name="nubox_mode" id="nubox_mode" value="<?php echo $nubox_mode; ?>">
						<option value="testing" <?php if($nubox_mode === 'testing'): echo 'selected="selected"'; endif; ?> >Testing</option>
						<option value="produccion" <?php if($nubox_mode === 'produccion'): echo 'selected="selected"'; endif; ?> >Producción</option>
						<!--<option value="produccion" selected="selected" >Producción</option>-->
					</select>
				</p>

				<div id="nubox_mensajes" class="error notice" style="display: none;"></div>

			</div><!-- #universal-message-container -->
			<?php
				wp_nonce_field( 'nubox_settings_save', 'nubox_compruebox' );
				submit_button();
			?>
		</div>
	</form>
	</div>
</div>



<?php
// create custom plugin settings menu
/*add_action('admin_menu', 'my_cool_plugin_create_menu');

function my_cool_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('My Cool Plugin Settings', 'Cool Settings', 'administrator', __FILE__, 'my_cool_plugin_settings_page' , plugins_url('/images/icon.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'register_my_cool_plugin_settings' );
}


function register_my_cool_plugin_settings() {
	//register our settings
	register_setting( 'my-cool-plugin-settings-group', 'new_option_name' );
	register_setting( 'my-cool-plugin-settings-group', 'some_other_option' );
	register_setting( 'my-cool-plugin-settings-group', 'option_etc' );
}

function my_cool_plugin_settings_page() {
?>
<div class="wrap">
<h1>Your Plugin Name</h1>

<form method="post" action="options.php">
	<?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
	<?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row">New Option Name</th>
		<td><input type="text" name="new_option_name" value="<?php echo esc_attr( get_option('new_option_name') ); ?>" /></td>
		</tr>
		 
		<tr valign="top">
		<th scope="row">Some Other Option</th>
		<td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Options, Etc.</th>
		<td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
		</tr>
	</table>
	
	<?php submit_button(); ?>

</form>
</div>
<?php } */?>
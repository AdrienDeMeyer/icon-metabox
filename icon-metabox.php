<?php
/*
Plugin Name: Icon MetaBox
Plugin URI: https://adriendemeyer.com/
Description: adds to your posts a meta boxes for Fontawesome Icons
Version: 1
Author: Adrien De Meyer
Author URI: https://adriendemeyer.com
Text Domain: icon-metabox
Domain Path: /languages
*/

add_action( 'admin_menu', 'add_icon_metabox_menu' );
function add_icon_metabox_menu() {
	add_options_page( 'Icon MetaBox', 'Icon MetaBox', 'manage_options', 'icon-metabox', 'icon_metabox_settings_page');
}

function icon_metabox_settings_page() { ?>
	
	<div class="wrap">

	<h2><?php _e( 'Icon Metabox Settings', 'icon-metabox' ); ?></h2>

	<?php
	if ( isset( $_POST['submit_data'] ) ) {   
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'nonce_icon_metabox' ) ) {
		   echo 'Sorry, your nonce did not verify.';
		   exit;
		} else {
			update_option('_post_types_selected',$_POST["post_types_selected"]);	
			echo '<div id="message" class="updated">'.__( "Settings saved." ).'</div>';
		}
	}
	?>
		

	<form method="post">

	<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'nonce_icon_metabox' ); ?>

	<div id="icon-metabox-select-objects">

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Post Types', 'icon-metabox' ) ?></th>
				<td>
				<?php
					$post_types = get_post_types( array (
						'show_ui' => true,
						'show_in_menu' => true,
					), 'objects' );

					$post_types_selected = get_option('_post_types_selected');
										
					foreach ( $post_types  as $post_type ) {
						if ( $post_type->name != 'attachment' ) {	?>
							
							<input type="checkbox" name="post_types_selected[]" value="<?php echo $post_type->name; ?>" <?php if ( isset( $post_types_selected ) && is_array( $post_types_selected ) ) { if( in_array($post_type->name,$post_types_selected) ) { echo ' checked="checked" '; }} ?>>
							<label><?php echo $post_type->label; ?></label><br>

						<?php
						}
					}
				?>
				</td>
			</tr>
		</tbody>
	</table>

	</div>
	<label><input type="checkbox" id="all_icon_metabox_objects"> <?php _e( 'All Check', 'icon-metabox' ) ?></label>

	<p class="submit">
		<input type="hidden" value="Y" name="submit_data">
		<input type="submit" class="button-primary" name="icon_metabox_submit" value="<?php _e( 'Update' ); ?>">
	</p>
		
	</form>

	</div>
	<script>
	(function($){
		
		$("#all_icon_metabox_objects").on('click', function(){
			var items = $("#icon-metabox-select-objects input");
			if ( $(this).is(':checked') ) $(items).prop('checked', true);
			else $(items).prop('checked', false);	
		});

	})(jQuery)
	</script>

<?php
	
}

/**
 * Metabox (Add)
 */

add_action('admin_print_styles', 'fontawesome_style', 11 );
add_action('wp_enqueue_scripts','fontawesome_style');
function fontawesome_style() {
    wp_enqueue_style(  'fontawesome', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css");
}

$add_cpt = get_option('_post_types_selected');
   
add_action('add_meta_boxes','initialisation_metabox');
function initialisation_metabox(){
	global $add_cpt;
	add_meta_box('id_meta_icon', 'choisissez une icône', 'icon_metabox_function', $add_cpt, 'normal', 'high');
}

/**
 * Metabox (Functions)
 */

function array_delete($array, $element) {
    return (is_array($element)) ? array_values(array_diff($array, $element)) : array_values(array_diff($array, array($element)));
}

function icon_metabox_function($post){
	
	$icons_file = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css";
	$parsed_file = file_get_contents($icons_file);
	preg_match_all("/fa\-([a-zA-z0-9\-]+[^\:\.\,\s\{\>])/", $parsed_file, $matches);
	$exclude_icons = array("fa-pull-left", "fa-pull-right", "fa-lg", "fa-2x", "fa-3x", "fa-4x", "fa-5x", "fa-ul", "fa-li", "fa-fw", "fa-border", "fa-pulse", "fa-rotate-90", "fa-rotate-180", "fa-rotate-270", "fa-spin", "fa-flip-horizontal", "fa-flip-vertical", "fa-stack", "fa-stack-1x", "fa-stack-2x", "fa-inverse");
	$icons = array(array_delete($matches[0], $exclude_icons));
	
	echo '<style>
	#id_meta_icon i {
		font-size: 20px !important;
		vertical-align: middle;
	}
	#id_meta_icon div .icon-column {
		display: inline-block;
		width: 60px;
		border-bottom: 1px solid #eee;
	}
	</style>';
	
	// on récupère la valeur actuelle pour la mettre dans le champ
	$icon_selected = get_post_meta($post->ID,'icon_selected',true);
		
	echo '<div class="icon-column">';
	$count = 1;
	foreach($icons[0] as $icon){ 
		echo '<p><input type="radio" name="icon_selected" value="'.$icon.'" '.checked( $icon_selected, $icon, false ).'><i class="fa '.$icon.'"></i></p>';
		if($count%10==0){echo '</div><div class="icon-column">';}
	$count++;
	}			
	echo '</div>';
	
	echo '<div style="text-align: right;"><a target="_blank" href="http://fontawesome.io/icons/">Rechercher une icône fontawesome</a></div>';

}

/**
 * Métabox (save)
 */

add_action('save_post','save_icon_metabox');
function save_icon_metabox($post_ID){
  // si la metabox est définie, on sauvegarde sa valeur
  if(isset($_POST['icon_selected'])){
    update_post_meta($post_ID,'icon_selected', esc_html($_POST['icon_selected']));
  }
  
   if(isset($_POST['number_selected'])){
    update_post_meta($post_ID,'number_selected', esc_html($_POST['number_selected']));
  }
}

/**
 * Using in front
 */
function fontawesome_metabox(){
	global $post;
	$icon_selected = get_post_meta($post->ID,'icon_selected',true); 

	if($icon_selected != '') 
		echo '<i class="fa '.$icon_selected.'" aria-hidden="true"></i>';	
}
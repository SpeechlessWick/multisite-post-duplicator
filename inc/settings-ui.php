<?php
/**
 * 
 * This file controls arte generation of the MPD Settings page
 * @since 0.4
 * @author Mario Jaconelli <mariojaconelli@gmail.com>
 * 
 */


if ( is_multisite() ) {

	add_action( 'admin_menu', 'mdp_add_admin_menu' );
	
	add_action( 'admin_init', 'mdp_settings_init' );
	
}

/**
 * 
 * Add MDP Settings navigation to WordPress navigation
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_add_admin_menu(  ) {

	$options 		= get_option( 'mdp_settings' );
	$settingsLogic 	= current_user_can( 'manage_options' );
	$settingsLogic 	= apply_filters( 'mpd_show_settings_page', $settingsLogic );

	if($settingsLogic){

		add_submenu_page( 'options-general.php', __('Multisite Post Duplicator Settings', MPD_DOMAIN ), __('Multisite Post Duplicator Settings', MPD_DOMAIN), 'manage_options', 'multisite_post_duplicator', 'mdp_options_page' );
		
	}

}


/**
 * 
 * Register the settings for MPD
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_settings_init(  ) { 

	register_setting( MPD_SETTING_PAGE, 'mdp_settings' );

	do_action( 'mdp_start_plugin_setting_page' );

	add_settings_section(
		MPD_SETTING_SECTION, 
		false, 
		'mdp_settings_section_callback', 
		MPD_SETTING_PAGE
	);

	mpd_settings_field('meta_box_show_radio', __( 'What Post Types you want to show the MPD Meta Box?', MPD_DOMAIN ), 'meta_box_show_radio_render');

	$mpd_post_types 		= get_post_types();
	$loopcount 				= 1;
	$post_types_to_ignore 	= mpd_get_post_types_to_ignore();

	foreach ($mpd_post_types as $mpd_post_type){

		if( !in_array( $mpd_post_type, $post_types_to_ignore ) ){

			mpd_settings_field(

					'meta_box_post_type_selector_' . $mpd_post_type,
					$loopcount == 1 ? __("Select Post Types to show the MPD Meta Box on", MPD_DOMAIN ) : "",
					'meta_box_post_type_selector_render',
					array(
						'mdpposttype' => $mpd_post_type
					)

			);
			
			$loopcount++;

		}

	}
	
	mpd_settings_field(
		'mdp_default_prefix',
		__( 'Default prefix', MPD_DOMAIN ),
		'mdp_default_prefix_render'
	);
	mpd_settings_field(
		'mdp_default_tags_copy',
		__( 'Copy Post Tags when duplicating?', MPD_DOMAIN ),
		'mdp_default_tags_copy_render'
	);
	mpd_settings_field(
		'mdp_copy_post_categories',
		__( 'Copy Post Categories?', MPD_DOMAIN ),
		'mdp_copy_post_categories_render'
	);
	mpd_settings_field(
		'mdp_copy_post_taxonomies',
		__( 'Copy Post Taxonomies?', MPD_DOMAIN ),
		'mdp_copy_post_taxonomies_render'
	);

	mpd_settings_field(
		'mdp_default_featured_image',
		__( 'Copy Featured Image when duplicating?', MPD_DOMAIN ),
		'mdp_default_feat_image_copy_render'
	);
	mpd_settings_field(
		'mdp_copy_content_images',
		__( 'Copy Post content images to destination Media Library?', MPD_DOMAIN ),
		'mdp_copy_content_image_render'
	);

	do_action( 'mdp_end_plugin_setting_page' );

	mpd_settings_field(
		'mdp_ignore_custom_meta',
		__( 'Post Meta to ignore?', MPD_DOMAIN ),
		'mdp_ignore_custom_meta_render'
	);

}
/**
 * 
 * Create the UI for the Post Type Selector in Settings
 * 
 * @since 0.4
 * @return null
 * 
 */
function meta_box_show_radio_render(){

	if($options = get_option( 'mdp_settings' )){

		$mdp_radio_label_value = $options['meta_box_show_radio'];

	}else{

		$mdp_radio_label_value = 'all';

	};

	?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.mbs-click', '.mbs-content', 'fast');
		});
	</script>
	<div id="mpd_radio_choice_wrap">

		<div class="mdp-inputcontainer">

			<input type="radio" class="mdp_radio" name='mdp_settings[meta_box_show_radio]' id="meta_box_show_choice_all" <?php checked( $mdp_radio_label_value, 'all'); ?> value="all">
		
			<label class="mdp_radio_label" for="radio-choice-1"><?php _e('All Post Types', MPD_DOMAIN ) ?></label>
			    
			<input type="radio" class="mdp_radio" name='mdp_settings[meta_box_show_radio]' id="meta_box_show_choice_some" <?php checked( $mdp_radio_label_value, 'some'); ?> value="some">
		
			<label class="mdp_radio_label" for="radio-choice-2"><?php _e('Some Post Types', MPD_DOMAIN ) ?></label>
		
			<input type="radio" class="mdp_radio" name='mdp_settings[meta_box_show_radio]' id="meta_box_show_choice_none" <?php checked( $mdp_radio_label_value, 'none'); ?> value="none">
		
			<label class="mdp_radio_label" for="radio-choice-3"><?php _e('No Post Types', MPD_DOMAIN) ?></label>

	    	<i class="fa fa-info-circle mbs-click accord" aria-hidden="true"></i>
	    </div>

	    <p class="mpdtip mbs-content" style="display:none"><?php _e('The MDP meta box is shown on the right of your post/page/custom post type. You can control where you would like this meta box to appear using the selection above. If you select "Some post types" you will get a list of all the post types below to toggle their display.', MPD_DOMAIN ) ?></p>

    </div>
	<?php
}

/**
 * 
 * Create the UI for the Post Type checkboxes
 * 
 * @since 0.4
 * @param array $args The post type checkbox to render. Probably generated in mdp_settings_init()
 * 
 * @return null
 * 
 */
function meta_box_post_type_selector_render($args) { 

	$options 		= get_option( 'mdp_settings' );
	$mpd_post_type 	= $args['mdpposttype'];
	$the_name 		= "mdp_settings[meta_box_post_type_selector_" . $mpd_post_type . "]";
	$the_selector 	= 'meta_box_post_type_selector_' . $mpd_post_type;

	?>

	<input type='checkbox' class="posttypecb" name='<?php echo $the_name; ?>' <?php mpd_checked_lookup($options, $the_selector, $mpd_post_type) ;?> value='<?php echo $mpd_post_type; ?>'> <?php echo $mpd_post_type; ?> <br >

	<?php

}

/**
 * 
 * Create the UI for the Prefix Setting
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_default_prefix_render(  ) { 

	$options = get_option( 'mdp_settings' );
	
	?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.dp-click', '.dp-content', 'fast');
		});
	</script>
	
	<input type='text' name='mdp_settings[mdp_default_prefix]' value='<?php echo mpd_get_prefix(); ?>'> <i class="fa fa-info-circle dp-click accord" aria-hidden="true"></i>  

	<p class="mpdtip dp-content" style="display:none"><?php _e('Change the default prefix for your duplication across the network.', MPD_DOMAIN )?></p>
	
	<?php

}
/**
 * 
 * Create the UI for the Keys to Ignore Setting
 * 
 * @since 0.9
 * @return null
 * 
 */
function mdp_ignore_custom_meta_render(  ) { 

	$options = get_option( 'mdp_settings' ); ?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.icm-click', '.icm-content', 'fast');
		});
	</script>
	<input id="mdp-ignore-custom-meta" type='text' autocapitalize="none" autocorrect="none" name='mdp_settings[mdp_ignore_custom_meta]' value='<?php echo mpd_get_ignore_keys(); ?>'> <i class="fa fa-info-circle icm-click accord" aria-hidden="true"></i>  

	<p class="mpdtip icm-content" style="display:none"><?php _e('A comma delimerated list of post meta keys you wish to ignore during the duplication process. <em>i.e (without quotes) \'my_custom_meta_key, post_facebook_share_count\'</em></br></br>WARNING: Only edit this option if you are sure what you are doing.', MPD_DOMAIN )?></p>
	
	<?php

}
/**
 * 
 * Create the UI for the Tag Copy Selection Setting
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_default_tags_copy_render(  ) { 

	$options = get_option( 'mdp_settings' ); ?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.dtc-click', '.dtc-content', 'fast');
		});
	</script>

	<input type='checkbox' name='mdp_settings[mdp_default_tags_copy]' <?php mpd_checked_lookup($options, 'mdp_default_tags_copy', 'tags') ;?> value='tags'> <i class="fa fa-info-circle dtc-click accord" aria-hidden="true"></i>  

	<p class="mpdtip dtc-content" style="display:none"><?php _e('This plugin will automatically copy the tags associated with the post. You can turn off this activity by unchecking the box.', MPD_DOMAIN )?></p>

	<?php

}

/**
 * 
 * Create the UI for the Category Copy Selection Setting
 * 
 * @since 0.8
 * @return null
 * 
 */
function mdp_copy_post_categories_render(  ) { 

	$options = get_option( 'mdp_settings' ); ?>

	<script>
		jQuery(document).ready(function() {
				accordionClick('.mpdcpt-click', '.mpdcpt-content', 'fast');
		});
	</script>
	
	<input type='checkbox' name='mdp_settings[mdp_copy_post_categories]' <?php mpd_checked_lookup($options, 'mdp_copy_post_categories', 'category') ;?> value='category'> <i class="fa fa-info-circle mpdcpt-click accord" aria-hidden="true"></i> 

	<p class="mpdtip mpdcpt-content" style="display:none"><?php _e('This plugin will automatically copy the categories associated with the post. If the category doesn\'t exist in the destination site the category will be created for you. You can turn off this activity by unchecking the box.', MPD_DOMAIN )?></p>

	<?php

}
/**
 * 
 * Create the UI for the Taxonomy Copy Selection Setting
 * 
 * @since 0.8
 * @return null
 * 
 */
function mdp_copy_post_taxonomies_render(  ) { 

	$options = get_option( 'mdp_settings' ); ?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.cpt-click', '.cpt-content', 'fast');
		});
	</script>
	
	<input type='checkbox' name='mdp_settings[mdp_copy_post_taxonomies]' <?php mpd_checked_lookup($options, 'mdp_copy_post_taxonomies', 'taxonomy') ;?> value='taxonomy'> <i class="fa fa-info-circle cpt-click accord" aria-hidden="true"></i>

	<p class="mpdtip cpt-content" style="display:none"><?php _e('This plugin will automatically copy the taxonomy TERMS associated with the post. If the taxonomy TERMS don\'t exist in the destination site the will be created for you. Note: This functionsality assumes you have the taxonomies in your source site also registered in your destination site. You can turn off this activity by unchecking the box.', MPD_DOMAIN )?></p>

	<?php

}

/**
 * 
 * Create the UI for the Featured Image Copy Selection Setting
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_default_feat_image_copy_render(  ) { 

	$options = get_option( 'mdp_settings' ); ?>

	<script>
		jQuery(document).ready(function() {
				accordionClick('.dfi-click', '.dfi-content', 'fast');
		});
	</script>
	<input type='checkbox' name='mdp_settings[mdp_default_featured_image]' <?php mpd_checked_lookup($options, 'mdp_default_featured_image', 'feat') ;?> value='feat'> <i class="fa fa-info-circle dfi-click accord" aria-hidden="true"></i>

	<p class="mpdtip dfi-content" style="display:none"><?php _e('This plugin will automatically copy any featured image associated with the post. You can turn off this activity by unchecking the box.', MPD_DOMAIN )?></p>
	
	<?php

}

/**
 * 
 * Create the UI for the Inline Image Copy Selection setting
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_copy_content_image_render(  ) { 

	$options = get_option( 'mdp_settings' );

	?>
	<script>
		jQuery(document).ready(function() {
				accordionClick('.cci-click', '.cci-content', 'fast');
		});
	</script>
	<input type='checkbox' name='mdp_settings[mdp_copy_content_images]' <?php mpd_checked_lookup($options, 'mdp_copy_content_images', 'content-image') ;?> value='content-image'> <i class="fa fa-info-circle cci-click accord" aria-hidden="true"></i>

	<p class="mpdtip cci-content" style="display:none"><?php _e('On duplication this plugin will look at the content within the main post content field and try to identify any images that have been added from your media library. If it finds any it will duplicate the image and all its meta data to your destinations site`s media library for exclusive use there. It will also change the urls in the duplicated post to reference the new media file. You can turn off this activity by unchecking the box', MPD_DOMAIN)?></p>
	
	<?php

}

/**
 * 
 * Generate a sub heading for the settings page
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_settings_section_callback(  ) { 

	_e( 'Here you can change the default settings for Multisite Post Duplicator. Note that these settings are used for every site in your network.', MPD_DOMAIN );

}

add_action( 'update_option_mdp_settings', 'mpd_globalise_settings', 10, 2 );

/**
 * 
 * This function is used to copy the saved settings to all other sites options table,
 * therefore globalising the MPD settings arcroos all sites.
 * 
 * @since 0.4
 * @return null
 * 
 */
function mpd_globalise_settings(){
    
    $options 	= get_option( 'mdp_settings' );
    $sites 		= mpd_wp_get_sites();

	foreach ($sites as $site) {

		switch_to_blog($site->blog_id);

			update_option( 'mdp_settings', $options);

		restore_current_blog();

	}
    	
}
/**
 * 
 * Generate the complete Settings page.
 * See https://codex.wordpress.org/Creating_Options_Pages for info.
 * 
 * @since 0.4
 * @return null
 * 
 */
function mdp_options_page(  ) { 

	$active_tab = '';

	if( isset( $_GET[ 'tab' ] ) ) {
        
        $active_tab = $_GET[ 'tab' ];
    
    }

    $options 		= get_option( 'mdp_settings' );
	$settingsLogic 	= current_user_can( mpd_get_required_cap() );
	$settingsLogic 	= apply_filters( 'mpd_show_settings_page', $settingsLogic );

	if($logic = $settingsLogic):?>

		<?php if(isset($options['add_logging']) || isset($options['allow_persist'])):?>

			<h2 class="nav-tab-wrapper">

    			<a href="options-general.php?page=multisite_post_duplicator" class="nav-tab <?php echo $active_tab == '' ? 'nav-tab-active' : ''; ?>"><i class="fa fa-sliders fa-fw" aria-hidden="true"></i> Settings</a>

    	<?php endif; ?>

    	<?php if(isset($options['add_logging'])) :?>

    			<a href="options-general.php?page=multisite_post_duplicator&tab=log" class="nav-tab <?php echo $active_tab == 'log' ? 'nav-tab-active' : ''; ?>"><i class="fa fa-list-ul fa-fw" aria-hidden="true"></i> Activity Log</a>

    	<?php endif;?>

    	<?php if(isset($options['allow_persist'])):?>
    		
    			<a href="options-general.php?page=multisite_post_duplicator&tab=persists" class="nav-tab <?php echo $active_tab == 'persists' ? 'nav-tab-active' : ''; ?>"><i class="fa fa-link fa-fw" aria-hidden="true"></i> Linked Duplications</a>

    	<?php endif; ?>

    	<?php if(isset($options['add_logging']) || isset($options['allow_persist'])):?>
			</h2>
		<?php endif; ?>
		
	<?php endif; 

	if($active_tab == 'log' && $logic){

		mdp_log_page();

	}elseif($active_tab == 'persists' && $logic){

		mpd_persist_page();
		
	}else{
		echo "<div class='wrap'>";
		echo "<h2><i class='fa fa-link' aria-hidden='true'></i> Multisite Post Duplicator Settings</h2>";
		echo "<form action='options.php' method='post'>";
		settings_fields( MPD_SETTING_PAGE );
		do_settings_sections( MPD_SETTING_PAGE );
		submit_button();
		echo "</form>";
		echo "</div>";

	}

}

?>
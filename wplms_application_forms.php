<?php
/*
Plugin Name: WPLMS Application Forms
Plugin URI: http://www.Vibethemes.com
Description: A WPLMS Addon to get details of the user while applying for a course.
Version: 1.0
Author: Vibethemes (H.K.)
Author URI: http://www.vibethemes.com
Text Domain: wplms-af
*/
/*
Copyright 2017  VibeThemes  (email : vibethemes@gmail.com)
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once 'includes/class.config.php';
include_once 'includes/class.updater.php';
include_once 'includes/class.init.php';

// Add text domain
add_action('plugins_loaded','wplms_application_forms_translations');
function wplms_application_forms_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-af');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-af', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-af', $mofile_global );
    } else {
        load_textdomain( 'wplms-af', $mofile_local );
    }  
}

if(class_exists('WPLMS_Application_Forms_Init')){
    // instantiate the plugin class
 	$init = WPLMS_Application_Forms_Init::Instance_WPLMS_Application_Forms_Init();
}

register_activation_hook(__FILE__,'flush_rewrite_rules');

function Wplms_Application_Forms_Plugin_updater() {
    $license_key = trim( get_option( 'wplms_application_forms_license_key' ) );
    $edd_updater = new Wplms_Application_Forms_Plugin_Updater( 'http://vibethemes.com', __FILE__, array(
            'version'   => '1.0',               
            'license'   => $license_key,        
            'item_name' => 'WPLMS APPLICATION FORMS',    
            'author'    => 'VibeThemes' 
        )
    );
}
add_action( 'admin_init', 'Wplms_Application_Forms_Plugin_updater', 0 );
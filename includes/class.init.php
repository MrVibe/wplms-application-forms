<?php
/**
 *
 * @class       WPLMS_Application_Forms_Init
 * @author      VibeThemes (H.K.Latiyan)
 * @category    Admin
 * @package     WPLMS-Application-Forms/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPLMS_Application_Forms_Init{

	public static $instance;
	public static function Instance_WPLMS_Application_Forms_Init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPLMS_Application_Forms_Init();
	    return self::$instance;
	}

	private function __construct(){

		add_filter('wplms_course_product_metabox',array($this,'add_wplms_application_forms_in_course'),99,1);
		add_filter('wplms_course_details_widget',array($this,'add_wplms_application_form_on_course_details'),99,2);

	} // END public function __construct

	function add_wplms_application_forms_in_course( $metabox ){
		$metabox['vibe_course_apply']['type'] = 'conditionalswitch';
		$metabox['vibe_course_apply']['hide_nodes'] = array('vibe_wplms_application_forms');
		$metabox['vibe_course_apply']['options'] = array('H'=>__('Hide','wplms-af' ),'S'=>__('Show','wplms-af' ));

		$metabox['vibe_wplms_application_forms'] = array(
								'label'=> __('Invite Application Form','wplms-af' ),
								'text'=>__('Invite Application Form','wplms-af' ),
								'type'=> 'conditionalswitch',
								'hide_nodes'=> array('vibe_wplms_application_forms_editor'),
								'options'  => array('H'=>__('DISABLE','wplms-af' ),'S'=>__('ENABLE','wplms-af' )),
								'style'=>'',
								'id' => 'vibe_wplms_application_forms',
								'from'=> 'meta',
								'default'=>'H',
								'desc'=> __('Show application form to the users to get their information before applying for the course.','wplms-af' ),
							);
		$metabox['vibe_wplms_application_forms_editor'] = array(
								'label'=> __('Application Form','wplms-af' ),
								'text'=>__('Add Application Form','wplms-af' ),
								'type'=> 'editor',
								'noscript'=>true,
								'style'=>'',
								'id' => 'vibe_wplms_application_forms_editor',
								'from'=> 'meta',
								'desc'=> __('Application form shown to the users before applying for the course.','wplms-af' ),
							);
		return $metabox;
	}
	
} // END class WPLMS_Application_Forms_Init

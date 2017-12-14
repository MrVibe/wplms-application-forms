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
		add_filter('wplms_take_course_button_html',array($this,'add_wplms_application_form_on_course_details'),99,2);

	} // END public function __construct

	function add_wplms_application_forms_in_course( $metabox ){
		$metabox['vibe_course_apply']['type'] = 'conditionalswitch';
		$metabox['vibe_course_apply']['hide_nodes'] = array('vibe_wplms_application_forms','vibe_wplms_application_forms_editor');
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

	function add_wplms_application_form_on_course_details( $course_details,$course_id ){
		$check = get_post_meta($course_id,'vibe_course_apply',true);
		if( vibe_validate($check) ){
			$user_id = get_current_user_id();
			$check_apply = get_user_meta($user_id,'apply_course'.$course_id,true);

			if( empty($check_apply) ){
				$check_apply_form = get_post_meta($course_id,'vibe_wplms_application_forms',true);
				if( vibe_validate($check_apply_form) ){
					$check_apply_content = get_post_meta($course_id,'vibe_wplms_application_forms_editor',true);
					if( !empty($check_apply_content) ){
						echo '<div class="course_aaplication_form">';
						echo do_shortcode($check_apply_content);
						echo '</div>';
						add_action('wp_footer',array($this,'add_script_for_apply_for_course_button'));
					}
				}
			}
		}
		return $course_details;
	}

	function add_script_for_apply_for_course_button(){
		?>
		<script>
			jQuery(document).ready(function($){
				$('#apply_course_button').on('click',function(event){
					var $this = $(this);
					var form = $('.course_aaplication_form').find('form');
					if( typeof(form) != 'undefined' ){
						$this.removeClass('disabled');
					}else{
						$this.addClass('disabled');
					}

					if( $this.hasClass('disabled') ){
						return;
					}

					var parent = $('.course_aaplication_form').find('form');
					var $response= parent.find(".response");
					var error= '';
					var data = [];
					var label = [];
					var regex = [];
					regex['email'] = /^([a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$)/i;
					regex['phone'] = /[A-Z0-9]{7}|[A-Z0-9][A-Z0-9-]{7}/i;
					regex['numeric'] = /^[0-9]+$/i;
					regex['captcha'] = /^[0-9]+$/i;
					regex['required'] = /([^\s])/;
					var i=0;

					parent.find('.form_field').each(function(){
						i++;
						var validate=$(this).attr('data-validate');
						var value = $(this).val();

						if(!value.match(regex[validate])){
							error += ' '+vibe_shortcode_strings.invalid_string+$(this).attr('placeholder');
							$(this).css('border-color','#e16038');
						}else{
							data[i]=value;
							label[i]=$(this).attr('placeholder');
							if(parent.hasClass('isocharset')){
								label[i]=encodeURI(label[i]);
								data[i]=encodeURI(value);
							}

						}
						if(validate === 'captcha' && error === ""){
							var $num = $(this).attr('id');
							var $sum=$(this).closest('.math-sum');
							var num1 = parseInt($('#'+$num+'-1').text());
							var num2 = parseInt($('#'+$num+'-2').text());
							var sumval = parseInt($(this).val());
							if( sumval != (num1+num2))
								error += vibe_shortcode_strings.captcha_mismatch;
						}
					});

					var attachment_id = $('.course_aaplication_form').find('.attachment_ids').val();

					if (error !== "") {
						$response.fadeIn("slow");
						$response.html("<span style='color:#D03922;'>"+vibe_shortcode_strings.error_string+" " + error + "</span>");
					}else{
						var isocharset = false;
						if(parent.hasClass('isocharset')){
							isocharset = true;
						}
						setTimeout(function(){
							$.ajax({
									type: "POST",
									url: ajaxurl,
									data: { action: 'submit_course_aaplication_form',
											security: $response.attr('data-security'),
											isocharset:isocharset,
											label:JSON.stringify(labels),
											data:JSON.stringify(formdata),
										},
									cache: false,
									success: function (html) {
										//
									}
							});
						}, 2000);
					}
				});
			});
		</script>
		<?php
	}
	
} // END class WPLMS_Application_Forms_Init

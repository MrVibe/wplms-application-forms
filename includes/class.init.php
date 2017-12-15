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
		add_action('wp_ajax_submit_course_aaplication_form',array($this,'submit_course_aaplication_form'));
		add_action('wplms_course_application_submission_users',array($this,'wplms_show_user_application_form'),10,2);

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
					var error = '';
					var data  = [];
					var label = [];
					var regex = [];
					var attachment = [];
					var event = parent.attr('data-event');
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
					attachment_id = parseInt(attachment_id);
					if( typeof(attachment_id) == 'number' ){
						attachment[0] = parent.find('.form_upload_label').text();
						attachment[1] = attachment_id;
					}

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
											label:JSON.stringify(label),
											data:JSON.stringify(data),
											course_id:$this.attr('data-id'),
											event:event,
											attachment:attachment,
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

	function submit_course_aaplication_form(){
		$nonce = $_POST['security'];
		$event = $_POST['event'];
		if ( ! wp_verify_nonce( $nonce, 'vibeform_security'.$event ) || empty($_POST['course_id']) ){
			echo __("Security check failed, please contact administrator","wplms-af");
			die();
		}

		$data = json_decode(stripslashes($_POST['data']));
		$labels = json_decode(stripslashes($_POST['label']));

		$message = '<ul>';
		for($i=1;$i<count($data);$i++){
			$message .= '<li>';
			$message .= $labels[$i].' : '.$data[$i];
			$message .= '</li>';
		}

		if( isset($_POST['attachment']) && !empty($_POST['attachment']) ){
			$attachment = $_POST['attachment'];
			$attachment_url = wp_get_attachment_url($attachment[1]);
			$message .= '<li>';
			$message .= $attachment[0].' : '.$attachment_url;
			$message .= '</li>';
		}
		$message .= '</ul>';
		$user_id = get_current_user_id();
		$course_id = $_POST['course_id'];
		update_user_meta($user_id,'wplms_course_application_form_'.$course_id,$message);
		die();
	}

	function wplms_show_user_application_form($user_id,$course_id){
		$application_form = get_user_meta($user_id,'wplms_course_application_form_'.$course_id,true);
		if( !empty($application_form) ){
			echo '<div class="user_application_form" style="width:400px;margin:auto;">'.$application_form.'</div>';
		}
	}
	
} // END class WPLMS_Application_Forms_Init

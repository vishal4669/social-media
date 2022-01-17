<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://store.webbycrown.com/
 * @since      1.0.0
 *
 * @package    Webbycrown
 * @subpackage Webbycrown/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Webbycrown
 * @subpackage Webbycrown/admin
 * @author     WebbyCrown Solutions WordPress Team <info@webbycrown.com>
 */
class Webbycrown_Admin {

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

    	add_action( 'init', array($this, 'cptui_register_my_cpts_wc_calendars') );
    	add_action( 'init', array($this, 'cptui_register_my_taxes_wc_calendars_tags') );

    	add_action( 'add_meta_boxes', array($this, 'global_notice_meta_box') );
    	add_action( 'save_post', array($this, 'save_global_notice_meta_box_data'), 10, 3 );

    }


    public function cptui_register_my_cpts_wc_calendars() {

    	$labels = [
    		"name" => __( "Content Approvals", "wunderkind" ),
    		"singular_name" => __( "Content Approval", "wunderkind" ),
    	];

    	$args = [
    		"label" => __( "Content Approvals", "wunderkind" ),
    		"labels" => $labels,
    		"description" => "",
    		"public" => true,
    		"publicly_queryable" => false,
    		"show_ui" => true,
    		"show_in_rest" => true,
    		"rest_base" => "",
    		"rest_controller_class" => "WP_REST_Posts_Controller",
    		"has_archive" => false,
    		"show_in_menu" => true,
    		"show_in_nav_menus" => true,
    		"delete_with_user" => false,
    		"exclude_from_search" => false,
    		"capability_type" => "post",
    		"map_meta_cap" => true,
    		"hierarchical" => false,
    		"rewrite" => [ "slug" => "wc_calendars", "with_front" => true ],
    		"query_var" => true,
    		"supports" => [ "title", "editor", "thumbnail" ],
    	];

    	register_post_type( "wc_calendars", $args );
    }

    public function cptui_register_my_taxes_wc_calendars_tags() {

    	$labels = [
    		"name" => __( "Tags", "wunderkind" ),
    		"singular_name" => __( "Tag", "wunderkind" ),
    	];

    	$args = [
    		"label" => __( "Tags", "wunderkind" ),
    		"labels" => $labels,
    		"public" => true,
    		"publicly_queryable" => true,
    		"hierarchical" => false,
    		"show_ui" => true,
    		"show_in_menu" => true,
    		"show_in_nav_menus" => true,
    		"query_var" => true,
    		"rewrite" => [ 'slug' => 'wc_calendars_tags', 'with_front' => true, ],
    		"show_admin_column" => false,
    		"show_in_rest" => true,
    		"rest_base" => "wc_calendars_tags",
    		"rest_controller_class" => "WP_REST_Terms_Controller",
    		"show_in_quick_edit" => false,
    	];

    	register_taxonomy( "wc_calendars_tags", [ "wc_calendars" ], $args );
    }

    public function global_notice_meta_box() {

    	add_meta_box( 'select-user', __( 'Another Setting', 'sitepoint' ), array($this, 'select_box_meta_box_callback'), 'wc_calendars', 'side' );

    }

    function select_box_meta_box_callback( $post ) {

    	wp_nonce_field( 'global_select_user', 'global_select_user' );

    	$user_id = get_post_meta( $post->ID, '_select_user', true );
    	$date = get_post_meta( $post->ID, '_start_date', true );
    	$time = get_post_meta( $post->ID, '_start_time', true );
    	$status = get_post_meta( $post->ID, '_status', true );
    	$reasons = get_post_meta($post->ID, '_reasons', true);
    	$email_notification = get_post_meta( $post->ID, 'email_notification', true );		

    	$blogusers = get_users( [ 'role__in' => [ 'author', 'subscriber' ] ] );

    	if(!empty($blogusers)) {
    		echo '<h4 for="select_user">Select User:</h4><br/>';
    		echo '<select id="select_user" name="select_user">';
    		foreach ( $blogusers as $user ) {
    			$selected = ($user->ID == $user_id)? 'selected="selected"' : '';
    			echo '<option value="'.$user->ID.'" '.$selected.'>' . esc_html( $user->display_name ) . '</option>';
    		}
    		echo '</select><br/><br/>';
    	}

    	echo '<h4 for="select_date">Select Date:</h4><br/>';
    	echo '<input type="text" class="wc-datepicker" id="select_date" name="start_date" value="'.$date.'" /><br/><br/>';

    	echo '<h4 for="select_time">Select Time:</h4><br/>';
    	echo '<input type="time" id="select_time" name="start_time" value="'.$time.'" /> <strong>24 Hours watch*</strong><br/><br/>';


    	?>
    	<h4 for="status">Status:</h4><br/>
    	<select id="status" name="status">
    		<option value="pending" <?php echo ($status == "pending")? 'selected="selected"': ''; ?>>Pending</option>
    		<option value="approved" <?php echo ($status == "approved")? 'selected="selected"': ''; ?>>Approved</option>
    		<option value="rejected" <?php echo ($status == "rejected")? 'selected="selected"': ''; ?>>Rejected</option>
    	</select><br/><br/>

    	<?php if(!empty($reasons)) { ?>
    		<h4 for="resons">Resons:</h4><br/>
    		<table class="wc-reasons">
    			<thead>
    				<tr>
    					<th style="width: 30%;">Date</th>
    					<th style="width: 70%;">Reason</th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php foreach($reasons as $key => $reason) { ?>
    					<tr>
    						<td style="width: 30%;"><?php echo date('F j, Y g:i A', strtotime($key)); ?></td>
    						<td style="width: 70%;"><?php echo $reason; ?></td>
    					</tr>
    				<?php } ?>
    			</tbody>
    		</table><br/><br/>
    	<?php } ?>

    	<div class="calendar_post_notification">
    		<table>
    			<tr>    
    				<td>
    					<label for="email_notification">
    						<?php _e( 'Enable Email Notification:', 'dpevent' ); ?>
    					</label>
    				</td>
    				<td>
    					<input type="checkbox" id="email_notification" name="email_notification"  size="40"
    					<?php
    					checked( $email_notification,"yes");
    					?> />
    				</td>          
    			</tr>
    		</table>
    	</div>

		<div class="calendar_video_notification">
			<table>
				<tr>  
					<td>
				<?php
					$html = '<h4>Upload your Video here:</h4><br/>';
					$html .= '<input id="wp_custom_attachment" name="wp_custom_attachment" size="25" type="file" value="" /><br/>';

						$filearray = get_post_meta( get_the_ID(), 'wp_custom_attachment', true );
							$this_file = '';
							if(!empty($filearray['url'])) {
								$this_file = $filearray['url'];
							}
							$html .= '<p><strong>Video Url: </strong> <input id="wp_custom_text" name="wp_customtext" type="text" value="'. $this_file .'" /></p>';
					echo $html; 
			
				?>
					</td>
				</tr>
			</table>
		</div>

		<script>
			jQuery(document).ready(function(){
				jQuery('#post').attr('enctype','multipart/form-data');
			});
		</script>
    	<?php
    }

    public function save_global_notice_meta_box_data( $post_id, $post, $update ) {

    	if ( ! isset( $_POST['global_select_user'] ) ) {
    		return;
    	}

    	if ( ! wp_verify_nonce( $_POST['global_select_user'], 'global_select_user' ) ) {
    		return;
    	}

    	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    		return;
    	}
		
		
    	if ( isset( $_POST['start_date'] ) ) {

    		$my_data = $_POST['start_date'];
    		update_post_meta( $post_id, '_start_date', $my_data );
    	}

    	if ( isset( $_POST['start_time'] ) ) {

    		$my_data = $_POST['start_time'];
    		update_post_meta( $post_id, '_start_time', $my_data );
    	}

    	if ( isset( $_POST['status'] ) ) {

    		$my_data = $_POST['status'];
    		update_post_meta( $post_id, '_status', $my_data );
    	}

    	$email_notification = sanitize_text_field( $_POST['email_notification'] );



    	if ( isset( $_POST['select_user'] ) ) {

    		$my_data = $_POST['select_user'];
    		update_post_meta( $post_id, '_select_user', $my_data );

    		$author_obj = get_user_by('id', $my_data);
    		if ( $update ){
    			$subject = 'Content Added for Approval.';
    		} else {
    			$subject = 'Content Added or Updated.';
    		}
    		$subject = 'Content Added or Updated.';
    		$body = '<strong>Name:</strong> '.get_the_title($post_id).'<br/>';
    		$body .= '<strong>Start Date:</strong> '.get_post_meta( $post_id, '_start_date', true ).'<br/>';
    		$body .= '<strong>Start Time:</strong> '.get_post_meta( $post_id, '_start_time', true ).'<br/>';
    		$body .= '<strong>Status:</strong> '.get_post_meta( $post_id, '_status', true ).'<br/>';
    		$body .= '<br/><br/>Kindly Login to our portal at '.site_url('/').'calendar/ with your email id and password to approve or reject content.';
    		$headers = array('Content-Type: text/html; charset=UTF-8');

    		if($email_notification){
    			wp_mail( $author_obj->user_email, $subject, $body, $headers );
    		}
    	}
		
		if ( !empty( $_FILES['wp_custom_attachment']['name'] ) ) {
			$upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));
			if ( isset( $upload['error'] ) && $upload['error'] != 0 ) {
				wp_die( 'There was an error uploading your file. The error is: ' . $upload['error'] );
			} else {
				update_post_meta( $post_id, 'wp_custom_attachment', $upload );
			}
		} else {
			update_post_meta( $post_id, 'wp_custom_attachment', $_POST['wp_customtext'] );
		}
		
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
         * defined in Webbycrown_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Webbycrown_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name.'jquery-ui-datepicker-style' , '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webbycrown-admin.css', array(), $this->version, 'all' );

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
         * defined in Webbycrown_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Webbycrown_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/webbycrown-admin.js', array( 'jquery' ), $this->version, true );

    }

}
<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://store.webbycrown.com/
 * @since      1.0.0
 *
 * @package    Webbycrown
 * @subpackage Webbycrown/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Webbycrown
 * @subpackage Webbycrown/public
 * @author     WebbyCrown Solutions WordPress Team <info@webbycrown.com>
 */
class Webbycrown_Public {

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
         * @param      string    $plugin_name       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {

                $this->plugin_name = $plugin_name;
                $this->version = $version;

                add_shortcode('wc_calender_month', array($this, 'wc_calender_month_function'));
                add_shortcode('wc_calender_list', array($this, 'wc_calender_list_function'));

                add_shortcode('wc_login_form', array($this, 'wc_login_form_callback'));
                add_action('wp', array($this, 'wc_user_login_callback'));
                add_action( 'template_redirect', array($this, 'wc_logged_in_redirect'));
                add_action( 'admin_init', array($this, 'wc_no_admin_access'));
                add_filter('show_admin_bar', array($this, 'wc_remove_admin_bar'), 100);
                add_action('admin_menu',array($this, 'wc_admin_calendars_menu'));
                add_action('admin_enqueue_scripts', array($this, 'wc_enqueue_date_picker'));
                add_action('admin_footer', array($this, 'wc_admin_footer_funct'));
                add_action('save_post', array($this, 'update_user_transient_funct') );

                add_action('wp_ajax_wc_get_data_by_user', array($this, 'wc_get_data_by_user_fuction'));
                add_action('wp_ajax_nopriv_wc_get_data_by_user', array($this, 'wc_get_data_by_user_fuction'));

                add_action('wp_ajax_wc_update_status', array($this, 'wc_update_status'));
                add_action('wp_ajax_nopriv_wc_update_status', array($this, 'wc_update_status'));

                add_action('wp_ajax_wc_magnific_popup', array($this, 'wc_magnific_popup'));
                add_action('wp_ajax_nopriv_wc_magnific_popup', array($this, 'wc_magnific_popup'));

                add_filter( 'wp_mail_from_name', array($this, 'wpb_sender_name') );
        }

        public function wpb_sender_name( $original_email_from ) {
                return 'Miami Marketing Company';
        }

        public function wc_no_admin_access() {
                if (is_admin() && !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author') && !current_user_can('contributor') && !( defined('DOING_AJAX') && DOING_AJAX )) {
                        wp_safe_redirect(home_url());
                        exit;
                }
        }

        public function wc_remove_admin_bar() {
                if (current_user_can('administrator')) {
                        return true;
                }
                return false;
        }

        public function wc_admin_calendars_menu(){

                add_submenu_page('edit.php?post_type=wc_calendars','Settings','Settings','manage_options','wc_settings',array($this, 'wc_settings_funct'));

                add_submenu_page('edit.php?post_type=wc_calendars','View Records','View Records','manage_options','wc_records',array($this, 'wc_records_funct'));
        }

        public function wc_enqueue_date_picker(){
                /*wp_enqueue_style( 'jquery-ui-datepicker-style' , '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
                wp_enqueue_script( 'field-date-js', 'Field_Date.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true ); 
                wp_enqueue_style( 'jquery-ui-datepicker' );*/

                wp_enqueue_script( 'jquery-ui-datepicker' );

    // You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
                wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
                wp_enqueue_style( 'jquery-ui' );
        }

        public function wc_settings_funct(){

                if(isset($_REQUEST["submit"]))
                {

                        $wc_sel_login_page              =$_REQUEST["wc_sel_login_page"];
                        $wc_sel_calender_page   =$_REQUEST["wc_sel_calender_page"];
                        $wc_admin_email =$_REQUEST["wc_admin_email"];


                        update_option( 'wc_sel_login_page', $wc_sel_login_page );
                        update_option( 'wc_sel_calender_page', $wc_sel_calender_page );
                        update_option( 'wc_admin_email', $wc_admin_email );
                }

                ?>
                <div class="wrap">
                        <div id="icon-users" class="icon32"></div>
                        <h2>Calender Settings</h2>

                        <form method="post">
                                <table class="form-table">
                                        <tbody>
                                                <?php
                                                $args_query = array(
                                                        'post_type' => array('page'),
                                                        'post_status' => array('publish'),
                                                );

                                                $query = new WP_Query( $args_query );
                                                $page_list = array();

                                                if ( $query->have_posts() ) {
                                                        while ( $query->have_posts() ) {
                                                                $query->the_post();
                                                                $page_list[get_the_ID()] = get_the_title();
                                                        }
                                                }

                                                wp_reset_postdata();
                                                ?>

                                                <tr valign="top">
                                                        <th scope="row"><label for="multilevel_marketing_multilevel_marketing_login">Select Login page </label></th>
                                                        <td class="forminp">
                                                                <fieldset>
                                                                        <select name="wc_sel_login_page" class="form-control">
                                                                                <?php
                                                                                $wc_sel_login_option = get_option('wc_sel_login_page');
                                                                                foreach ($page_list as $key => $value) {
                                                                                        ?>
                                                                                        <option value="<?php echo $key; ?>" <?php if($wc_sel_login_option == $key){ echo "selected"; }?>><?php echo $value; ?></option>
                                                                                        <?php
                                                                                }
                                                                                ?>
                                                                        </select>
                                                                </fieldset>
                                                        </td>
                                                </tr>

                                                <tr valign="top">
                                                        <th scope="row"><label for="multilevel_marketing_multilevel_marketing_register">Select Calender page </label></th>
                                                        <td class="forminp">
                                                                <fieldset>
                                                                        <select name="wc_sel_calender_page" class="form-control">
                                                                                <?php
                                                                                $wc_sel_calender_option = get_option('wc_sel_calender_page');
                                                                                foreach ($page_list as $key => $value) {
                                                                                        ?>
                                                                                        <option value="<?php echo $key; ?>" <?php if($wc_sel_calender_option == $key){ echo "selected"; }?> ><?php echo $value; ?></option>
                                                                                        <?php
                                                                                }
                                                                                ?>
                                                                        </select>
                                                                </fieldset>
                                                        </td>
                                                </tr>
                                                <tr valign="top">
                                                        <th scope="row"><label for="multilevel_marketing_multilevel_marketing_register">Admin Email </label></th>
                                                        <td class="forminp">
                                                                <fieldset>
                                                                        <?php $wc_admin_email = get_option('wc_admin_email'); ?>
                                                                        <input type="email" name="wc_admin_email" class="form-control" value="<?php echo $wc_admin_email; ?>" />
                                                                </fieldset>
                                                        </td>
                                                </tr>
                                        </tbody>
                                </table>
                                <?php  submit_button(); ?>
                        </form>
                </div>
                <?php
        }
        public function wc_records_funct(){
                $recordListTable = new Record_List_Table();
                $recordListTable->prepare_items();
                ?>
                <div class="wrap">
                        <h2>Records</h2>
                        <?php 
                        //echo '<form id="wc_recored_form" method="post">';
                        $recordListTable->search_box( __( 'Search' ), 'search');
                        $recordListTable->display();
                        //echo '</form>';
                        ?>
                </div>
                <?php
        }

        public function wc_admin_footer_funct(){
                ?>
                <script>
                        jQuery(document).ready(function($){


                                var dateFormat = "mm/dd/yy",
                                from = $( "#search-from" ).datepicker({
                                        defaultDate: "+1w",
                                        changeMonth: true
                                }).on( "change", function() {
                                        to.datepicker( "option", "minDate", getDate( this ) );
                                }),
                                to = $( "#search-to" ).datepicker({
                                        defaultDate: "+1w",
                                        changeMonth: true
                                })
                                .on( "change", function() {
                                        from.datepicker( "option", "maxDate", getDate( this ) );
                                });

                                function getDate( element ) {
                                        var date;
                                        try {
                                                date = $.datepicker.parseDate( dateFormat, element.value );
                                        } catch( error ) {
                                                date = null;
                                        }

                                        return date;
                                }

                                jQuery(document).on('click', '#wc-search-submit', function () {
                                        var wc_sel_user = jQuery('#wc_sel_user').val();
                                        var wc_sel_status = jQuery('#wc_sel_status').val();
                                        var s_from = jQuery('input[name="s_from"]').val();
                                        var s_to = jQuery('input[name="s_to"]').val();
                                        window.location.href="/wp-admin/edit.php?post_type=wc_calendars&page=wc_records&wc_sel_user="+wc_sel_user+"&wc_sel_status="+wc_sel_status+"&s_from="+s_from+"&s_to="+s_to;
                                });
                        });
                </script>
                <?php
        }       



        public function wc_login_form_callback() {
                ob_start();
                if (!is_user_logged_in()) {
                        global $errors_login;
                        if (!empty($errors_login)) {
                                ?>
                                <div class="alert alert-danger">
                                        <?php echo $errors_login; ?>
                                </div>
                        <?php } ?>
                        <form method="post" class="wc-login-form">
                                <div class="login_form">
                                        <div class="log_user">
                                                <label for="user_name">Username</label>
                                                <input name="log" type="text" id="user_name" value="<?php echo $_POST['log']; ?>">
                                        </div>
                                        <div class="log_pass">
                                                <label for="user_password">Password</label>
                                                <input name="pwd" id="user_password" type="password">
                                        </div>
                                        <?php
                                        ob_start();
                                        do_action('login_form');
                                        echo ob_get_clean();
                                        ?>
                                        <?php wp_nonce_field('userLogin', 'formType'); ?>
                                </div>
                                <button type="submit">LOG IN</button>
                        </form>
                        <?php
                } else {
                        echo '<p class="error-logged">You are already logged in.</p>';
                }

                $login_form = ob_get_clean();
                return $login_form;
        }

        public function wc_user_login_callback() {
                if (isset($_POST['formType']) && wp_verify_nonce($_POST['formType'], 'userLogin')) {

                        global $errors_login;
                        $uName = $_POST['log'];
                        $uPassword = $_POST['pwd'];
                        $redirect = $_POST['redirect'];

                        if ($uName == '' && $uPassword != '') {
                                $errors_login = '<strong>Error! </strong> Username is required.';
                        } elseif ($uName != '' && $uPassword == '') {
                                $errors_login = '<strong>Error! </strong> Password is required.';
                        } elseif ($uName == '' && $uPassword == '') {
                                $errors_login = '<strong>Error! </strong> Username & Password are required.';
                        } elseif ($uName != '' && $uPassword != '') {
                                $creds = array();
                                $creds['user_login'] = $uName;
                                $creds['user_password'] = $uPassword;
                                $creds['remember'] = false;
                                $user = wp_signon($creds, false);
                                if (is_wp_error($user)) {
                                        $errors_login = $user->get_error_message();
                                } else {
                                        $wc_sel_calender_option = get_option('wc_sel_calender_page');
                                        wp_redirect(get_page_link($wc_sel_calender_option));
                                        exit;
                                }
                        }
                }
        }


        public function wc_logged_in_redirect() {
                $wc_sel_calender_option = get_option('wc_sel_calender_page');
                $wc_sel_login_option    = get_option('wc_sel_login_page');
                $post_data = get_post($wc_sel_login_option);

                if ( !is_user_logged_in() && is_page( $wc_sel_calender_option ) ) 
                {
                        wp_redirect( site_url( $post_data->post_name ) );
                        die;
                }

        }


        public function wc_calender_month_function(){
                ob_start();
                $wc_sel_login_option = get_option('wc_sel_login_page');
                ?>
                <div class="section" id="monthly-view">
                        <p style="text-align: right;"><a style="    background-color: #39c9ef;
                        color: #fff;
                        padding: 12px 20px;
                        text-transform: uppercase;
                        font-weight: 500;
                        border-radius: 3px;" href="<?php echo wp_logout_url(get_page_link($wc_sel_login_option)); ?>">Logout</a></p>
                        <div class="timetable-example">
                                <div class="tiva-timetable" data-view="month" data-start="monday"></div>                        
                        </div>
                </div>

                <script>
                        jQuery(document).ready(function($){
                                jQuery('.tiva-timetable').each(function(index) {
                                        jQuery(this).attr('id', 'timetable-' + (index + 1));

                                        var timetable_contain = jQuery(this);

                                        var data = {
                                                action: 'wc_get_data_by_user',
                                                user_id: <?php echo get_current_user_id(); ?>
                                        }

                                        jQuery.ajax({
                                                data: data,
                                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                                dataType: 'json',
                                                method: "POST",
                                                beforeSend : function(){
                                                        timetable_contain.html('<div class="loading"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif" /></div>');
                                                },
                                                success: function(data) {

                                                        tiva_timetables = [];

                                                        for (var i = 0; i < data.length; i++) {
                                                                tiva_timetables.push(data[i]);
                                                        }

                                                        tiva_timetables.sort(sortByTime);

                                                        for (var j = 0; j < tiva_timetables.length; j++) {
                                                                tiva_timetables[j].id = j;
                                                        }

                                                        var todayDate = new Date();
                                                        var date_start = (typeof timetable_contain.attr('data-start') != "undefined") ? timetable_contain.attr('data-start') : 'sunday';
                                                        if (date_start == 'sunday') {
                                                                var tiva_current_week = new Date(todayDate.setDate(tiva_current_date.getDate() - todayDate.getDay()));
                                                        } else {
                                                                var today_date = (todayDate.getDay() == 0) ? 7 : todayDate.getDay();
                                                                var tiva_current_week = new Date(todayDate.setDate(tiva_current_date.getDate() - today_date + 1));
                                                        }
                                                        createTimetable(timetable_contain, 'current', tiva_current_week, tiva_current_month, tiva_current_year);
                                                }
                                        });     

                                });

                                $(document).on('click', '.wc-actions button.btn-success', function (){
                                        var data = {
                                                action: 'wc_update_status',
                                                post_id: $(this).attr('data-id'), 
                                                status: $(this).attr('data-action'),
                                        }

                                        jQuery.ajax({
                                                data: data,
                                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                                method: "POST",
                                                success: function (resp) {
                                                        $('#post-'+resp).remove();
                                                        $('#reason-'+resp).remove();
                                                }
                                        });
                                });

                                $(document).on('click', '.wc-actions button.btn-warning', function (){
                                        var post_id = $(this).attr('data-id');
                                        $('#reason-'+post_id ).show();
                                });

                                $(document).on('click', '.timetable-desc button.submit', function (){
                                        var post_id = $(this).attr('data-id');
                                        var reason = $('#reason-'+post_id ).find('textarea').val();

                                        if(reason == '') {
                                                $('#reason-'+post_id ).find('textarea').addClass('required');
                                        } else {
                                                var data = {
                                                        action: 'wc_update_status',
                                                        post_id: $(this).attr('data-id'), 
                                                        status: $(this).attr('data-action'),
                                                        reason: reason
                                                }

                                                jQuery.ajax({
                                                        data: data,
                                                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                                        method: "POST",
                                                        success: function (resp) {
                                                                $('#post-'+resp).remove();
                                                                $('#reason-'+resp).remove();
                                                        }
                                                });
                                        }
                                });

                        });
                </script>
                <?php
                return ob_get_clean();
        }

        public function wc_update_status(){
                $current_user = wp_get_current_user();
                $post_id = $_POST['post_id'];
                $status = $_POST['status'];
                $reason = $_POST['reason'];

                update_post_meta($post_id, '_status', $status);

                if(!empty($reason)) {
                        $reasons = get_post_meta($post_id, '_reasons', true);
                        if(empty($reasons)) {
                                $reasons = array();
                        }

                        $reasons[date('Y-m-d H:i:s')] = $reason;

                        update_post_meta($post_id, '_reasons', $reasons);
                }

                $to = get_option('wc_admin_email');
                $subject = get_the_title($post_id).' has beed '.$status.' by '.esc_html( $current_user->user_login );
                $body = '<strong>Name:</strong> '.get_the_title($post_id).'<br/>';
                $body .= '<strong>Status:</strong> '.$status.'<br/>';
                if(!empty($reason)) {
                        $body .= '<strong>Reason:</strong> '.$reason.'<br/>';
                }
                $headers = array('Content-Type: text/html; charset=UTF-8');

                wp_mail( $to, $subject, $body, $headers );

                echo $post_id;
                die();
        }

        public function update_user_transient_funct( $post_id ) {
                if( 'wc_calendars' == get_post_type( $post_id ) ) {
                        $this->save_user_product_order();
                }
        }

        public function save_user_product_order() {
                $args = array(
                        'post_type' => 'wc_calendars',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'meta_value_datetime',
                        'meta_key' => '_start_date',
                        'order' => 'ASC',
                        'fields' => 'ids',
                        'meta_query' => array(
                                array(
                                        'key'     => '_select_user',
                                        'value'   => get_current_user_id(),
                                        'compare' => '=',
                                ),
                        ),
                );
                $exhibs = new WP_Query( $args );
                $exhibs = $exhibs->posts;
                set_transient( 'saved_posts_by_user_'.get_current_user_id(), $exhibs, 1 );
        }

        function pf_get_adjacent_exhibition_link( $previous ) {
                $post_id = get_the_ID();

                //$exhibs = get_transient( 'saved_posts_by_user_'.get_current_user_id() );
                $exhibs = false;
                if( false == $exhibs ) {
                        $this->save_user_product_order();
                        $exhibs = get_transient( 'saved_posts_by_user_'.get_current_user_id() );
                }

                $pos = array_search( $post_id, $exhibs );
                if( $previous ) {
                        $new_pos = $pos - 1;
                } else {
                        $new_pos = $pos + 1;
                }

                if( $exhibs[$new_pos] ) {
                        $prev_id = $exhibs[$new_pos];
                        return $prev_id;
                }

                return false;
        }

        public function wc_get_data_by_user_fuction(){

                $timetables = array();
                $args = array(
                        'post_type' => 'wc_calendars',
                        'post_status' => 'publish',
                        'meta_key'   => '_select_user',
                        'meta_value' => get_current_user_id(),
                        'posts_per_page' => -1,
                );

                $my_query = null;
                $my_query = new WP_Query($args);

                if ($my_query->have_posts()) {

                        while ($my_query->have_posts()) : $my_query->the_post();

                                $user_id = get_post_meta( get_the_ID(), '_select_user', true );
                                $date = get_post_meta( get_the_ID(), '_start_date', true );
                                $time = get_post_meta( get_the_ID(), '_start_time', true );

                                $description = apply_filters( 'the_content', get_the_content() );

                                $term_obj_list = get_the_terms( get_the_ID(), 'wc_calendars_tags' );
                                if(!empty($term_obj_list)) {
                                        $terms_string = '<p>#'.join('&nbsp;&nbsp; #', wp_list_pluck($term_obj_list, 'name')).'</p>';
                                        $description .= $terms_string;
                                }

                                $status = get_post_meta( get_the_ID(), '_status', true );
                                $color = 4;

                                if($status == 'pending') {
                                        $color = 2;
                                } else if($status == 'approved'){
                                        $color = 1;
                                }

                                if($status == 'pending') {
                                        $description .= '<div class="col-sm-12 wc-actions" id="post-'.get_the_ID().'"><br/><div class="col-sm-6 pl"><button type="button" data-action="approved" data-id="'.get_the_ID().'" class="btn btn-success">Approve</button></div><div class="col-sm-6 pr"><button type="button" data-action="rejected" data-id="'.get_the_ID().'" class="btn btn-warning">Reject</button></div></div><br/><br/><div id="reason-'.get_the_ID().'" style="display:none; text-align: center;"><textarea style="margin-top: 20px;" name="reject_reason"></textarea><button type="button" data-id="'.get_the_ID().'" class="btn btn-info submit" data-action="rejected">Submit Reason</button></div>';
                                }

                                $timetable = new stdClass();
                                $timetable->name = get_the_title();
                                $timetable->post_date = get_the_date();
                                $timetable->post_id = get_the_ID();
                                $timetable->image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                                $timetable->date = date('j', strtotime($date));
                                $timetable->month = date('n', strtotime($date));
                                $timetable->year = date('Y', strtotime($date));
                                $timetable->start_time = $time ? date('H:i', strtotime($time)) : '';
                                $timetable->color = $color;
                                $timetable->description = utf8_encode(nl2br($description));

                                array_push($timetables, $timetable);

                        endwhile;
                }

                wp_reset_query();

                echo json_encode($timetables);

                die();
        }

        public function wc_magnific_popup(){
                $post_id = $_REQUEST['id'];

                $args = array(
                        'post_type'     => 'wc_calendars',
                        'post_status' => 'publish',
                        'meta_key'   => '_select_user',
                        'meta_value' => get_current_user_id(),
                        'p' => $post_id,
                        'posts_per_page' => 1,
                );

                $my_query = null;
                $my_query = new WP_Query($args);

                if ($my_query->have_posts()) {

                        while ($my_query->have_posts()) : $my_query->the_post();

                                $status = get_post_meta( get_the_ID(), '_status', true );
                                $color = 4;

                                $previous_post = get_previous_post();
                                $next_post = get_next_post();

                                if($status == 'pending') {
                                        $color = 2;
                                } else if($status == 'approved'){
                                        $color = 1;
                                }
                                $start_date = get_post_meta( get_the_ID(), '_start_date', true );
                                $time = get_post_meta( get_the_ID(), '_start_time', true );
                                $description = apply_filters( 'the_content', get_the_content() );

                                $term_obj_list = get_the_terms( get_the_ID(), 'wc_calendars_tags' );
                                if(!empty($term_obj_list)) {
                                        $terms_string = '<p>#'.join('&nbsp;&nbsp; #', wp_list_pluck($term_obj_list, 'name')).'</p>';
                                        $description .= $terms_string;
                                }

                                if($status == 'pending') {
                                        $description .= '<div class="col-sm-12 wc-actions" id="post-'.get_the_ID().'"><br/><div class="col-sm-6 pl"><button type="button" data-action="approved" data-id="'.get_the_ID().'" class="btn btn-success">Approve</button></div><div class="col-sm-6 pr"><button type="button" data-action="rejected" data-id="'.get_the_ID().'" class="btn btn-warning">Reject</button></div></div><br/><br/><div id="reason-'.get_the_ID().'" style="display:none; text-align: center;"><textarea style="margin-top: 20px;" name="reject_reason"></textarea><button type="button" data-id="'.get_the_ID().'" class="btn btn-info submit" data-action="rejected">Submit Reason</button></div>';
                                }

                                ?>
                                <?php $previous = $this->pf_get_adjacent_exhibition_link(true); ?>
                                <div id="popup-<?php echo get_the_ID(); ?>" class="timetable-popup zoom-anim-dialog">
                                        <button title="Previous (Left arrow key)" type="button" <?php echo ($previous)? 'id="'.$previous.'"' : 'disabled'; ?> class="wc-mfp-arrow mfp-arrow mfp-arrow-left mfp-prevent-close"></button>
                                        <div class="popup-header color-<?php echo $color; ?>">
                                                <?php echo get_the_title(get_the_ID()); ?>
                                        </div>
                                        <div class="popup-body">
                                                <?php
                                                $img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                                                if(!empty($img_url)) {
                                                        ?>
                                                        <div class="timetable-image"><img src="<?php echo $img_url; ?>" alt="<?php echo get_the_title(get_the_ID()); ?>" /></div>
                                                <?php } ?>
											
												<?php
                                                	$featured_video = get_post_meta( get_the_ID(), 'wp_custom_attachment', true );
                                                	if(!empty($featured_video)) {
                                                 ?>
        <div class="feture_video"><video controls="controls" preload="auto" width="100%" height="100%"><source src="<?php echo $featured_video['url']; ?>" type="video/mp4" /></video></div>
                                                <?php } ?>
											
                                                <div class="timetable-time color-<?php echo $color; ?>"><?php echo date('F j, Y', strtotime($start_date)); ?> <?php echo ($time) ? date('g:i A', strtotime($time)) : ''; ?></div>
                                                <div class="timetable-desc"><?php echo $description; ?></div>
                                        </div>
                                        <button title="Close (Esc)" type="button" class="mfp-close">×</button>
                                        <?php $next = $this->pf_get_adjacent_exhibition_link(false); ?>
                                        <button title="Next (Right arrow key)" type="button" <?php echo ($next)? 'id="'.$next.'"' : 'disabled'; ?> class="wc-mfp-arrow mfp-arrow mfp-arrow-right mfp-prevent-close"></button>
                                </div>
                                <?php

                        endwhile;
                }
                wp_reset_query();

                die;
        }

        public function wc_calender_list_function(){
                ob_start();
                ?>
                <div class="section" id="list-view">
                        <h2>List View</h2>
                        <p>Display all your post list view</p>
                        <div class="timetable-example">
                                <div class="tiva-timetable" data-view="list" data-mode="day"></div>
                        </div>
                </div>
                <?php
                return ob_get_clean();
        }

        /**
         * Register the stylesheets for the public-facing side of the site.
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

                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webbycrown-public.css', array(), $this->version, 'all' );
                wp_enqueue_style( $this->plugin_name.'-magnific-popup', plugin_dir_url( __FILE__ ) . 'css/magnific-popup.css', array(), $this->version, 'all' );
                wp_enqueue_style( $this->plugin_name.'-timetable', plugin_dir_url( __FILE__ ) . 'css/timetable.css', array(), $this->version, 'all' );
                wp_enqueue_style( $this->plugin_name.'-font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );

        }

        /**
         * Register the JavaScript for the public-facing side of the site.
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

                wp_enqueue_script( $this->plugin_name.'-magnific-popup-script', plugin_dir_url( __FILE__ ) . 'js/jquery.magnific-popup.js', array( 'jquery' ), $this->version, false );
                wp_enqueue_script( $this->plugin_name.'-timetable-script', plugin_dir_url( __FILE__ ) . 'js/timetable.js', array( 'jquery' ), $this->version, false );
                wp_localize_script($this->plugin_name.'-timetable-script', 'myAjax', array( 'ajaxurl' => admin_url('admin-ajax.php')));
                wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/webbycrown-public.js', array( 'jquery' ), $this->version, false );
                wp_localize_script($this->plugin_name, 'myAjax', array( 'ajaxurl' => admin_url('admin-ajax.php')));

        }

}


if( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

include 'recordlist.php';
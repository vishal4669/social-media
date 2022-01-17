<?php

class Record_List_Table extends WP_List_Table {

  public function prepare_items() {

    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();

    $data = $this->table_data();
    //usort( $data, array( &$this, 'sort_data' ) );

    $perPage = 50;
    $currentPage = $this->get_pagenum();
    $totalItems = count($data);

    $this->set_pagination_args( array(
      'total_items' => $totalItems,
      'per_page'    => $perPage
  ) );

    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->items = $data;
}

public function get_columns() {

    $columns = array(
      'post_name'   => 'Post Name',
      'description'  => 'Description',
      'image'       => 'Image',
      'tags'        => 'Tags',
      'date'        => 'Date',
      'status'      => 'Status',
  );

    return $columns;
}

public function get_hidden_columns() {
    return array();
}

public function get_sortable_columns() {
    return '';
}

private function table_data() {
    $data = array();
    $s_from         = isset( $_REQUEST['s_from'] ) ? wp_unslash( trim( $_REQUEST['s_from'] ) ) : '';
    $s_to           = isset( $_REQUEST['s_to'] ) ? wp_unslash( trim( $_REQUEST['s_to'] ) ) : '';
    $wc_sel_user    = isset( $_REQUEST['wc_sel_user'] ) ? wp_unslash( trim( $_REQUEST['wc_sel_user'] ) ) : '';
    $wc_sel_status  = isset( $_REQUEST['wc_sel_status'] ) ? wp_unslash( trim( $_REQUEST['wc_sel_status'] ) ) : '';

    $args_query = array(
      'post_type' => array('wc_calendars'),
      'post_status' => array('publish'),
      'posts_per_page' => -1
  );

    if(!empty($s_from) && !empty($s_to) && ($wc_sel_user !== '') && ($wc_sel_status !== '')) {

        $s_from = date("Y-m-d", strtotime($s_from));
        $s_to   = date("Y-m-d", strtotime($s_to));

        $args_query['meta_query'] = array(

            array(
                'key'     => '_start_date',   
                'value'   => array($s_from,$s_to),	
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),
            array(
                'key' => '_select_user',
                'value' => $wc_sel_user,
                'compare' => '='
            ),
            array(
                'key' => '_status',
                'value' => $wc_sel_status,
                'compare' => '='
            ),

        );

    }else if(!empty($s_from) && !empty($s_to) && ($wc_sel_user !== '') && ($wc_sel_status == '')){

        $s_from = date("Y-m-d", strtotime($s_from));
        $s_to   = date("Y-m-d", strtotime($s_to));

        $args_query['meta_query'] = array(

            array(
                'key'     => '_start_date',   
                'value'   => array($s_from,$s_to),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),
            array(
                'key' => '_select_user',
                'value' => $wc_sel_user,
                'compare' => '='
            ),

        );
    }else if(!empty($s_from) && !empty($s_to) && ($wc_sel_user == '') && ($wc_sel_status == '')){

        $s_from = date("Y-m-d", strtotime($s_from));
        $s_to   = date("Y-m-d", strtotime($s_to));

        $args_query['meta_query'] = array(

            array(
                'key'     => '_start_date',   
                'value'   => array($s_from,$s_to),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),

        );
    }else if(!empty($s_from) && !empty($s_to) && ($wc_sel_user == '') && ($wc_sel_status !== '')){
        $s_from = date("Y-m-d", strtotime($s_from));
        $s_to   = date("Y-m-d", strtotime($s_to));

        $args_query['meta_query'] = array(

            array(
                'key'     => '_start_date',   
                'value'   => array($s_from,$s_to),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),
            array(
                'key' => '_status',
                'value' => $wc_sel_status,
                'compare' => '='
            ),

        );
    }else if(empty($s_from) && empty($s_to) && ($wc_sel_user !== '') && ($wc_sel_status !== '')){
        $args_query['meta_query'] = array(

            array(
                'key' => '_select_user',
                'value' => $wc_sel_user,
                'compare' => '='
            ),
            array(
                'key' => '_status',
                'value' => $wc_sel_status,
                'compare' => '='
            ),

        );
    }else if(empty($s_from) && empty($s_to) && ($wc_sel_user == '') && ($wc_sel_status !== '')){
        $args_query['meta_query'] = array(

            array(
                'key' => '_status',
                'value' => $wc_sel_status,
                'compare' => '='
            ),

        );
    }else if(empty($s_from) && empty($s_to) && ($wc_sel_user !== '') && ($wc_sel_status == '')){
        $args_query['meta_query'] = array(
            array(
                'key' => '_select_user',
                'value' => $wc_sel_user,
                'compare' => '='
            ),

        );
    }
   // print_r( $args_query);

$query = new WP_Query( $args_query );

if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();

      $post_terms = get_the_terms(get_the_ID(), 'wc_calendars_tags');
      $cat_data = '';
      if (!empty($post_terms) && !is_wp_error($post_terms)) {
        foreach ($post_terms as $post_term) {
          $cat_data .= $post_term->name . ', ';
      }
  }
  $cat_data = trim($cat_data, ', ');

  $post_start_date = get_post_meta(get_the_ID(),'_start_date',true);
  $post_status = get_post_meta(get_the_ID(),'_status',true);

  $data[] = array(
    'post_name'       => get_the_title(),
    'description'     => get_the_content(),
    'image'           => '<img width="150px" src="'.get_the_post_thumbnail_url().'">',
    'tags'            =>  $cat_data,
    'date'            => $post_start_date,
    'status'          => $post_status
);
}
}

wp_reset_postdata();

return $data;
}

public function search_box( $text, $input_id ) { ?>
  <p class="search-box">
    <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
    <?php
    $blogusers = get_users( [ 'role__in' => [ 'author', 'subscriber' ] ] );
    $user_id = $_REQUEST['wc_sel_user'];
    $status = $_REQUEST['wc_sel_status'];
    if(!empty($blogusers)) {
      echo '<h4 for="select_user">Select User:</h4>';
      echo '<select id="wc_sel_user" name="wc_sel_user">';
      echo '<option value="">Select User</option>';
      foreach ( $blogusers as $user ) {
        $selected = ($user->ID == $user_id)? 'selected="selected"' : '';
        echo '<option value="'.$user->ID.'" '.$selected.'>' . esc_html( $user->display_name ) . '</option>';
    }
    echo '</select>';
}
?>
<h4 for="status">Date:</h4>
<input type="search" id="<?php echo $input_id ?>-from" name="s_from" autocomplete="off" placeholder="From" value="<?php echo $_REQUEST['s_from']; ?>" />

<input type="search" id="<?php echo $input_id ?>-to" name="s_to" autocomplete="off" placeholder="To" value="<?php echo $_REQUEST['s_to']; ?>" />

<h4 for="status">Status:</h4>
<select id="wc_sel_status" name="wc_sel_status">
  <option value="" >Select Status</option>
  <option value="pending" <?php echo ($status == "pending")? 'selected="selected"': ''; ?>>Pending</option>
  <option value="approved" <?php echo ($status == "approved")? 'selected="selected"': ''; ?>>Approved</option>
  <option value="rejected" <?php echo ($status == "rejected")? 'selected="selected"': ''; ?>>Rejected</option>
</select>

<input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
<?php submit_button( $text, 'button', false, false, array('id' => 'wc-search-submit') ); ?>
</p>
<?php
}



public function column_default( $item, $column_name ) {
  switch( $column_name ) {
    case 'post_name':
    case 'description':
    case 'image':
    case 'tags':
    case 'date':
    case 'status':
    return $item[ $column_name ];

    default:
    return print_r( $item, true ) ;
}
}

private function sort_data( $a, $b ) {

  $orderby = 'date';
  $order = 'asc';

  if(!empty($_GET['orderby'])) {
    $orderby = $_GET['orderby'];
}

if(!empty($_GET['order'])) {
    $order = $_GET['order'];
}


$result = strcmp( $a[$orderby], $b[$orderby] );

if($order === 'asc') {
    return $result;
}

return -$result;
}
}
<?php
/*
Plugin Name: HT Events listing
Plugin URI: http://www.helpfultechnology.com
Description: Display future events
Author: Luke Oatham
Version: 0.1
Author URI: http://www.helpfultechnology.com
*/

class htEventsListing extends WP_Widget {
    function htEventsListing() {
        parent::WP_Widget(false, 'HT Events listing', array('description' => 'Events listing widget'));
    }

    function widget($args, $instance) {
	    extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $items = intval($instance['items']);
        $thumbnails = ($instance['thumbnails']);

		//display forthcoming events
		$tzone = get_option('timezone_string');
		date_default_timezone_set($tzone);
		$sdate=date('Ymd');

		$cquery = array(

		   'meta_query' => array(
		       array(
	           		'key' => 'event_start_date',
	        	   'value' => $sdate,
	    	       'compare' => '>=',
	    	       'type' => 'DATE' 
	    	       ) 
		        ),   
			    'orderby' => 'meta_value',
			    'meta_key' => 'event_start_date',
			    'order' => 'ASC',
			    'post_type' => 'event',
				'posts_per_page' => $items,
		);

		$news =new WP_Query($cquery);
		if ($news->post_count!=0){
			echo "
		    <style>
			.upcoming-events .date-stamp {
				border: 3px solid ".get_option('general_intranet_header_background').";
			}
			.upcoming-events .date-stamp em {
				background: ".get_option('general_intranet_header_background').";
				
			}
		    </style>
		    ";

			echo $before_widget; 


			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo "<div class='widget-area widget-events'>";
			if ($thumbnails!='on') echo "<div class='upcoming-events'><ul>";
		}
		$k=0;
		$alreadydone= array();

		while ($news->have_posts()) {
			$news->the_post();
			if (in_array($post->ID, $alreadydone )) { //don't show if already in stickies
				continue;
			}
			$k++;
			if ($k > $items){
				break;
			}
			global $post;//required for access within widget
			$thistitle = get_the_title($post->ID);
			$edate = get_post_meta($post->ID,'event_start_date',true);
			$etime = get_post_meta($post->ID,'event_start_time',true);
			$edate = date('D j M',strtotime($edate));
			$edate .= " ".date('g:ia',strtotime($etime));


			$thisURL=get_permalink($ID); 
			if ($thumbnails=='on'){
				$image_uri =  wp_get_attachment_image_src( get_post_thumbnail_id( $ID ), 'thumbnail' ); 
				if ($image_uri!="" ){
					echo "<div class='media'>";
					echo "<a class='pull-right' href='".site_url()."/event/".$post->post_name."/'><img class='tinythumb' src='{$image_uri[0]}' alt='".$thistitle."' /></a>";		
					echo "<div class='media-body'><a href='{$thisURL}'> ".$thistitle."</a><br><small>".$edate."</small>";
					echo "</div></div>";
				} else {
					echo "<div class='media'><a href='{$thisURL}'> ".$thistitle."</a><br><small>".$edate."</small></div>";
				} 
			} else {
				echo "<li><a href='".site_url()."/event/".$post->post_name."/'><span class='date-stamp'><em>".date('M',strtotime(get_post_meta($post->ID,'event_start_date',true)))."</em>".date('d',strtotime(get_post_meta($post->ID,'event_start_date',true)))."</span>".$thistitle."<span>".get_post_meta($post->ID,'event_location',true)."</span></a></li>";
			}
		}


		if ($news->post_count!=0){
		if ($thumbnails!='on') echo "</ul></div>";
			echo '<hr><p><strong><a title="More in events" class="small" href="'.site_url().'/events/">More in events</a></strong> <i class="glyphicon glyphicon-chevron-right small"></i></p></div>';
			echo $after_widget;
		}
		
		wp_reset_query();								

    }

    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['items'] = strip_tags($new_instance['items']);
		$instance['thumbnails'] = strip_tags($new_instance['thumbnails']);
       return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        $items = esc_attr($instance['items']);
        $thumbnails = esc_attr($instance['thumbnails']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br><br>

          <label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" type="text" value="<?php echo $items; ?>" /><br><br>
          
          <input id="<?php echo $this->get_field_id('thumbnails'); ?>" name="<?php echo $this->get_field_name('thumbnails'); ?>" type="checkbox" <?php checked((bool) $instance['thumbnails'], true ); ?> />
          <label for="<?php echo $this->get_field_id('thumbnails'); ?>"><?php _e('Show thumbnails'); ?></label> <br>

        </p>

        <?php 
    }

}
		wp_register_style( 'ht-events-listing', plugin_dir_url( ).  "ht-events-listing/ht_events_listing.css");
		wp_enqueue_style( 'ht-events-listing' );

add_action('widgets_init', create_function('', 'return register_widget("htEventsListing");'));

?>
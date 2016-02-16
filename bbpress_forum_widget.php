<?php
/*Widget class for Join the discussion widget on bbpress. Use this widget to display list of private and public forums 
from bbpress to attract users to recent discussions. 
*/

add_action( 'widgets_init', function(){
     register_widget( 'bbp_custom_forum_widget' );
});

if(!class_exists('bbp_custom_forum_widget')) {
//begin class
class bbp_custom_forum_widget extends WP_Widget {
 
    public function __construct() {
        // widget actual processes
		 $widget_ops = array( 'classname' => 'bbp_custom_forum_widget', 'description' => __( 'A list of forums to show private and published discussion forums.' ) );
		parent::__construct( false, __( '(bbPress) Join the discussion!' ), $widget_ops );
		add_shortcode( 'forum_discussion', array( $this, 'forum_shortcode' ));
    }
	
	public function forum_shortcode() {
		$content_return = "";
		$content_return = $this->forum_discussion_content($content_return);
		return $content_return;
    }
    public function widget( $args, $instance ) {	
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		$content_return = "";
		$content_return = $this->forum_discussion_content($content_return);
		echo $content_return;
		echo $args['after_widget'];
    }
	
	public function forum_discussion_content($content_return){
		global $wpdb;
		//get userid
		$current_user_id = get_current_user_id();
		//get forum posts 
		$forum_posts = $wpdb->get_results("SELECT * FROM `wp_posts` WHERE post_type = 'forum' AND (post_status = 'publish' OR post_status = 'private')");
		
		foreach($forum_posts as $forum_post){
		
			$content_str = "";
			if($forum_post->post_status == 'publish' && $current_user_id == 0){
				$content_return .= "<a class='bbp-forum-title' href='/forum/".$forum_post->post_name."/'>".$forum_post->post_title."</a><br>";
			}elseif($forum_post->post_status == 'publish' && $current_user_id > 0){
				$content_return .= "<a class='bbp-forum-title' href='/forum/".$forum_post->post_name."/'>".$forum_post->post_title."</a><br>";
			}
			if($forum_post->post_status == 'private' && $current_user_id > 0){
				$forum_group_id = $wpdb->get_var("SELECT meta_value FROM `wp_postmeta` WHERE post_id = ".$forum_post->ID." AND meta_key = 'user-group-content'");
				if($forum_group_id > 0){
					$term_tax_id = $wpdb->get_var("SELECT term_taxonomy_id FROM `wp_term_taxonomy` where term_id = ".$forum_group_id."");
					if($term_tax_id > 0){
						$group_user_id = $wpdb->get_var("SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id = ".$term_tax_id." AND object_id = ".$current_user_id);
						if($group_user_id == $current_user_id){
							$content_return .= "<a class='bbp-forum-title' href='/forum/".$forum_post->post_name."/'>".$forum_post->post_title."</a><br>";
						}
					}
				}
			}			
		}
		return 	$content_return;	
	}
 
    public function form( $instance ) {
      if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Recent Discussions!', 'text_domain' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
    }
 
   /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}//end of bbpress custom forum widget class
}
?>
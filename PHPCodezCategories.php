<?php

/**

* Plugin Name: PHPCodez Categories

* Plugin URI: http://phpcodez.com/

* Description: A Widget That Displays Categories

* Version: 0.1

* Author: PHPCodez

* Author URI: http://phpcodez.com/

*/



add_action( 'widgets_init', 'wpc_categories_widgets' );



function wpc_categories_widgets() {

	register_widget( 'wpccategoriesWidget' );

}



class wpccategoriesWidget extends WP_Widget {

	function wpccategoriesWidget() {

		$widget_ops = array( 'classname' => 'wpcClass', 'description' => __('A Widget That Displays Categories.', 'wpcClass') );

		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wpc-categories' );

		$this->WP_Widget( 'wpc-categories', __('PHPCodez Categories', ''), $widget_ops, $control_ops );

	}



	function show_subcats($cat,$inc=0) {

		$args=array('child_of' => $cat);

		$artists =  get_categories($args);

		$i=0;

		//print_r($artists);

		if($inc)

			$catogries[$i++] =$cat;

		if(!empty($artists)){

			foreach($artists as $values)

			$catogries[$i++] =$values->term_id;

			return $catogries; 

		}

		else

			return 0;	

	}

	

	

	function widget( $args, $instance ) {

		extract( $args );

		global $wpdb;

		if($instance['category_count']) $limit =" LIMIT 0,".$instance['category_count'];

		if($instance['category_sort']) $order_by =" ORDER BY ".$instance['category_sort'];

		if($instance['category_order']) $order_by .=" " .$instance['category_order'];

		if($instance['category_exclude']) $exlucde .=" AND  c.term_id NOT IN(".$instance['category_exclude'].")  ";

		

		$categoryQry="SELECT c.*,ct.* FROM {$wpdb->prefix}terms as c JOIN  {$wpdb->prefix}term_taxonomy as ct  ON c.term_id=ct.term_id

						  WHERE ct.taxonomy='category' AND  ct.parent ='0' AND  c.slug!='uncategorized' $exlucde $order_by $limit ";

		$categoriesData = $wpdb->get_results($categoryQry);

?>

	<div class="arch_box" style="margin-left:10px;">

		<?php if($instance['category_title']) { ?>

			<div class="side_hd">

				<h3><?php echo $instance['category_title'] ?></h3>

			</div>

		<?php } ?>

		<div class="sider_mid">

			<ul>

			<?php

				foreach($categoriesData as $key=>$category) { $haveCategory=1;

			 ?>

				<li>

					<a href="<?php echo get_category_link($category->term_id); ?>">

						<?php echo $category->name; ?> <?php if($instance['category_posts']) { ?>(<?php echo $category->count ?>)<?php } ?>

					</a>

				</li>

			<?php

				if(!$instance['category_parent']) {

				$nCategories		=	$this->show_subcats($category->term_id,1);			

				if(!empty($nCategories)) {

					foreach($nCategories as $values) 

					$const	 .=	"'".$values."',";

					$const	  =	substr($const,0,strlen( $const)-1);

				}

				$subCategoriesData = $wpdb->get_results("SELECT c.*,ct.* FROM {$wpdb->prefix}terms as c 

				JOIN  {$wpdb->prefix}term_taxonomy as ct  ON c.term_id=ct.term_id 

				WHERE ct.taxonomy='category' AND ct.parent = '".$category->term_id."'  ");

			?>

				<ul>

					<?php foreach($subCategoriesData as $key=>$subCategory) { if( $category->name=="Uncategorized")continue;  ?>

						<li>

							<a href="<?php echo get_category_link($subCategory->term_id); ?>">

								<?php echo $subCategory->name; ?> <?php if($instance['category_posts']) { ?>(<?php echo $subCategory->count ?>)<?php } ?>

							</a>

						</li>

					<?php } ?>	

				</ul>

			<?php }} ?>

			

			<?php if(!$haveCategory){ ?>

				<li>No Categories Are Added Yet</li>

			<?php } ?>

			</ul>	

			

		</div>	

	</div>

<?php



}





function update( $new_instance, $old_instance ) {

	$instance = $old_instance;

	$instance['category_title']		=  $new_instance['category_title'] ;

	$instance['category_count'] 	=  $new_instance['category_count'] ;

	$instance['category_posts'] 	=  $new_instance['category_posts'] ;

	$instance['category_parent'] 	=  $new_instance['category_parent'] ;

	$instance['category_sort'] 		=  $new_instance['category_sort'] ;

	$instance['category_order'] 	=  $new_instance['category_order'] ;

	$instance['category_exclude'] 	=  $new_instance['category_exclude'] ;

	return $instance;

}



function form( $instance ) {?>

	<p>

		<label for="<?php echo $this->get_field_id( 'category_title' ); ?>"><?php _e('Title', 'wpclass'); ?></label>

		<input id="<?php echo $this->get_field_id( 'category_title' ); ?>" name="<?php echo $this->get_field_name( 'category_title' ); ?>" value="<?php echo $instance['category_title'] ?>"  type="text" width="99%" />

	</p>

	<p>

		<label for="<?php echo $this->get_field_id( 'category_parent' ); ?>"><?php _e('Display Only Root Categories', 'wpclass'); ?></label>

		<input id="<?php echo $this->get_field_id( 'category_parent' ); ?>" name="<?php echo $this->get_field_name( 'category_parent' ); ?>" value="1" <?php if($instance['category_parent']) echo 'checked="checked"'; ?> type="checkbox" />

	</p>

	<p>

		<label for="<?php echo $this->get_field_name( 'category_sort' ); ?>"><?php _e('Order BY', 'wpclass'); ?></label>

		<select id="<?php echo $this->get_field_name( 'category_sort' ); ?>" name="<?php echo $this->get_field_name( 'category_sort' ); ?>">

			<option value="name"  <?php if($instance['category_sort']=="name") echo 'selected="selected"'; ?>>Name</option>

			<option value="c.term_id"  <?php if($instance['category_sort']=="c.term_id") echo 'selected="selected"'; ?>>ID</option>

			<option value="count"  <?php if($instance['category_sort']=="count") echo 'selected="selected"'; ?>>No Of Posts</option>

		</select>

		<select id="<?php echo $this->get_field_name( 'category_order' ); ?>" name="<?php echo $this->get_field_name( 'category_order' ); ?>">

			<option value="ASC" <?php if($instance['category_order']=="ASC") echo 'selected="selected"'; ?>>ASC</option>

			<option value="DESC" <?php if($instance['category_order']=="DESC") echo 'selected="selected"'; ?>>DESC</option>

		</select>

	</p>

	<p>

		<label for="<?php echo $this->get_field_id( 'category_count' ); ?>"><?php _e('Number of parent categories . for "0" or "No Value" It will list all the categories', 'wpclass'); ?></label>

		<input id="<?php echo $this->get_field_id( 'category_count' ); ?>" name="<?php echo $this->get_field_name( 'category_count' ); ?>" value="<?php echo $instance['category_count'] ?>"  type="text" />

	</p>

	<p>

		<label for="<?php echo $this->get_field_id( 'category_exclude' ); ?>"><?php _e('Exclude category - Enter category ids to be excluded (example 5,78,90)', 'wpclass'); ?></label>

		<input id="<?php echo $this->get_field_id( 'category_exclude' ); ?>" name="<?php echo $this->get_field_name( 'category_exclude' ); ?>" value="<?php echo $instance['category_exclude'] ?>"  type="text" />

	</p>

	<p>

		<label for="<?php echo $this->get_field_id( 'category_posts' ); ?>"><?php _e('Display Post Count', 'wpclass'); ?></label>

		<input id="<?php echo $this->get_field_id( 'category_posts' ); ?>" name="<?php echo $this->get_field_name( 'category_posts' ); ?>" value="1" <?php if($instance['category_posts']) echo 'checked="checked"'; ?> type="checkbox" />

	</p>

<?php

	}

}



?>

<?php

	if(!class_exists('Portfolion'))
	{
		class Portfolion
		{
			private $plugin_url;
		
			public function __construct()
			{
				$this->plugin_url = plugins_url('portfolion');
			
				add_action('init', array($this, 'init_portfolion'));
				add_action('admin_head', array($this, 'override_icons'));
				add_action('add_meta_boxes', array($this, 'add_custom_boxes'));
				add_action('save_post', array($this, 'save_postdata'), 10, 2);
				add_action('right_now_content_table_end', array($this, 'add_project_count'));
				add_action('manage_posts_custom_column', array($this, 'display_columns'), 10, 2);
				
				add_filter('manage_edit-portfolio_columns', array($this, 'edit_columns'));
				add_filter('the_project_link', array($this, 'build_project_link'), 1);
				
			}
			
			public function build_project_link($args)
			{
				extract($args);
				
				if($value = $this->get_meta_value('_portfolio_project_link'))
				{
					$link = $before.$value.$after;
					if($display == true) echo $link;
					else return $link;
				}
				
				return null;
			}
			
			public function add_custom_boxes()
			{
				add_meta_box('project_link', __('Project Link'), array($this, 'build_project_link_meta_box'), 'portfolio', 'normal');
			}
			
			public function save_postdata($post_id, $post)
			{
				$fields = array('portfolio_project_link');

				foreach($fields as $field)
				{
					if(!isset($_POST[$field])) return;
				}
				
				if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
				if(!wp_verify_nonce($_POST['portfolio_noncename'], 'portfolio')) return;
				if(!current_user_can('edit_'.($_POST['post_type'] == 'page' ? 'page' : 'post'), $post_id)) return;
				
				foreach($fields as $field)
				{
					$value = wp_filter_kses($_POST[$field]);
					$meta_key = '_'.$field;
					
					if(empty($value)) 
					{
						delete_post_meta($post_id, $meta_key, $value);
					}
					elseif(!update_post_meta($post_id, $meta_key, $value))
					{
						add_post_meta($post_id, $meta_key, $value, true);
					}
				}
			}
			
			public function build_project_link_meta_box()
			{
				wp_nonce_field('portfolio', 'portfolio_noncename');
				$value = esc_html($this->get_meta_value('_portfolio_project_link'));
				echo '<input id="portfolio_project_link" name="portfolio_project_link" type="text" style="width:100%" value="'.$value.'" />';
				echo '<p>The project link can be added to your theme using <code>the_project_link()</code>. ';
				echo 'Use <code>get_the_project_link()</code> to return the link. ';
				echo 'Use <code>has_the_project_link()</code> to check if the link exists.</p>';
			}
			
			public function get_meta_value($meta_key, $id = false)
			{
				global $post;
				return get_post_meta(($id !== false ? $id : $post->ID), $meta_key, true);
			}
			
			public function init_portfolion()
			{
				$this->register_post_type();
				$this->register_categories();
				$this->register_tags();
				
				$this->rewrite_taxonomies();
				
				add_theme_support('post-thumbnails', array('portfolio'));
			}
			
			private function rewrite_taxonomies()
			{
				global $wp_rewrite;
				
				$wp_rewrite->add_permastruct('portfolio_tag', '/portfolio/tag/%portfolio_tag%', false);
				add_rewrite_rule('^portfolio/tag/([^/]*)/?', 'index.php?portfolio_tag=$matches[1]', 'top');
				
				$wp_rewrite->add_permastruct('portfolio_category', '/portfolio/category/%portfolio_category%', false);
				add_rewrite_rule('^portfolio/category/([^/]*)/?', 'index.php?portfolio_category=$matches[1]', 'top');
			}
			
			public function add_project_count()
			{
		        if(!post_type_exists('portfolio')) return;
		
		        $num_posts = wp_count_posts('portfolio');
		        $num = number_format_i18n($num_posts->publish);
		        $text = _n('Project', 'Projects', intval($num_posts->publish));
		        
		        if( current_user_can('edit_posts'))
		        {
		            $num = "<a href='edit.php?post_type=portfolio'>$num</a>";
		            $text = "<a href='edit.php?post_type=portfolio'>$text</a>";
		        }
		        
		        echo '<td class="first b b-portfolio">' . $num . '</td>';
		        echo '<td class="t portfolio">' . $text . '</td>';
		        echo '</tr>';
		
		        if($num_posts->pending > 0)
		        {
		            $num = number_format_i18n($num_posts->pending);
		            $text = _n('Project Pending', 'Projects Pending', intval($num_posts->pending));
		            
		            if(current_user_can('edit_posts'))
		            {
		                $num = "<a href='edit.php?post_status=pending&post_type=portfolio'>$num</a>";
		                $text = "<a href='edit.php?post_status=pending&post_type=portfolio'>$text</a>";
		            }
		            
		            echo '<td class="first b b-portfolio">' . $num . '</td>';
		            echo '<td class="t portfolio">' . $text . '</td>';
		            echo '</tr>';
		        }
			}
			
			public function override_icons()
			{
				$img_url = $this->plugin_url . '/resources/images/';
				include('views/icons.php');
			}
			
			function edit_columns($portfolio_columns)
			{
				$portfolio_columns = array
				(
					"cb" => "<input type=\"checkbox\" />",
					"title" => __('Title'),
					"author" => __('Author'),
					"portfolio_category" => __('Categories'),
					"portfolio_tag" => __('Tags'),
					"date" => __('Date')
				);
				
				return $portfolio_columns;
			}
			
			public function display_columns($portfolio_columns, $post_id)
			{
				switch($portfolio_columns)
				{
					case "portfolio_category":				
						if($category_list = get_the_term_list($post_id, 'portfolio_category', '', ', ', '')) echo $category_list;
						else echo __('No Categories');
					break;	
					
					case "portfolio_tag":				
						if($tag_list = get_the_term_list($post_id, 'portfolio_tag', '', ', ', '')) echo $tag_list;
						else echo __('No Tags');
					break;
				}
			}
			
			private function register_post_type()
			{
				$labels = array
				(
					'name' => __('Portfolio'),
					'singular_name' => __('Project'),
					'add_new' => __('Add New'),
					'all_items' => __('All Projects'),
					'add_new_item' => __( 'Add New Project'),
					'edit_item' => __('Edit Project'),
					'new_item' => __('Add New Project'),
					'view_item' => __('View Project'),
					'search_items' => __('Search Portfolio'),
					'not_found' => __('No projects found.'),
					'not_found_in_trash' => __( 'No projects found in trash.')
				);
			
				$args = array
				(
			    	'labels' => $labels,
			    	'public' => true,
					'description' => 'An archive of portfolio projects.',
					'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
					'capability_type' => 'post',
					'rewrite' => array('slug' => 'portfolio', 'with_front' => false),
					'menu_position' => 5,
					'has_archive' => true
				); 
			
				register_post_type('portfolio', $args);
			}
			
			private function register_categories()
			{
				$labels = array
				(
					'name' => __('Portfolio Categories'),
					'singular_name' => __('Portfolio Category'),
					'search_items' => __('Search Portfolio Categories'),
					'popular_items' => __('Popular Portfolio Categories'),
					'all_items' => __('All Portfolio Categories'),
					'parent_item' => __('Parent Portfolio Category'),
					'parent_item_colon' => __('Parent Portfolio Category:'),
					'edit_item' => __('Edit Portfolio Category'),
					'update_item' => __('Update Portfolio Category'),
					'add_new_item' => __('Add New Portfolio Category'),
					'new_item_name' => __('New Portfolio Category Name'),
					'separate_items_with_commas' => __('Separate portfolio categories with commas'),
					'add_or_remove_items' => __('Add or remove portfolio categories'),
					'choose_from_most_used' => __('Choose from the most used portfolio categories'),
					'menu_name' => __('Portfolio Categories')
			    );
				
			    $args = array
			    (
					'labels' => $labels,
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_tagcloud' => true,
					'hierarchical' => true,
					'rewrite' => array('slug' => 'portfolio-category', 'with_front' => false),
					'query_var' => true
			    );
				
			    register_taxonomy('portfolio_category', array('portfolio'), $args);
			}
			
			private function register_tags()
			{
				$labels = array
				(
					'name' => __('Portfolio Tags'),
					'singular_name' => __('Portfolio Tag'),
					'search_items' => __('Search Portfolio Tags'),
					'popular_items' => __('Popular Portfolio Tags'),
					'all_items' => __('All Portfolio Tags'),
					'parent_item' => __('Parent Portfolio Tag'),
					'parent_item_colon' => __('Parent Portfolio Tag:'),
					'edit_item' => __('Edit Portfolio Tag'),
					'update_item' => __('Update Portfolio Tag'),
					'add_new_item' => __('Add New Portfolio Tag'),
					'new_item_name' => __('New Portfolio Tag Name'),
					'separate_items_with_commas' => __('Separate portfolio tags with commas'),
					'add_or_remove_items' => __('Add or remove portfolio tags'),
					'choose_from_most_used' => __('Choose from the most used portfolio tags'),
					'menu_name' => __('Portfolio Tags')
				);
				
				$args = array
				(
					'labels' => $labels,
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_tagcloud' => true,
					'hierarchical' => false,
					'rewrite' => array('slug' => 'portfolio-tag', 'with_front' => false),
					'query_var' => true
				);
			
				register_taxonomy('portfolio_tag', array('portfolio'), $args);
			}
		}
	}

?>
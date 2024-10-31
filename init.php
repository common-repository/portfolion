<?php

/*****************************************************************************************************	

Copyright (C) 2012  Robert Abramski

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

------------------------------------------------------------------------------------------------------

Plugin Name: Portfolion
Plugin URI: http://wordpress.org/extend/plugins/portfolion
Author: Robert Abramski
Version: 1.0.1
Author URI: http://robertabramski.com

Description: Enables a portfolio post type with category and tag taxomonies.

*****************************************************************************************************/

	require_once('portfolion.php');
	
	global $portfolion;
	$portfolion = new Portfolion();
	
	if(!function_exists('get_the_project_link'))
	{
		function get_the_project_link($id = false, $before = '', $after = '')
		{
			return apply_filters('the_project_link', array('before' => $before, 'after' => $after, 'id' => $id));
		}
	}
	
	if(!function_exists('has_the_project_link'))
	{
		function has_the_project_link($id = false)
		{
			global $post;
			$post_meta = get_post_meta(($id !== false ? $id : $post->ID), '_portfolio_project_link', true);
			return !empty($post_meta);
		}
	}
	
	if(!function_exists('the_project_link'))
	{
		function the_project_link($before = '', $after = '', $display = true, $id = false)
		{
			return apply_filters('the_project_link', array('before' => $before, 'after' => $after, 'display' => $display, 'id' => $id));
		}
	}

?>
<?php
namespace StacesBuilder\Inc\NPT;

use StacesBuilder\Inc\CustomFields\CustomFieldsManager;
use StacesBuilder\Inc\NPT\CPTRegistry;

if(!class_exists('\StacesBuilder\Inc\NPT\Setups')){
	class Setups{
		protected $addon_name;
		protected $register;
		protected $taxonomy;
		protected $fields;
		protected $args;

		protected $custom_meta_boxes = array();
		protected $submenu_args = array();
		
		protected function setup(): void{
			if(isset($this->taxonomy) && array_key_exists("submenus", $this->taxonomy)) $this->submenu_args = $this->taxonomy['submenus'];

			if(!post_type_exists($this->addon_name)) add_action('init', array($this, 'npt_register'));
			new CustomFieldsManager([$this->addon_name], $this->fields, $this->args);
			add_action('init', array($this, 'npt_taxonomy'));
			add_action('admin_menu', array($this, 'npt_submenu_page'));
			add_filter('views_edit-'.$this->addon_name, array($this, 'npt_description'));
			add_filter('manage_'.$this->addon_name.'_posts_columns', array($this, 'npt_filter_posts_columns'));
			add_action('manage_'.$this->addon_name.'_posts_custom_column', array($this, 'npt_columns_content'), 10, 2);
			add_action('quick_edit_custom_box', array($this, 'npt_quick_edit_custom_box'), 10, 2);
			add_action('template_redirect', array($this, 'npt_template_redirect'));
		}
		public function npt_template_redirect(): void{
			CPTRegistry::redirect_old();
		}
		public function npt_register(): void{
			register_post_type($this->addon_name, $this->register);
		}
		public function npt_taxonomy(): void{
			$tax_name = $this->addon_name.'-taxonomy';
			$tag_name = $this->addon_name.'-tag';
			register_taxonomy($tax_name, $this->addon_name, $this->taxonomy);
			if($this->taxonomy["show_tags"]) register_taxonomy($tag_name, $this->addon_name, $this->taxonomy["tags"]);
		}
		public function npt_description($views){
			if(isset($this->register['description']) && $this->register['description'] != "")
				echo "<h4>".esc_html(__($this->register['description']))."</h4>";
			return $views;
		}
		public function npt_submenu_page(): void{
			if(count($this->submenu_args) > 0){
				if(is_array($this->submenu_args[0]))
					foreach ($this->submenu_args as $key => $submenu){ $this->add_submenu_args($submenu); }
				else $this->add_submenu_args($this->submenu_args);
			}
		}
		public function npt_columns_content($column_name, $post_id): void{}
		public function npt_filter_posts_columns($columns){ return $columns; }
		public function npt_quick_edit_custom_box($column_name, $post_type): void{ wp_nonce_field('post_metadata', 'post_metadata_field'); }

		function add_submenu_args(array $args): void{
			$sbm = array_merge([
				"title" => __("Options"),
				"menu_title" => __("Options item"),
				"capability" => "manage_options",
				"callback" => ""
			], $args);
			add_submenu_page(
				"edit.php?post_type={$this->register['slug']}",
				$sbm['title'],
				$sbm['menu_title'],
				$sbm['capability'],
				str_ireplace(" ", "_", strtolower($sbm['menu_title'])),
				$sbm['callback']
			);
		}
	}
}
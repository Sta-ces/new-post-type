<?php
namespace StacesBuilder\Inc\NPT;

use StacesBuilder\Inc\NPT\Setups;
use StacesBuilder\Inc\NPT\CPTRegistry;

if(!class_exists('\StacesBuilder\Inc\NPT\NewPostType')){
	class NewPostType extends Setups
	{
		protected $name;
		protected $all_items;
		protected $version;
		
		function __construct(string $addon_name, string $name){
			$this->addon_name = $addon_name;
			$this->name = __($name);
			$this->version = '3.3.0';
		}
		public function run(array $fields = array(), array $args = array(), array $rg = array(), array $tx = array()): void{
			$is_gutenberg = isset($rg['gutenberg']) ? $rg['gutenberg'] : true;
			$icon = isset($rg["menu_icon"]) ? $rg["menu_icon"] : (isset($rg["icon"]) ? $rg["icon"] : "dashicons-admin-post");
			if(isset($tx["tags"])) $tx["tags"]["show_in_rest"] = $is_gutenberg;
			$default_slug = isset($rg['slug']) ? $rg['slug'] : $this->get_addon_name();
			$slug = CPTRegistry::get_slug($this->get_addon_name(), $default_slug);
			$tx_label = isset($tx["label"]) ? $tx["label"] : "Categories";
			
			$rg_default = array(
				'public'				=> true,
				'slug'					=> $slug,
				'label'					=> __($this->get_name()),
				'menu_position'			=> 5,
				'hierarchical'			=> true,
				'rewrite'				=> array( 'slug' => $slug ),
				'menu_icon'				=> $icon,
				'supports'				=> array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
				'photo'					=> false,
				'show_in_rest'			=> $is_gutenberg
			);
			$tx_default = array(
				"label" 		=> __($tx_label),
				"hierarchical" 	=> true,
				"submenus"		=> array(),
				"tags"			=> array_merge( [ "hierarchical" => false ], (isset($tx["tags"]) ? $tx["tags"] : []) ),
				"show_tags"		=> isset($tx["tags"]),
				"show_admin_column" => true,
				"show_ui"		=> true,
				"show_in_rest" 	=> $is_gutenberg
			);
			$rg['labels'] = $this->get_all_items(
								(isset($rg["item_label"]["singular"]) ? $rg["item_label"]["singular"] : ""),
								(isset($rg["item_label"]["plural"]) ? $rg["item_label"]["plural"] : ""),
								(isset($rg["labels"]) && is_array($rg["labels"]) ? $rg["labels"] : [])
							);
			$tx['labels'] = $this->get_all_items(
								(isset($tx["item_label"]["singular"]) ? $tx["item_label"]["singular"] : ""),
								(isset($tx["item_label"]["plural"]) ? $tx["item_label"]["plural"] : ""),
								(isset($tx["labels"]) && is_array($tx["labels"]) ? $tx["labels"] : [])
							);
			$rg = array_merge($rg_default, $rg);
			$tx = array_merge($tx_default, $tx);
			$this->register = $rg;
			$this->taxonomy = $tx;
			$this->fields = $fields;
			$this->args = $args;
			CPTRegistry::register($this->get_addon_name(), [
				'slug'  => $slug,
				'label' => $this->get_name()
			]);
			$this->setup();
		}
		public function add_taxonomy(string $name, array $args): void{
			$args["labels"] = $this->get_all_items(
					(isset($args["item_label"]["singular"]) ? $args["item_label"]["singular"] : (isset($args["label"]) ? strtolower($args["label"]) : strtolower($this->get_name()))),
					(isset($args["item_label"]["plural"]) ? $args["item_label"]["plural"] : ""),
					(isset($args["labels"]) && is_array($args["labels"]) ? $args["labels"] : [])
				);
			register_taxonomy($name, $this->get_addon_name(), array_merge($this->taxonomy, $args));
		}
		public function get_all_items(string $item_singular = "", string $item_plural = "", array $all_items = []): array{
			if(empty($item_singular)) return $all_items;
			if(empty($item_plural)) $item_plural = $item_singular."s";
			$default_items = [
				"all_items" => __("All ".strtolower($item_plural)),
				"edit_item" => __("Edit ".strtolower($item_singular)),
				"view_item" => __("View ".strtolower($item_singular)),
				"update_item" => __("Update ".strtolower($item_singular)),
				"add_new_item" => __("Add a ".strtolower($item_singular)),
				"new_item_name" => __("New ".strtolower($item_singular)),
				"parent_item" => __("Parent ".strtolower($item_singular)),
				"parent_item_colon" => __("Parent ".strtolower($item_singular).":"),
				"search_items" => __("Search ".strtolower($item_plural)),
				"popular_items" => __("Popular ".strtolower($item_plural)),
				"separate_items_with_commas" => __("Separate ".strtolower($item_plural)." with commas"),
				"add_or_remove_items" => __("Add or remove ".strtolower($item_plural)),
				"choose_from_most_used" => __("Choose from the most used ".strtolower($item_plural)),
				"not_found" => __("No ".strtolower($item_plural)." found."),
				"back_to_items" => __("← Back to ".strtolower($item_plural))
			];
			return array_merge($default_items, $all_items);
		}
		public function get_addon_name(): string{ return $this->addon_name; }
		public function get_name(): string{ return $this->name; }
		public function get_version(): string{ return $this->version; }
		public static function version(): string{ return self::$version; }
	}
}
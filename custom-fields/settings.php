<?php
/**
 * CUSTOM SETTINGS
 */

namespace StacesBuilder\Inc\CustomFields;

use StacesBuilder\Inc\CustomFields\FieldRenderer;

if(!class_exists('\StacesBuilder\Inc\CustomFields\CustomSettings')){
	class CustomSettings{
		private string $name_setting;
		private string $title;
		private string $slug;
		private string $menu_title;
		private string $description;
		private array $settings = [];
		
		public function __construct(
			string $_name_setting,
			string $_title,
			string $_slug = "general",
			string $_menu_title = "",
			string $_description = ""
		) {
			$this->name_setting = sanitize_key($_name_setting);
			$this->title = $_title;
			$this->slug = sanitize_key($_slug);
			$this->menu_title = $_menu_title ?: $_title;
			$this->description = $_description;

			add_action( 'admin_menu', array($this, 'register_submenu') );
			add_action( 'admin_init', array($this, 'register_settings_section') );
		}
		public function add_fields(array $settings): void{
			$this->settings = $settings;
			add_action( 'admin_init', array($this, 'register_fields') );
		}
		public function register_fields(): void{
			foreach($this->settings as $field){
				$name  = $field['name'] ?? '';
				$title = $field['title'] ?? '';

				if (empty($name)) continue;

				add_settings_field(
					$name,
					$title,
					array($this, 'render_field'),
					$this->slug,
					$this->name_setting.'-section-id',
					$field
				);

				register_setting( $this->slug, $name );
			}
		}
		public function register_submenu(): void{
			if(!$this->menu_exists($this->slug, true))
				add_submenu_page( 
					'options-general.php',
					__($this->menu_title),
					__($this->menu_title), 
					'administrator', 
					$this->slug, 
					function(){ $this->render_page(); } 
				);
		}
		public function register_settings_section(): void{
			add_settings_section(
				$this->name_setting.'-section-id',
				$this->title,
				array($this, 'render_section_description'),
				$this->slug
			);
		}
		public function render_section_description(): void{
			if(!empty($this->description)) echo wpautop( $this->description );
		}
		public function render_field(array $field): void{
			global $post;
			FieldRenderer::render(null, [$field]);
		}
		private function render_page(): void{
			?>
			<div class="wrap">
				<?php if(!empty($this->title)): ?>
					<h2><?php echo esc_html(__($this->title)); ?></h2>
				<?php endif; ?>
				<?php if(!empty($this->description)): ?>
					<p><?php echo esc_html(__($this->description)); ?></p>
				<?php endif; ?>
				<form method='POST' action='options.php'>
					<?php 
						settings_fields( $this->slug );
						do_settings_sections( $this->slug );
						submit_button();
					?>
				</form>
			</div>
			<?php
		}
		
		private function menu_exists( string $handle, bool $sub = false ): bool{
			if( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX) ) return false;
			
			global $menu, $submenu;
			$check_menu = $sub ? $submenu : $menu;
			if(empty($check_menu)) return false;
			
			$handle = "options-".$handle.".php";

			foreach( $check_menu as $items ){
				if( $sub ){
					foreach( $items as $sm ){ if( $handle === $sm[2] ) return true; }
				} elseif( $handle == $items[2] ) return true;
			}
			return false;
		}
	}
}
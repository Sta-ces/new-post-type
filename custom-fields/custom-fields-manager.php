<?php
namespace StacesBuilder\Inc\CustomFields;

require_once( __DIR__.'/media-upload.php' );
require_once( __DIR__.'/field-renderer.php' );
require_once( __DIR__.'/settings.php' );

use WP_Post;
use StacesBuilder\Inc\CustomFields\FieldRenderer;

if(!class_exists('\StacesBuilder\Inc\CustomFields\CustomFieldsManager')){
    class CustomFieldsManager {

        private array $post_types;
        private array $fields;
        private string $meta_box_id;
        private string $meta_box_title;
        private string $description;
        private string $context;
        private string $priority;
        private array $args = [];

        public function __construct(
            array $post_types = array(),
            array $fields = array(),
            array $args = array()
        ) {
            if(empty($post_types) || empty($fields)) return;
            $this->args = array_merge(
                [
                    "id" => "",
                    "title" => "Custom Fields",
                    "description" => "",
                    "context" => "normal",
                    "priority" => "high"
                ],
                $args
            );
            $this->meta_box_id = sanitize_key(!empty($this->args["id"]) ? $this->args["id"]: $this->args["title"]);
            $this->meta_box_title = __($this->args["title"]);
            $this->description = __($this->args["description"]);
            $this->context = $this->args["context"];
            $this->priority = $this->args["priority"];
            $this->post_types = $post_types;
            $this->fields = $fields;

            add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
            add_action('save_post', array($this, 'save_custom_meta_box'));
            add_action('edit_post', array($this, 'save_custom_meta_box'));
        }

        public function add_custom_meta_box(): void {
            foreach ($this->post_types as $post_type) {
                add_meta_box(
                    $this->meta_box_id, // ID
                    $this->meta_box_title,   // Title
                    array($this, 'custom_meta_box_callback'), // Callback function
                    $post_type,       // Post type
                    $this->context,         // Context
                    $this->priority            // Priority
                );
            }
        }

        public function custom_meta_box_callback(WP_Post $post): void {
            wp_nonce_field('save_custom_meta_box', 'custom_meta_box_nonce');
            FieldRenderer::render($post, $this->fields, $this->description);
        }

        public function save_custom_meta_box(int $post_id): void {
            if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'save_custom_meta_box')) return;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

            foreach ($this->fields as $field) {
                $field_key = sanitize_key($field['name']);
                if (isset($_POST[$field_key])) {
                    $text_field = 
                        ($field["type"] === "WYSWYG")
                        ? wp_kses_post($_POST[$field_key])
                        : sanitize_text_field($_POST[$field_key]);
                    if($text_field === "") delete_post_meta( $post_id, $field_key );
                    else update_post_meta($post_id, $field_key, $text_field);
                }
                else{
                    delete_post_meta( $post_id, $field_key );
                }
            }
        }
    }
}
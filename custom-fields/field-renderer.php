<?php
namespace StacesBuilder\Inc\CustomFields;

use WP_Post;
use StacesBuilder\Inc\CustomFields\MediaUpload;

if(!class_exists('\StacesBuilder\Inc\CustomFields\FieldRenderer')){
    class FieldRenderer{
        public static function render(WP_POST|null $post, array $fields, string $description = ""): void{
            if(!empty($description)) echo "<p style='margin:0 0 2em;'>".esc_html(_st($description))."</p>";

            foreach ($fields as $field) {
                if(isset($field['name'])){
                    $field_key = sanitize_key($field['name']);
                    $field_label = $field['label']??null;
                    $field_placeholder = isset($field['placeholder']) ? str_ireplace("'", "&apos;", $field['placeholder']) : "";
                    $field_type = isset($field['type']) ? $field['type'] : 'text'; // By defaut, text type
                    $value = ($post) ? get_post_meta($post->ID, $field_key, true) : get_option($field_key);
                    $isrequired = isset($field['isrequired']) ? boolval($field['isrequired']) : false;
                    $options = isset($field['options'])?$field['options']:[];
                    $default = isset($field['default'])?(empty($value)?$field['default']:$value):"";
                    if ( ! array_key_exists( '0', $options ) ) {
                        $options = [0 => "---"] + $options;
                    }

                    echo "<div style='display:flex;flex-direction:column;flex-wrap:wrap;margin-bottom:1em;'>";
                    if($field_label) echo '<label for="' . esc_attr($field_key) . '">' . esc_html(_st($field_label)) . ($isrequired ? "<span style='color:var(--wp--preset--color--vivid-red);padding-left:5px;'>*</span>" : "") . '</label>';
                    
                    switch ($field_type) {
                        case 'textarea':
                            echo '<textarea id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" placeholder="'.esc_attr($field_placeholder).'" ' . esc_attr($isrequired ? "required" : "") . '>' . esc_textarea($value) . '</textarea>';
                            break;
                        case 'checkbox':
                            echo '<input type="hidden" name="' . esc_attr( $field_key ) . '" value="0">';
                            echo '<input type="checkbox" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="1" ' . checked(1, $value, false) . ' ' . esc_attr($isrequired ? "required" : "") . ' />';
                            break;
                        case 'radio':
                            if (isset($options)) {
                                foreach ($options as $option_value => $option_label) {
                                    echo '<label><input type="radio" name="' . esc_attr($field_key) . '" value="' . esc_attr($option_value) . '" ' . checked($option_value, $value, false) . ' ' . esc_attr($isrequired ? "required" : "") . ' /> ' . esc_html($option_label) . '</label><br>';
                                }
                            }
                            break;
                        case "taxonomies":
                            $opt = [];
                            $tax_args = array_merge([
                                'order_by' => 'name',
                                'hide_empty' => false
                            ], (isset($field['taxonomy_args'])?$field['taxonomy_args']:[]));
                            $tax_id = $post ? get_object_taxonomies(get_post_type($post))[0] : [];
                            $tax = get_terms($tax_id, $tax_args);
                            foreach ($tax as $key => $value) { $opt[$value->slug] = $value->name; }
                            $options = $opt;
                        case 'select':
                            echo '<select id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" ' . esc_attr($isrequired ? "required" : "") . '>';
                            if (isset($options)) {
                                foreach ($options as $option_value => $option_label) {
                                    echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                                }
                            }
                            echo '</select>';
                            break;
                        case "WYSWYG":
                            $clean_value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );
                            wp_editor( $clean_value, esc_attr($field_key), array(
                                'wpautop'       => false,
                                'media_buttons' => false,
                                'textarea_name' => esc_attr($field_key),
                                'editor_class'  => esc_attr($field_key)."_class",
                                'textarea_rows' => 10
                            ) );
                            break;
                        case "media":
                            new MediaUpload([
                                'id' => esc_attr($field_key),
                                'name' => esc_attr($value)
                            ]);
                            break;
                        case "dates":
                            echo '<div class="datepicker input-group date form-group" id="datepicker"><input class="form-control" type="text" value="' . esc_attr($value) . '" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" placeholder="'.esc_attr($field_placeholder).'" ' . esc_attr($isrequired ? "required" : "") . ' /><span class="input-group-addon"></span></div>';
                            break;
                        default:
                            echo '<input type="' . esc_attr($field_type) . '" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '" placeholder="'.esc_attr($field_placeholder).'" ' . esc_attr($isrequired ? "required" : "") . ' />';
                    }

                    echo "</div>";
                }
            }
        }
    }
}
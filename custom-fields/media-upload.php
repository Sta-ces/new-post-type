<?php
namespace StacesBuilder\Inc\CustomFields;

if(!class_exists('\StacesBuilder\Inc\CustomFields\MediaUpload')){
    class MediaUpload{
        private int $image_id = 0;
        private string $image_name = '';
        private bool $is_multiple = false;

        function __construct(array $args){
            $this->image_id = $args['id'];
            $this->image_name = $args['name'];
            if(isset($args['multiple'])) $is_multiple = $args['multiple'];
            $this->render();
        }

        public function getID(): int{ return $this->image_id; }
        public function setID(int $id): void{ $this->image_id = $id; }
        
        public function render(): void{
            $image = wp_get_attachment_url( $this->image_id );
            if( preg_match("/(\.pdf)$/", $image) ) $image = includes_url('/images/media/document.png');
            $content = ($image) ? "<img src='".$image."' width='100' height='auto'>" : __('Upload image');
            $style_remove = ($image) ? 'inherit' : 'none';
            $hidden = ($image) ? $this->image_id : '';
            ?>
                <a href="#" class="stacesbuilder-upl"><?php echo esc_html($content); ?></a>
                <a href="#" class="stacesbuilder-rmv" style="display:<?php echo esc_attr($style_remove); ?>"><?php _e("Remove image"); ?></a>
                <input type="hidden" name="<?php echo esc_attr($this->image_name); ?>" id="<?php echo esc_attr($this->image_name); ?>" value="<?php echo esc_attr($hidden); ?>">
            <?php
        }
    }
}
<?php
namespace StacesBuilder\Inc\NPT;

use StacesBuilder\Inc\CustomSettings;

if(!class_exists('\StacesBuilder\Inc\NPT\CPTRegistry')){
    class CPTRegistry {
        private static $cpts = [];

        public static function register(string $name, array $args = []): void{
            self::$cpts[$name] = $args;
        }
        public static function all(): array{
            return self::$cpts;
        }
        public static function get_slug(string $name, mixed $default): string{
            $option = "stacesbuilder_slug_" . $name;
            return sanitize_title(get_option($option, $default));
        }
        public static function redirect_old(): void{
            global $wp;
            $request = trim($wp->request, '/');

            foreach (self::$cpts as $name => $cpt) {
                $option = "stacesbuilder_slug_" . $name;
                $old_slug = get_option($option . '_old', $cpt['slug']);
                $new_slug = self::get_slug($name, $cpt['slug']);

                if ($old_slug !== $new_slug && ($request === $old_slug || strpos($request, $old_slug . '/') === 0)) {
                    $redirect = home_url(str_replace($old_slug, $new_slug, $request));
                    wp_redirect($redirect, 301);
                    exit;
                }
            }
        }
    }
}

add_action('update_option', function(string $option, mixed $old, mixed $new): void{
    if (strpos($option, 'stacesbuilder_slug_') === 0 && $old !== $new) {
        flush_rewrite_rules();
    }
}, 10, 3);

add_action('admin_init', function(): void{
    add_settings_section(
        'stacesbuilder_cpts_section',
        __('Custom Post Types (Staces Builder)'),
        function(): void {
            echo '<p>' . (count(CPTRegistry::all()) ? __('Edit the slugs of your custom content types.') : __('No Custom Post Types at this moment.')) . '</p>';
        },
        'permalink'
    );

    foreach (CPTRegistry::all() as $name => $args) {
        $option = "stacesbuilder_slug_" . $name;
        $current = CPTRegistry::get_slug($name, $args['slug']);

        add_settings_field(
            $option,
            sprintf(__('Slug for %s'), $args['label']),
            function() use ($option, $current): void{
                echo '<input type="text" name="' . esc_attr($option) . '" value="' . esc_attr($current) . '" class="regular-text">';
            },
            'permalink',
            'stacesbuilder_cpts_section'
        );
        register_setting('permalink', $option);
    }
});

add_action('admin_init', function(): void{
    if (isset($_POST['permalink_structure'])) {
        foreach (CPTRegistry::all() as $name => $args) {
            $option = "stacesbuilder_slug_" . $name;
            if (isset($_POST[$option])) {
                $new_slug = sanitize_title($_POST[$option]);

                $old_slug = get_option($option);
                if ($old_slug && $old_slug !== $new_slug) {
                    update_option($option . '_old', $old_slug);
                }

                update_option($option, $new_slug);
            }
        }
    }
});

// -- Automatic redirection of old URLs ---
add_action('template_redirect', [CPTRegistry::class, 'redirect_old']);
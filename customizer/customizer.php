<?php
/**
 * Customizer Class
 * Developer: Cedric Staces
 * URI: https://staces.be/
 */

namespace StacesBuilder\Inc\Customizer;

use StacesBuilder\Inc\Customizer\STH_Slider_Control;

if(!class_exists('\StacesBuilder\Inc\Customizer\STHCustomizer')){
	class STHCustomizer{
		function __construct(\WP_Customize_Manager $wp, string $name, array $args){
			if ( ! $wp instanceof \WP_Customize_Manager ) return false;
			if(!isset($args['label']) || !isset($args['section'])) return false;
			$args = array_merge([
				'default' => '',
				'transport' => 'refresh',
				'type' => 'text',
				'priority' => 10,
				'settings' => $name
			], $args);
			if(isset($args['refresh'])) $args['transport'] = !!$args['refresh'] ? 'refresh' : 'postMessage';
			if( ! $wp->get_section( $args['section'] ) ){
				$section_args = array_merge([
					'title'	=> _st(ucfirst(trim($args['section']))),
					'priority'	=> 150
				], $args['section_args'] ?? []);
				$wp->add_section($args['section'], $section_args);
			}
			$wp->add_setting(
				$name,
				array(
					'default'		=> $args['default'],
					'transport'		=> $args['transport']
				)
			);
			switch ($args['type']) {
				case 'color': case 'colors':
					$wp->add_control(new \WP_Customize_Color_Control( $wp, $name, $args ));
					break;
				case 'image': case 'images': case 'media':
					$args['mime_type'] = 'image';
					$wp->add_control(new \WP_Customize_Media_Control( $wp, $name, $args ));
					break;
				case 'range':
					$wp->add_control(new STH_Slider_Control($wp, $name, $args));
					break;
				default: $wp->add_control($name, $args); break;
			}
			return true;
		}
	}
}

function stth_customize_register( $wp_customize ): void{
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	require_once( __DIR__.'/config/class-sth-slider-control.php' );
	// REMOVE CONTROLS
	$wp_customize->remove_section('static_front_page');
	$wp_customize->remove_section('colors');
	$wp_customize->remove_control('header_text');
	
	// CUSTOMIZERS
	// TITLE_TAGLINE
	new STHCustomizer($wp_customize, 'website_description', [ 'label' => _st('Site description'), 'description' => _st('Try to be concise in your description. (Recommended maximum 160 characters)'), 'type' => 'textarea', 'section' => 'title_tagline', 'input_attrs' => [ 'maxlength' => 160 ] ]);
	new STHCustomizer($wp_customize, 'website_keywords', [ 'label' => _st('Keywords'), 'description' => _st('Give some keywords to upgrade you SEO'), 'type' => 'textarea', 'section' => 'title_tagline', "default" => get_option('meta-keywords') ]);
	// COMPANY
	new STHCustomizer($wp_customize, 'address_google_maps', [ 'label' => _st('Headquarter'), 'section' => 'company' ]);
	new STHCustomizer($wp_customize, 'tva_company', [ 'label' => _st('VAT'), 'section' => 'company' ]);
	// COOKIES
	new STHCustomizer($wp_customize, 'cookieconsent_display', [ 'label' => _st('Active cookies'), 'section' => 'cookies', 'type' => 'checkbox', 'default' => false ]);
	new STHCustomizer($wp_customize, 'cookieconsent_title', [ 'label' => _st('Title'), 'section' => 'cookies', 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_description', [ 'label' => _st('Description'), 'section' => 'cookies', "type" => "textarea", 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_cookieusage', [ 'label' => _st('Cookie usage'), 'section' => 'cookies', 'type' => 'textarea', 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_necessarycookies', [ 'label' => _st('Strictly Necessary Cookies'), 'section' => 'cookies', 'type' => 'textarea', 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_analyticscookies', [ 'label' => _st('Analytics Cookies'), 'section' => 'cookies', 'type' => 'textarea', 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_acceptallbtn', [ 'label' => _st('Accept all button'), 'section' => 'cookies', 'description' => _st('Default: `Accept all`'), 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_acceptnecessarybtn', [ 'label' => _st('Accept necessary button'), 'section' => 'cookies', 'description' => _st('Default: `Reject all`'), 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_showpreferencesbtn', [ 'label' => _st('Show preferences button'), 'section' => 'cookies', 'description' => _st('Default: `Manage preferences`'), 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_savepreferencesbtn', [ 'label' => _st('Save preferences button'), 'section' => 'cookies', 'description' => _st('Default: `Save preferences`'), 'transport' => 'postMessage' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_privacypolicy', [ 'label' => _st('Privacy policy page'), 'section' => 'cookies', 'type' => 'dropdown-pages' ]);
	new STHCustomizer($wp_customize, 'cookieconsent_termsandconditions', [ 'label' => _st('Terms and conditions page'), 'section' => 'cookies', 'type' => 'dropdown-pages' ]);
	// SETTINGS
	if(is_plugin_active('fluentform/fluentform.php')) new STHCustomizer($wp_customize, 'connexion_form', [ 'label' => _st('Select the login form'), 'section' => 'settings', 'description' => _st('Form to be used as login form'), 'type' => 'select', 'choices' => get_all_forms() ]);
    new STHCustomizer($wp_customize, 'is_close_website', [ 'label' => _st('Close the site temporarily'), 'section' => 'settings', 'type' => 'checkbox', 'priority' => 300 ]);
    new STHCustomizer($wp_customize, 'close_page', [ 'label' => _st('Maintenance page'), 'section' => 'settings', 'description' => _st('Select the maintenance page that will be displayed when maintaining your site.'), 'type' => 'dropdown-pages' ]);
    new STHCustomizer($wp_customize, 'arrow_to_top', [ 'label' => _st('Arrow to top'), 'section' => 'settings', 'description' => _st('Display an arrow on the bottom left screen side to scroll quickly to the top of the screen.'), 'type' => 'checkbox' ]);
	// SPLASHSCREEN
	new STHCustomizer($wp_customize, 'active_splashscreen', [ 'label' => _st('Active'), 'section' => 'splashscreen', 'type' => 'checkbox', 'default' => 'checked' ]);
	new STHCustomizer($wp_customize, 'load_splashscreen', [ 'label' => _st('When is showed?'), 'description' => _st('When the splashscreen will be showed'), 'section' => 'splashscreen', 'type' => 'select', 'choices' => ['before-unload' => 'Before loading page', 'after-unload' => 'After loading page', 'both-unload' => 'Before and after loading page'], 'default' => 'both' ]);
	new STHCustomizer($wp_customize, 'animation_splashcreen_loaded', [ 'label' => _st('Animation loaded'), 'description' => _st("The animation when the page loaded"), 'section' => 'splashscreen', 'type' => 'select', 'choices' => array_column(getAnimations(), 'label', 'value'), 'default' => 'animate__slideOutUp' ]);
	new STHCustomizer($wp_customize, 'animation_duration_loaded', [ 'label' => _st('Animation duration'), 'section' => 'splashscreen', 'type' => 'range', 'input_attrs' => [ 'max' => 2, 'min' => 0, 'step' => 0.25 ], 'default' => 1 ]);
	new STHCustomizer($wp_customize, 'animation_delay_loaded', [ 'label' => _st('Animation delay'), 'section' => 'splashscreen', 'type' => 'range', 'input_attrs' => [ 'max' => 5, 'min' => 0, 'step' => 0.25 ], 'default' => 0 ]);
	new STHCustomizer($wp_customize, 'animation_splashcreen_loading', [ 'label' => _st('Animation loading'), 'description' => _st("The animation when the page is loading"), 'section' => 'splashscreen', 'type' => 'select', 'choices' => array_column(getAnimations(), 'label', 'value'), 'default' => 'animate__slideInUp' ]);
	new STHCustomizer($wp_customize, 'animation_duration_loading', [ 'label' => _st('Animation duration'), 'section' => 'splashscreen', 'type' => 'range', 'input_attrs' => [ 'max' => 2, 'min' => 0, 'step' => 0.25 ], 'default' => 1 ]);
	new STHCustomizer($wp_customize, 'animation_delay_loading', [ 'label' => _st('Animation delay'), 'section' => 'splashscreen', 'type' => 'range', 'input_attrs' => [ 'max' => 5, 'min' => 0, 'step' => 0.25 ], 'default' => 0 ]);
}
add_action( 'customize_register', 'StacesBuilder\Inc\Customizer\stth_customize_register' );
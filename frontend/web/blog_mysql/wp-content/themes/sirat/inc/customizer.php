<?php
/**
 * Sirat Theme Customizer
 *
 * @package Sirat
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

function sirat_custom_controls() {
	load_template( trailingslashit( get_template_directory() ) . '/inc/custom-controls.php' );
}
add_action( 'customize_register', 'sirat_custom_controls' );

function sirat_customize_register( $wp_customize ) {

	load_template( trailingslashit( get_template_directory() ) . 'inc/customize-homepage/class-customize-homepage.php' );

	//add home page setting pannel
	$wp_customize->add_panel( 'sirat_panel_id', array(
	    'priority' => 10,
	    'capability' => 'edit_theme_options',
	    'theme_supports' => '',
	    'title' => __( 'VW Settings', 'sirat' ),
	) );

	// Layout
	$wp_customize->add_section( 'sirat_left_right', array(
    	'title'      => __( 'General Settings', 'sirat' ),
		'panel' => 'sirat_panel_id'
	) );

	$wp_customize->add_setting('sirat_theme_options',array(
        'default' => __('Right Sidebar','sirat'),
        'sanitize_callback' => 'sirat_sanitize_choices'
	));
	$wp_customize->add_control('sirat_theme_options',array(
        'type' => 'select',
        'label' => __('Post Sidebar Layout','sirat'),
        'description' => __('Here you can change the sidebar layout for posts. ','sirat'),
        'section' => 'sirat_left_right',
        'choices' => array(
            'Left Sidebar' => __('Left Sidebar','sirat'),
            'Right Sidebar' => __('Right Sidebar','sirat'),
            'One Column' => __('One Column','sirat'),
            'Three Columns' => __('Three Columns','sirat'),
            'Four Columns' => __('Four Columns','sirat'),
            'Grid Layout' => __('Grid Layout','sirat')
        ),
	) );

	$wp_customize->add_setting('sirat_page_layout',array(
        'default' => __('One Column','sirat'),
        'sanitize_callback' => 'sirat_sanitize_choices'
	));
	$wp_customize->add_control('sirat_page_layout',array(
        'type' => 'select',
        'label' => __('Page Sidebar Layout','sirat'),
        'description' => __('Here you can change the sidebar layout for pages. ','sirat'),
        'section' => 'sirat_left_right',
        'choices' => array(
            'Left Sidebar' => __('Left Sidebar','sirat'),
            'Right Sidebar' => __('Right Sidebar','sirat'),
            'One Column' => __('One Column','sirat')
        ),
	) );

	//Contact
	$wp_customize->add_section( 'sirat_topbar', array(
    	'title'      => __( 'Top Bar Settings', 'sirat' ),
		'panel' => 'sirat_panel_id'
	) );

	$wp_customize->add_setting('sirat_contact_call',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('sirat_contact_call',array(
		'label'	=> __('Add Phone Number','sirat'),
		'input_attrs' => array(
            'placeholder' => __( '+00 987 654 1230', 'sirat' ),
        ),
		'section'=> 'sirat_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting('sirat_contact_email',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('sirat_contact_email',array(
		'label'	=> __('Add Email Address','sirat'),
		'input_attrs' => array(
            'placeholder' => __( 'example@gmail.com', 'sirat' ),
        ),
		'section'=> 'sirat_topbar',
		'type'=> 'text'
	));

	$wp_customize->add_setting( 'sirat_header_search',array(
    	'default' => 1,
      	'transport' => 'refresh',
      	'sanitize_callback' => 'sirat_switch_sanitization'
    ));  
    $wp_customize->add_control( new Sirat_Toggle_Switch_Custom_control( $wp_customize, 'sirat_header_search',array(
      	'label' => esc_html__( 'Show / Hide Search','sirat' ),
      	'section' => 'sirat_topbar'
    )));
    
	//Slider
	$wp_customize->add_section( 'sirat_slidersettings' , array(
    	'title'      => __( 'Slider Settings', 'sirat' ),
		'panel' => 'sirat_panel_id'
	) );

	$wp_customize->add_setting( 'sirat_slider_arrows',array(
    	'default' => 1,
      	'transport' => 'refresh',
      	'sanitize_callback' => 'sirat_switch_sanitization'
    ));  
    $wp_customize->add_control( new Sirat_Toggle_Switch_Custom_control( $wp_customize, 'sirat_slider_arrows',array(
      	'label' => esc_html__( 'Show / Hide Slider','sirat' ),
      	'section' => 'sirat_slidersettings'
    )));

	for ( $count = 1; $count <= 4; $count++ ) {

		$wp_customize->add_setting( 'sirat_slider_page' . $count, array(
			'default'           => '',
			'sanitize_callback' => 'sirat_sanitize_dropdown_pages'
		) );
		$wp_customize->add_control( 'sirat_slider_page' . $count, array(
			'label'    => __( 'Select Slider Page', 'sirat' ),
			'description' => __('Slider image size (1500 x 800)','sirat'),
			'section'  => 'sirat_slidersettings',
			'type'     => 'dropdown-pages'
		) );
	}
    
	//Our Services section
	$wp_customize->add_section( 'sirat_services_section' , array(
    	'title'      => __( 'Our Services Settings', 'sirat' ),
		'priority'   => null,
		'panel' => 'sirat_panel_id'
	) );

	$wp_customize->add_setting('sirat_section_title',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('sirat_section_title',array(
		'label'	=> __('Add Section Title','sirat'),
		'input_attrs' => array(
            'placeholder' => __( 'AWESOME SERVICES', 'sirat' ),
        ),
		'section'=> 'sirat_services_section',
		'type'=> 'text'
	));

	$wp_customize->add_setting('sirat_section_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('sirat_section_text',array(
		'label'	=> __('Add Section Text','sirat'),
		'input_attrs' => array(
            'placeholder' => __( 'Lorem ipsum is dummy text', 'sirat' ),
        ),
		'section'=> 'sirat_services_section',
		'type'=> 'text'
	));

	$categories = get_categories();
	$cat_post = array();
	$cat_post[]= 'select';
	$i = 0;	
	foreach($categories as $category){
		if($i==0){
			$default = $category->slug;
			$i++;
		}
		$cat_post[$category->slug] = $category->name;
	}

	$wp_customize->add_setting('sirat_our_services',array(
		'default'	=> 'select',
		'sanitize_callback' => 'sirat_sanitize_choices',
	));
	$wp_customize->add_control('sirat_our_services',array(
		'type'    => 'select',
		'choices' => $cat_post,
		'label' => __('Select Category to display Services','sirat'),
		'description' => __('Image Size (50 x 50)','sirat'),
		'section' => 'sirat_services_section',
	));

	$wp_customize->add_setting( 'sirat_about_page' , array(
		'default'           => '',
		'sanitize_callback' => 'sirat_sanitize_dropdown_pages'
	) );
	$wp_customize->add_control( 'sirat_about_page' , array(
		'label'    => __( 'Select About Page', 'sirat' ),
		'description' => __('Image Size (280 x 280)','sirat'),
		'section'  => 'sirat_services_section',
		'type'     => 'dropdown-pages'
	) );

	//Content Craetion
	$wp_customize->add_section( 'sirat_content_section' , array(
    	'title' => __( 'Customize Home Page Settings', 'sirat' ),
		'priority' => null,
		'panel' => 'sirat_panel_id'
	) );

	$wp_customize->add_setting('sirat_content_creation_main_control', array(
		'sanitize_callback' => 'esc_html',
	) );

	$homepage= get_option( 'page_on_front' );

	$wp_customize->add_control(	new Sirat_Content_Creation( $wp_customize, 'sirat_content_creation_main_control', array(
		'options' => array(
			esc_html__( 'First select static page in homepage setting for front page.Below given edit button is to customize Home Page. Just click on the edit option, add whatever elements you want to include in the homepage, save the changes and you are good to go.','sirat' ),
		),
		'section' => 'sirat_content_section',
		'button_url'  => admin_url( 'post.php?post='.$homepage.'&action=edit'),
		'button_text' => esc_html__( 'Edit', 'sirat' ),
	) ) );

	//Footer Text
	$wp_customize->add_section('sirat_footer',array(
		'title'	=> __('Footer Settings','sirat'),
		'panel' => 'sirat_panel_id',
	));	
	
	$wp_customize->add_setting('sirat_footer_text',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));	
	$wp_customize->add_control('sirat_footer_text',array(
		'label'	=> __('Copyright Text','sirat'),
		'input_attrs' => array(
            'placeholder' => __( 'Copyright 2019, .....', 'sirat' ),
        ),
		'section'=> 'sirat_footer',
		'type'=> 'text'
	));	
}

add_action( 'customize_register', 'sirat_customize_register' );

load_template( trailingslashit( get_template_directory() ) . '/inc/logo/logo-resizer.php' );

/**
 * Singleton class for handling the theme's customizer integration.
 *
 * @since  1.0.0
 * @access public
 */
final class Sirat_Customize {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register panels, sections, settings, controls, and partials.
		add_action( 'customize_register', array( $this, 'sections' ) );

		// Register scripts and styles for the controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
	}

	/**
	 * Sets up the customizer sections.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @return void
	*/
	public function sections( $manager ) {

		// Load custom sections.
		load_template( trailingslashit( get_template_directory() ) . '/inc/section-pro.php' );

		// Register custom section types.
		$manager->register_section_type( 'Sirat_Customize_Section_Pro' );

		// Register sections.
		$manager->add_section( new Sirat_Customize_Section_Pro( $manager,'example_1', array(
			'priority'   => 9,
			'title'    => esc_html__( 'SIRAT PRO', 'sirat' ),
			'pro_text' => esc_html__( 'UPGRADE PRO', 'sirat' ),
			'pro_url'  => esc_url('https://www.vwthemes.com/themes/multipurpose-wordpress-theme/'),
		) )	);
	}

	/**
	 * Loads theme customizer CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_control_scripts() {

		wp_enqueue_script( 'sirat-customize-controls', trailingslashit( get_template_directory_uri() ) . '/assets/js/customize-controls.js', array( 'customize-controls' ) );

		wp_enqueue_style( 'sirat-customize-controls', trailingslashit( get_template_directory_uri() ) . '/assets/css/customize-controls.css' );
	}
}

// Doing this customizer thang!
Sirat_Customize::get_instance();
<?php

	$sirat_first_color = get_theme_mod('sirat_first_color');

	/*------------------------------ Global First Color -----------*/
	if($sirat_first_color != false){
		$custom_css .='.top-bar, input[type="submit"],.top-btn a,.more-btn a,#sidebar h3,#footer-2,.pagination .current,.pagination a:hover, #comments input[type="submit"],#sidebar .custom-social-icons i, #footer .custom-social-icons i,#sidebar .tagcloud a:hover,.serv-box:hover,.box .inner-content:after, #slider .carousel-control-prev-icon:hover, #slider .carousel-control-next-icon:hover,#header .nav ul.sub-menu li a:hover,#footer .tagcloud a:hover,nav.woocommerce-MyAccount-navigation ul li,.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt {';
			$custom_css .='background-color: '.esc_html($sirat_first_color).';';
		$custom_css .='}';
	}
	if($sirat_first_color != false){
		$custom_css .='.scrollup, #slider .carousel-control-prev-icon:hover, #slider .carousel-control-next-icon:hover{';
			$custom_css .='border-color: '.esc_html($sirat_first_color).';';
		$custom_css .='}';
	}
	if($sirat_first_color != false){
		$custom_css .='a, .post-navigation a:hover .post-title, .post-navigation a:focus .post-title,#header .nav ul li a:hover,.post-main-box:hover h3,.scrollup,#footer h3,.serv-box a,#footer li a:hover,a.scrollup{';
			$custom_css .='color: '.esc_html($sirat_first_color).';';
		$custom_css .='}';
	}
	if($sirat_first_color != false){
		$custom_css .='#footer h3:after,#slider,.middle-header{';
			$custom_css .='border-bottom-color: '.esc_html($sirat_first_color).';';
		$custom_css .='}';
	}
	if($sirat_first_color != false){
		$custom_css .='#slider .inner_carousel,.heading-box h3{';
			$custom_css .='border-left-color: '.esc_html($sirat_first_color).';';
		$custom_css .='}';
	}
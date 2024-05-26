<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwSubscribePopupProFonts {

	public $fonts = array(
		'Archivo',
		'Biryani',
		'Blinker',
		'Cabin',
		'Cagliostro',
		'Chilanka',
		'Cute Font',
		'Darker Grotesque',
		'Harmattan',
		'Heebo',
		'Jaldi',
		'Jua',
		'Julius Sans One',
		'Lato',
		'Lekton',
		'Lexend Deca',
		'Major Mono Display',
		'Mallanna',
		'Merriweather',
		'Montserrat',
		'Mukta',
		'NTR',
		'Niramit',
		'Nunito',
		'Open Sans',
		'Oxygen',
		'Poppins',
		'Questrial',
		'Raleway',
		'Roboto',
		'Roboto Mono',
		'Rubik',
		'Ubuntu',
		'Varela',
		'Varela Round',
		'Work Sans'
	);

	public static function aw_get_font_weight( $font) {
		switch ($font) {
			case 'Archivo':
				$font_weight = array(
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic'
				);
				break;

			case 'Biryani':
				$font_weight = array(
				'200'	=> 'Extra-Light',
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'600'	=> 'Semi-Bold',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold',
				'900'	=> 'Black'
				);
				break;

			case 'Blinker':
				$font_weight = array(
				'100'	=> 'Thin',
				'200'	=> 'Extra-Light',
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'600'	=> 'Semi-Bold',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold',
				'900'	=> 'Black'
				);
				break;

			case 'Cabin':
				$font_weight = array(
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic'
				);
				break;

			case 'Cagliostro':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Chilanka':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Cute Font':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Darker Grotesque':
				$font_weight = array(
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'500'	=> 'Medium',
				'600'	=> 'Semi-Bold',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold',
				'900'	=> 'Black'
				);
				break;

			case 'Harmattan':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Heebo':
				$font_weight = array(
				'100'	=> 'Thin',
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'500'	=> 'Medium',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold',
				'900'	=> 'Black'
				);
				break;

			case 'Jaldi':
				$font_weight = array(
				'700'	=> 'Bold',
				'400'	=> 'Regular'
				);
				break;

			case 'Jua':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Julius Sans One':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Lato':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;

			case 'Lekton':
				$font_weight = array(
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'700'	=> 'Bold'
				);
				break;
			
			case 'Lexend Deca':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;
			
			case 'Major Mono Display':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;
			
			case 'Mallanna':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Merriweather':
				$font_weight = array(
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;
			
			case 'Montserrat':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'800'	=> 'Extra-Bold',
				'800i'	=> 'Extra-Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;

			case 'Mukta':
				$font_weight = array(
				'200'	=> 'Extra-Light',
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'500'	=> 'Medium',
				'600'	=> 'Semi-Bold',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold'
				);
				break;

			case 'NTR':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;	

			case 'Niramit':
				$font_weight = array(
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic'
				);
				break;

			case 'Nunito':
				$font_weight = array(
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'800'	=> 'Extra-Bold',
				'800i'	=> 'Extra-Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;
			
			case 'Open Sans':
				$font_weight = array(
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'800'	=> 'Extra-Bold',
				'800i'	=> 'Extra-Bold Italic'
				);
				break;
			
			case 'Oxygen':
				$font_weight = array(
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'700'	=> 'Bold'
				);
				break;

			case 'Poppins':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'800'	=> 'Extra-Bold',
				'800i'	=> 'Extra-Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;

			case 'Questrial':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;

			case 'Raleway':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'600'	=> 'Semi-Bold',
				'600i'	=> 'Semi-Bold Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'800'	=> 'Extra-Bold',
				'800i'	=> 'Extra-Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;

			case 'Roboto':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'200'	=> 'Extra-Light',
				'200i'	=> 'Extra-Light Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;
			
			case 'Roboto Mono':
				$font_weight = array(
				'100'	=> 'Thin',
				'100i'	=> 'Thin Italic',
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic'
				);
				break;
			
			case 'Rubik':
				$font_weight = array(
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic',
				'900'	=> 'Black',
				'900i'	=> 'Black Italic'
				);
				break;

			case 'Ubuntu':
				$font_weight = array(
				'300'	=> 'Light',
				'300i'	=> 'Light Italic',
				'400'	=> 'Regular',
				'400i'	=> 'Regular Italic',
				'500'	=> 'Medium',
				'500i'	=> 'Medium Italic',
				'700'	=> 'Bold',
				'700i'	=> 'Bold Italic'
				);
				break;
			
			case 'Varela':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;
			
			case 'Varela Round':
				$font_weight = array(
				'400'	=> 'Regular'
				);
				break;
			
			case 'Work Sans':
				$font_weight = array(
				'100'	=> 'Thin',
				'200'	=> 'Extra-Light',
				'300'	=> 'Light',
				'400'	=> 'Regular',
				'500'	=> 'Medium',
				'600'	=> 'Semi-Bold',
				'700'	=> 'Bold',
				'800'	=> 'Extra-Bold',
				'900'	=> 'Black'
				);
				break;

			default:
				$font_weight = array();
		}
		return $font_weight;
	}
}


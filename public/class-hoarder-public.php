<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://metagauss.com
 * @since      1.0.0
 *
 * @package    Hoarder
 * @subpackage Hoarder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Hoarder
 * @subpackage Hoarder/public
 * @author     Vikas Arora <vikas.arora@metagauss.com>
 */
class Hoarder_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $hoarder    The ID of this plugin.
	 */
	private $hoarder;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
        private $url;

        /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $hoarder       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $hoarder, $version ,$url ) {

		$this->hoarder = $hoarder;
		$this->version = $version;
                $this->url = $url;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoarder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoarder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->hoarder, plugin_dir_url( __FILE__ ) . 'css/hoarder-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoarder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoarder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->hoarder, plugin_dir_url( __FILE__ ) . 'js/hoarder-public.js', array( 'jquery' ), $this->version, false );

	}

}

<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://metagauss.com
 * @since      1.0.0
 *
 * @package    Hoarder
 * @subpackage Hoarder/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Hoarder
 * @subpackage Hoarder/includes
 * @author     Vikas Arora <vikas.arora@metagauss.com>
 */
class Hoarder {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Hoarder_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $hoarder    The string used to uniquely identify this plugin.
	 */
	protected $hoarder;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
        
        protected $url;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'HOARDER_VERSION' ) ) {
			$this->version = HOARDER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->hoarder = 'hoarder';
                $this->url = 'https://profilegrid.co/api.php';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
                ob_start();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Hoarder_Loader. Orchestrates the hooks of the plugin.
	 * - Hoarder_i18n. Defines internationalization functionality.
	 * - Hoarder_Admin. Defines all hooks for the admin area.
	 * - Hoarder_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
            
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-activator.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-deactivator.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-loader.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-dbhandler.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-sanitized.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoarder-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hoarder-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-hoarder-public.php';

		$this->loader = new Hoarder_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Hoarder_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Hoarder_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Hoarder_Admin( $this->get_hoarder(), $this->get_version(), $this->get_server_url() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                $this->loader->add_action( 'admin_menu', $plugin_admin, 'hoarder_admin_menu' );
                $this->loader->add_action( 'wp_ajax_hoarder_api_verification', $plugin_admin, 'hoarder_api_verification' );
                $this->loader->add_action('hoarder_fetch_rules', $plugin_admin, 'hoarder_fetch_rules');
                $this->loader->add_action( 'set_user_role',$plugin_admin,'hoarder_change_user_role', 10, 3 ); 

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Hoarder_Public( $this->get_hoarder(), $this->get_version(), $this->get_server_url() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_hoarder() {
		return $this->hoarder;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Hoarder_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

        public function get_server_url()
        {
            return $this->url;
        }
}

<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
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
 * @package    Woo_Extra_Cost
 * @subpackage Woo_Extra_Cost/includes
 * @author     Multidots <wordpress@multidots.com>
 */
class Woo_Extra_Cost {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Extra_Cost_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

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

        $this->plugin_name = 'woo-extra-cost';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Extra_Cost_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Extra_Cost_i18n. Defines internationalization functionality.
     * - Woo_Extra_Cost_Admin. Defines all hooks for the admin area.
     * - Woo_Extra_Cost_Public. Defines all hooks for the public side of the site.
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
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-extra-cost-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-extra-cost-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-extra-cost-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woo-extra-cost-public.php';

        $this->loader = new Woo_Extra_Cost_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woo_Extra_Cost_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Woo_Extra_Cost_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Woo_Extra_Cost_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('woocommerce_init', $plugin_admin, 'woo_extra_cost_product_tab');
        $this->loader->add_action('admin_init', $plugin_admin, 'woo_extra_cost_admin_init_own');
        $this->loader->add_action('admin_menu', $plugin_admin, 'welcome_screen_pages_extra_cost');
        $this->loader->add_action('admin_init', $plugin_admin, 'welcome_screen_do_activation_redirect_extra_cost');
        $this->loader->add_action('admin_head', $plugin_admin, 'welcome_screen_remove_menus_extra_cost');

        $this->loader->add_action('woocommerce_extra_cost_other_plugins', $plugin_admin, 'woocommerce_extra_cost_other_plugins');
        $this->loader->add_action('woocommerce_extra_cost_about', $plugin_admin, 'woocommerce_extra_cost_about');
        $this->loader->add_action('woocommerce_extra_cost_premium_feauter', $plugin_admin, 'woocommerce_extra_cost_premium_feauter');
        $this->loader->add_action('admin_print_footer_scripts', $plugin_admin, 'woocommerce_extra_cost_pointers_footer');
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices_new_version_extra_cost');
        
        $get_notice = get_option('woo-extra-cost-notice-dismissed');
        if (empty($get_notice)) {
            $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices_extra_cost');
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Woo_Extra_Cost_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'woo_add_cart_fee');
        $this->loader->add_filter('woocommerce_paypal_args', $plugin_public, 'paypal_bn_code_filter_wc_extra_cost_free', 99, 1);

        $based_on_country = get_option('wc_extra_cost_country');
        if (isset($based_on_country) && !empty($based_on_country) && $based_on_country == 'yes') {
            $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'woo_add_cart_fee_based_on_country_free');
        }

        $based_on_product = get_option('wc_extra_cost_cart_product');
        if (isset($based_on_product) && !empty($based_on_product) && $based_on_product == 'yes') {
            $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'woo_add_cart_fee_based_on_product_free');
        }

        $based_on_category = get_option('wc_extra_cost_cart_category_product');
        if (isset($based_on_category) && !empty($based_on_category) && $based_on_category == 'yes') {
            $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'woo_add_cart_fee_based_on_product_category_free');
        }
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
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Extra_Cost_Loader    Orchestrates the hooks of the plugin.
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

}

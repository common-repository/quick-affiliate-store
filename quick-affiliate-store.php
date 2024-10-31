<?php
/**
 * Plugin Name: Quick Affiliate Store
 * Description: Monetize your Wordpress site with affiliate marketing. Supports Amazon feed.
 * Version: 0.5.1
 * Text Domain: quick-affiliate-store
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!function_exists('qas_fs')) {
    // Create a helper function for easy SDK access.
    function qas_fs() {
        global $qas_fs;

        if (!isset($qas_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $qas_fs = fs_dynamic_init(array(
                'id'             => '3149',
                'slug'           => 'quick-affiliate-store',
                'type'           => 'plugin',
                'public_key'     => 'pk_c441cf1355e7f5f1baa3e902665d9',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => false,
                'menu'           => array(
                    'slug'       => 'quick_affiliate_store',
                    'account'    => false,
                    'support'    => false,
                    'parent'     => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ));
        }

        return $qas_fs;
    }

    // Init Freemius.
    qas_fs();
    // Signal that SDK was initiated.
    do_action('qas_fs_loaded');
}

class Quick_Affiliate_Store
{
    private $plugin_name;
    private $option_prefix;

    /**
     * Construct the plugin object
     */
    public function __construct()
    {
        $this->plugin_name = $this->option_prefix = 'quick_affiliate_store';
        $plugin_basename = plugin_basename(__FILE__);

        // Include plugin files
        require_once sprintf('%s/admin/class-quick-affiliate-store-settings.php', __DIR__);
        require_once sprintf('%s/includes/feeds/class-quick-affiliate-store-feed-base.php', __DIR__);
        require_once sprintf('%s/includes/feeds/class-quick-affiliate-store-feed-amazon.php', __DIR__);

        new Quick_Affiliate_Store_Settings();

        // add link in plugins listing page
        add_filter('plugin_action_links_' . $plugin_basename , array($this, 'pluginSettingsLink'));
        // add short code definitions
        add_shortcode('qas_product_grid', array($this, 'productBoxesShortcode'));
        // add custom plugin styles
        add_action('wp_enqueue_scripts', array($this, 'addStyles'));
        // load languages
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
    }

    // Add the settings link to the plugins page
    public function pluginSettingsLink($links)
    {
        $settingsLink = '<a href="options-general.php?page=quick_affiliate_store">' . __('Settings', 'quick-affiliate-store') . '</a>';
        array_unshift($links, $settingsLink);
        return $links;
    }

    public function addStyles()
    {
        wp_enqueue_style('qas-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('qas-main', plugins_url('public/css/stylesheet.css', __FILE__));
    }

    public function loadTextDomain()
    {
        load_plugin_textdomain('quick-affiliate-store', false, basename(dirname(__FILE__)) . '/languages/');
    }

    public function productBoxesShortcode($attrs)
    {
        $a = shortcode_atts(array(
            'keywords' => null,
            'show_price' => 'yes',
            'max_products' => null,
            'button_text' => __('Buy', 'quick-affiliate-store'),
        ), $attrs);

        $keywords = trim($a['keywords']);

        if (empty($keywords)) {
            return '';
        }

        // display options
        $showPrice = $a['show_price'] === 'yes';
        $limit = $a['max_products'];
        $buttonText = $a['button_text'];

        ob_start();

        $unfilteredProducts = $this->getProductsFromFeed($keywords);
        $products = $this->filterProducts($unfilteredProducts, $limit);

        include sprintf("%s/public/partials/quick-affiliate-store-products-grid.php", __DIR__);

        return ob_get_clean();
    }

    private function currencyCodeToSymbol($currencyCode)
    {
        if ($currencyCode === 'EUR') {
            return '&euro;';
        } elseif ($currencyCode === 'USD') {
            return '$';
        } else {
            return $currencyCode;
        }
    }

    private function buildFeedHelper()
    {
        return new Quick_Affiliate_Store_Feed_Amazon($this->option_prefix);
    }

    private function getProductsFromFeed($keywords)
    {
        $feedHelper = $this->buildFeedHelper();
        return $feedHelper->searchProductsByKeywords($keywords);
    }

    private function filterProducts($products, $limit = null)
    {
        $filtered = array();

        foreach ($products as $prod) {
            if ($limit !== null && count($filtered) >= $limit) {
                break;
            }
            $filtered[] = $prod;
        }

        return $filtered;
    }
}

// initialize plugin
new Quick_Affiliate_Store();

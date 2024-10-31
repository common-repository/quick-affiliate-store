<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Quick_Affiliate_Store_Settings
{
    private $plugin_name;
    private $option_prefix;

    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
        $this->plugin_name = $this->option_prefix = 'quick_affiliate_store';

        // register actions
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'add_menu'));
    }

    /**
     * hook into WP's admin_init action hook
     */
    public function admin_init()
    {
        add_settings_section(
            $this->option_prefix . '_amazon_api_section',
            __('Amazon Product API', $this->plugin_name),
            array($this, $this->option_prefix . '_settings_amazon_api_section'),
            $this->plugin_name
        );

        add_settings_field(
            $this->option_prefix . '_amazon_api_tag',
            __('Associate Tag', $this->plugin_name),
            array($this, 'settings_field_input_text'),
            $this->plugin_name,
            $this->option_prefix . '_amazon_api_section',
            array(
                'field' => $this->option_prefix . '_amazon_api_tag'
            )
        );

        add_settings_field(
            $this->option_prefix . '_amazon_api_region',
            __('Marketplace Region', $this->plugin_name),
            array($this, 'settings_field_input_text'),
            $this->plugin_name,
            $this->option_prefix . '_amazon_api_section',
            array(
                'field' => $this->option_prefix . '_amazon_api_region'
            )
        );

        add_settings_field(
            $this->option_prefix . '_amazon_api_access_key',
            __('AWS AccessKey', $this->plugin_name),
            array($this, 'settings_field_input_text'),
            $this->plugin_name,
            $this->option_prefix . '_amazon_api_section',
            array(
                'field' => $this->option_prefix . '_amazon_api_access_key'
            )
        );

        add_settings_field(
            $this->option_prefix . '_amazon_api_secret_key',
            __('AWS SecretKey', $this->plugin_name),
            array($this, 'settings_field_input_text'),
            $this->plugin_name,
            $this->option_prefix . '_amazon_api_section',
            array(
                'field' => $this->option_prefix . '_amazon_api_secret_key'
            )
        );

        // Register your plugin's settings
        register_setting($this->plugin_name, $this->option_prefix . '_amazon_api_tag');
        register_setting($this->plugin_name, $this->option_prefix . '_amazon_api_region');
        register_setting($this->plugin_name, $this->option_prefix . '_amazon_api_access_key');
        register_setting($this->plugin_name, $this->option_prefix . '_amazon_api_secret_key');
    }

    public function quick_affiliate_store_settings_amazon_api_section()
    {
        echo 'Authentication credentials for Amazon Product API.';
    }

    public function settings_field_input_text($args)
    {
        // Get the field name from the $args array
        $field = $args['field'];
        // Get the value of this setting
        $value = get_option($field);

        echo sprintf('<input type="text" id="%s" name="%s" value="%s">', $field, $field, $value);
    }

    /**
     * Add a menu
     */
    public function add_menu()
    {
        // Add a page to manage this plugin's settings
        add_options_page(
            'Quick Affiliate Store Settings',
            'Quick Affiliate Store',
            'manage_options',
            $this->option_prefix,
            array($this, 'plugin_settings_page')
        );
    }

    /**
     * Menu Callback
     */
    public function plugin_settings_page()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Render the settings template
        include sprintf("%s/partials/quick-affiliate-store-settings.php", __DIR__);
    }
}
<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

abstract class Quick_Affiliate_Store_Feed_Base
{
    protected $optionsPrefix;

    public function __construct($optionsPrefix)
    {
        $this->optionsPrefix = $optionsPrefix;
    }

    abstract public function searchProductsByKeywords($keywords);

    protected function getRemoteXml($url)
    {
        $response = wp_remote_get(esc_url_raw($url));
        $body = wp_remote_retrieve_body($response);
        $xml = @simplexml_load_string($body);

        return $xml;
    }

    protected function sanitizeKeywords($keywords)
    {
        return str_replace(' ', '+', $keywords);
    }
}

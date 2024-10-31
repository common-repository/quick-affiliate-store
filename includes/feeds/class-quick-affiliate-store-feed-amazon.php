<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Quick_Affiliate_Store_Feed_Amazon extends Quick_Affiliate_Store_Feed_Base
{
    public function searchProductsByKeywords($keywords)
    {
        $params = array(
            'Operation' => 'ItemSearch',
            'Keywords' => $keywords,
            'IncludeReviewsSummary' => false,
            'ResponseGroup' => 'ItemAttributes,Offers,OfferFull,OfferSummary,Images',
            'Condition' => 'New',
            'SearchIndex' => 'All',
        );

        $url = $this->signedUrl($params);

        return $this->parseXml($this->getRemoteXml($url));
    }

    private function parseXml($xml)
    {
        $products = array();

        if (!empty($xml->Items->Item)) {
            foreach ($xml->Items->Item as $p) {
                $item = $p;
                $name = (string) $item->ItemAttributes->Title;
                $image = isset($item->ImageSets) ? (string) $item->ImageSets->ImageSet->MediumImage->URL : null;
                $url = (string) $item->DetailPageURL;

                // From Amazon Associate Link Builder
                $price = isset($item->Offers->Offer->OfferListing->Price->Amount) ? (string) $item->Offers->Offer->OfferListing->Price->Amount : null;
                $listPrice = isset($item->ItemAttributes->ListPrice->Amount) ? (string) $item->ItemAttributes->ListPrice->Amount : null;
                $salePrice = isset($item->Offers->Offer->OfferListing->SalePrice->Amount) ? (string) $item->Offers->Offer->OfferListing->SalePrice->Amount : null;
                $currency = isset($item->ItemAttributes->ListPrice->CurrencyCode) ? (string) $item->ItemAttributes->ListPrice->CurrencyCode : null;

                $savingPercent = isset($item->Offers->Offer->OfferListing->PercentageSaved) ? (int) $item->Offers->Offer->OfferListing->PercentageSaved : null;

                $product = new stdClass;
                $product->url = $url;
                $product->name = $name;
                $product->image = $image;
                $product->currency = $currency;
                $product->strikedPrice = null;
                $product->savingPercent = null;

                if ($salePrice) {
                    $product->price = $this->formatPrice($salePrice);
                    if ($savingPercent > 1) {
                        $product->savingPercent = $savingPercent;
                        $product->strikedPrice = $this->formatPrice($price);
                    }
                } else {
                    $product->price = $this->formatPrice($price);
                    if ($savingPercent > 1) {
                        $product->savingPercent = $savingPercent;
                        $product->strikedPrice = $this->formatPrice($listPrice);
                    }
                }

                $products[] = $product;
            }
        }

        return $products;
    }

    protected function signedUrl($params)
    {
        $accessKeyId = get_option($this->optionsPrefix . '_amazon_api_access_key');
        $secretKey = get_option($this->optionsPrefix . '_amazon_api_secret_key');
        $trackingId = get_option($this->optionsPrefix . '_amazon_api_tag');
        $region = get_option($this->optionsPrefix . '_amazon_api_region');

        $method = 'GET';
        $host = 'webservices.amazon.' . $region;
        $uri = '/onca/xml';

        $params['Service'] = 'AWSECommerceService';
        $params['AssociateTag'] = $trackingId;
        $params['AWSAccessKeyId'] = $accessKeyId;
        $params['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
        $params['Version'] = '2011-08-01';

        ksort($params);

        $canonicalizedQuery = array();
        foreach ($params as $param => $value) {
            $param = str_replace('%7E', '~', rawurlencode($param));
            $value = str_replace('%7E', '~', rawurlencode($value));
            $canonicalizedQuery[] = $param . '=' . $value;
        }

        $canonicalizedQuery = implode('&', $canonicalizedQuery);

        $toSign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalizedQuery;
        $signature = base64_encode(hash_hmac('sha256', $toSign, $secretKey, true));
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        $url = 'http://' . $host . $uri . '?' . $canonicalizedQuery . '&Signature=' . $signature;

        return $url;
    }

    private function formatPrice($price)
    {
        if (null === $price) {
            return null;
        }

        return number_format((float) ($price / 100), 2, ',', '');
    }
}

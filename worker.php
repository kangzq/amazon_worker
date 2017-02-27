<?php

require 'vendor/autoload.php';

class AmazonWorker 
{
    private $domain = 'https://www.amazon.com/';
    private $detail_url = 'dp/';
    private $seller_url = 'gp/offer-listing/';
    private $headers;
    private $cookies;
    private $opts;

    public function __construct()
    {
        $this->headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ];

        $this->opts = [
            'timeout' => 120
        ];
    }

    public function run($asin)
    {
        echo $asin . PHP_EOL;
        //$html = $this->_get_datail_html($asin);
        //$detail = $this->_parse_detail_html($html);

        $html = $this->_get_datail_html($asin, 'seller');
        $sellers = $this->_parse_seller_page($html);
        print_r($sellers);

        //print_r($detail);
    }


    private function _get_datail_html($asin, $type = 'detail')
    {
        if (file_exists("cache/{$asin}_{$type}")) {
            echo 'Detail page loaded from cache.' . PHP_EOL;
            return file_get_contents('cache/'.$asin);
        }

        $detail = '';
        $detail_url = $this->domain . ('seller'===$type ? $this->seller_url : $this->detail_url) . $asin;
        $response = Requests::get($detail_url, $this->headers, $this->opts);

        if ($response && $response->status_code === 200) {
            /*if ($response->cookies) {
                //print_r($response->cookies);
                $this->cookies = Requests_Cookie::parse_from_headers($response->headers);
                print_r($this->cookies);
                //$this->headers['Cookie'] = $response->cookies->format_for_header();
            }*/
            
            //$this->cache->save($asin, $response->body, 900, true);
            file_put_contents("cache/{$asin}_{$type}", trim($response->body));
            $detail = $response->body;
            echo 'Got asin page: '.$asin . PHP_EOL;
        } else {
            echo 'Failed to get page for '. $asin . PHP_EOL;
        }

        return $detail;
    }

    private function _parse_detail_html($html)
    {
        $detail = [];
        $dom = new  PHPHtmlParser\Dom();
        $dom->load($html);
        $detail['title'] = $dom->find('#productTitle', 0)->text();

        $detail['image'] = $dom->find('#landingImage', 0)->getAttribute('data-old-hires');

        $rank_text = trim($dom->find('#SalesRank')->find('td',1)->text());
        $detail['ranktext'] = $rank_text;
        if ($rank_text) {
            preg_match('/#([0-9]+,){0,}[0-9]+\s/', $rank_text, $matches);
            $detail['salesrank'] = preg_replace('/[^0-9]/', '', $matches[0]);
        }
        
        $cats = $dom->find('#wayfinding-breadcrumbs_feature_div')->find('li', 0)->innerHtml();
        $detail['category'] = trim(strip_tags($cats));

        // moreBuyingChoices
        $mbc = $dom->find('#mbc', 0)->find('span.aok-float-right', 0)->innerHtml();
        $mbc = str_replace('&nbsp;', ' ', strip_tags($mbc));
        //$detail['mbc'] = trim($mbc);
        preg_match('/([0-9]+)\snew\sfrom\s(\$[0-9\.,]+)/', trim($mbc), $matches);
        $detail['offers'] = $matches[1];
        $detail['price'] = $matches[2];

        return $detail;
    }

    private function _parse_seller_html($html)
    {
        $detail = [];
        $dom = new  PHPHtmlParser\Dom();
        $dom->load($html);
        

        return $detail;
    }

}


$asin = 'B00S1W66KW';

$worker = new AmazonWorker();
$worker->run($asin);

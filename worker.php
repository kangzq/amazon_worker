<?php

require 'vendor/autoload.php';

use Httpful\Request;
use Sunra\PhpSimple\HtmlDomParser as Parser;

class AmazonWorker 
{
    const UA        = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36';
    const ACCEPT    = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';

    public static function run()
    {
        $productUrl = "http://www.amazon.com/dp/B00I15SB16/";
        $request = Request::get($productUrl)
            ->addHeader('User-Agent', self::UA)
            ->addHeader('Accept', self::ACCEPT)
            ->addHeader('Cookie', '')
            ->send();

        print_r($request);

    }

}

AmazonWorker::run();

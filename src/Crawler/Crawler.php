<?php

namespace PriceAlerts\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class Crawler
{
    private String $baseUrl = "https://www.amazon.de/gp/product/ajax/ref=auto_load_aod";
    private Client $guzzleClient;
    private CookieJar $cookieJar;

    public function __construct()
    {
        $this->guzzleClient = new Client();
        $this->cookieJar    = new CookieJar();
    }

    public function crawl(): SymfonyCrawler
    {
        $productId = "B081FWVSG8";
        $productUrl = $this->baseUrl . '?asin=' . $productId . '&pc=dp&experienceId=aodAjaxMain';

        $response = $this->guzzleClient->request('GET', $productUrl, self::getRequestOptions());

        return new SymfonyCrawler($response->getBody()->getContents());
    }

    private function getRequestOptions(): array
    {
        return array(
            RequestOptions::HEADERS => [
                'X-Request-With' => 'XMLHttpRequest',
                'Accept-Encoding' => 'gzip, deflate, sdch, br',
                'Cache-Control' => 'max-age=0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1)',
                'Connection' => 'keep-alive'
            ],
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => 10,
            ],
            RequestOptions::COOKIES => $this->cookieJar,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::VERIFY => true,
        );
    }
}
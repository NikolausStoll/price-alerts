<?php

namespace PriceAlerts\Parser;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class Parser
{
    const DECIMAL_SEPERATOR = ',';
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function parse(): array
    {
        $result = [];

        $title = $this->getTitle($this->crawler);
        $this->crawler->filter('#aod-offer')->each(function (SymfonyCrawler $node) use (&$result, $title) {
            $parsedResult['title']          = $title;
            $parsedResult['price']          = $this->getPrice($node);
            $parsedResult['currency']       = $this->getCurrency($node);
            $parsedResult['seller']         = $this->getSeller($node);
            $parsedResult['conditionShort'] = $this->getConditionShort($node);
            $parsedResult['conditionLong']  = $this->getConditionLong($node);

            $result[] = $parsedResult;
        });

        return $result;
    }

    private function getTitle(SymfonyCrawler $node): string
    {
        return $this->getInnerTextByFilter($node, '#aod-asin-title-text');
    }

    private function getPrice(SymfonyCrawler $node): string
    {
        $base = $this->getInnerTextByFilter($node, '.a-price-whole');
        $decimal = $this->getInnerTextByFilter($node, '.a-price-fraction');

        return $base . self::DECIMAL_SEPERATOR . $decimal;
    }

    private function getCurrency(SymfonyCrawler $node): string
    {
        return $this->getInnerTextByFilter($node, '.a-price-symbol');
    }

    private function getSeller(SymfonyCrawler $node): string
    {
        return $this->getInnerTextByFilter($node, '#aod-offer-soldBy a');
    }

    private function getConditionShort(SymfonyCrawler $node): string
    {
        return $this->getInnerTextByFilter($node, '#aod-offer h5');
    }

    private function getConditionLong(SymfonyCrawler $node): string
    {
        return $this->getInnerTextByFilter($node, '.expandable-expanded-text');
    }

    private function getInnerTextByFilter(SymfonyCrawler $node, string $filter): string
    {
        $result = $node->filter($filter);
        if ($result->count()) {
            return trim($result->innerText());
        }
        return '';
    }
}
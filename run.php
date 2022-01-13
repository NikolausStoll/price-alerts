<?php

use PriceAlerts\Crawler\Crawler;

require 'vendor/autoload.php';

$sleepTime = 60;
$sleepTimeMultiplier = 2;

$maxPrice = 11.65;

//startCrawler
$crawler = (new Crawler())->crawl();

while(true) {
    $found = false;

    //getTitle
    $title = trim($crawler->filter("#aod-asin-title-text")->innerText());

    $crawler->filter("#aod-offer")->each(function ($node) use ($title, &$found, $maxPrice) {

        //getPrice
        $pricePre = $node->filter(".a-price-whole")->innerText();
        $priceDecimal = $node->filter(".a-price-fraction")->innerText();
        $currency = $node->filter(".a-price-symbol")->innerText();
        $price = $pricePre . ',' . $priceDecimal . ' ' . $currency;
        //getSeller
        $seller = $node->filter("#aod-offer-soldBy a")->innerText();
        //getCondition
        $condition = $node->filter("#aod-offer h5")->innerText();
        //getConditionDescription
        $conditionDescription = '';
        $conditionDescriptionNode = $node->filter("#aod-condition-container .expandable-expanded-text");
        if ($conditionDescriptionNode->count()) {
            $conditionDescription = $conditionDescriptionNode->innerText();
        }

        if (floatval($pricePre . ',' . $priceDecimal) < $maxPrice && $seller === 'Amazon Warehouse') {

            echo "$title ($seller)" .  PHP_EOL;
            echo "$price ($condition)" . PHP_EOL;
            echo $conditionDescription . PHP_EOL;
            echo "---" . PHP_EOL;

            $notificationCommand = 'display notification "Condition: ' . $condition . '" with title "' . $title . '" subtitle "Price: ' . $price . '" sound name "Bottle"';
            $scriptCommand = "osascript -e '" . $notificationCommand . "'";
            $processHandle = popen($scriptCommand, 'r');
            pclose($processHandle);

            $found = true;

        }
    });

    //increase sleeptime when item is found to avoid too m any notifications
    if ($found) {
        $sleepTime *= $sleepTimeMultiplier;
    }

    sleep($sleepTime);
}
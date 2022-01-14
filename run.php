<?php

use PriceAlerts\Crawler\Crawler;
use PriceAlerts\Parser\Parser;

require 'vendor/autoload.php';

const sleepTime            = 60;
const sleepTimeMultiplier  = 2;
const maxPrice             = 220;
const seller               = 'Amazon Warehouse';

$sleepTime = sleepTime;
while(true) {
    $crawler = (new Crawler())->crawl();
    $offers = (new Parser($crawler))->parse();

    $found = 0;
    foreach ($offers as $offer) {
        if (floatval($offer['price']) <= maxPrice && $offer['seller'] === seller) {
            $found = 1;

            //offer found, send notifications
            //send to console
            echo $offer['title'] . ' (' . $offer['seller'] . ')' .  PHP_EOL;
            echo $offer['price'] . $offer['currency'] . '(' . $offer['conditionShort'] . ')' .  PHP_EOL;
            echo $offer['conditionLong'] .  PHP_EOL;
            echo "---" . PHP_EOL;

            //send to os notification center
            sendToOsNotificationCenter(
                $offer['title'],
                $offer['price'] . $offer['currency'],
                $offer['conditionShort'],
                'Bottle'
            );
        }
    }

    //increase sleep time when offer was found to avoid too many notifications
    $sleepTime += $sleepTime * sleepTimeMultiplier * $found;
    sleep($sleepTime);
}

//helpers
function sendToOsNotificationCenter(string $title, string $subtitle, string $message, string $soundName): void
{
    $command = '
        display notification "' . $message . '" with title "' . $title . '" subtitle "' . $subtitle . '" sound name "' . $soundName. '"
    ';
    $osascriptCommand = "osascript -e '" . $command . "'";

    $processHandle = popen($osascriptCommand, 'r');
    pclose($processHandle);
}

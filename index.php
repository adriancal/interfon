<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;

getCurrencySamples();

//echo ($classifier->predict([1, 1, 0]));

function getCurrencySamples() {
    $currencies = ['BTC', 'ETH', 'BCH', 'XRP'];
    $preparedData = [];
    foreach ($currencies as $currency) {
        $apiData = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/histoday?fsym=$currency&tsym=USD&limit=5"), true);

        foreach ($apiData['Data'] as $info) {
            $preparedData[$currency][date('d/m/Y', $info['time'])] = $info['close'];
        }
        krsort($preparedData[$currency]);
    }
    var_dump($preparedData);
}

function mainAction() {
    /*
     * Data de 26:
     * Moneda1: +10   |
     * Moneda2: +1    | => moneda de interes => a+5
     * Moneda3: -1    |
     */

    /*
     * Data de 27:
     * Moneda1: +10   |
     * Moneda2: +5    | => moneda de interes => a-5
     * Moneda3: -1    |
     */
    $samples = [
        [10, 1, -1],
        [10, 5, -1],
    ];

    $samples = array_values($samples);
    $labels = ['a+5', 'a-5'];

    $classifier = new NaiveBayes();
    $classifier->train($samples, $labels);
}

<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;
use Symfony\Component\Yaml\Yaml;

$currencies = ['BTC', 'ETH', 'BCH', 'XRP', 'DASH', 'LTC', 'BTG', 'ADA', 'ETC', 'XMR', 'NEO', 'XEM', 'EOS', 'XLM', 'BCC', 'QTUM', 'ZEC', 'OMG', 'LSK'];

mainAction($_GET['l'], $_GET['c']);

function saveCurrencyHistoricData() {
    global $currencies;
    $preparedData = [];
    foreach ($currencies as $currency) {
        $apiData = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/histoday?fsym=$currency&tsym=USD&limit=365"), true);

        foreach ($apiData['Data'] as $info) {
            $preparedData[$currency][date('d_m_Y', $info['time'])] = $info['close'];
        }

        $yaml = Yaml::dump([$currency => $preparedData[$currency]]);
        file_put_contents("samples/$currency.yaml", $yaml);
    }
}

function getCurrencyValues($limit, $currencyToPredict) {
    global $currencies;

    $currencyValues = [];
    foreach ($currencies as $currency) {
        $yamlValues = Yaml::parseFile("samples/$currency.yaml")[$currency];
        $splicedValues = array_splice($yamlValues, -$limit);

        if (in_array(0, $splicedValues, true)) {
            if ($currency == $currencyToPredict) {
                var_dump('Currency to predict is only ', $splicedValues);
                die;
            }
            continue;
        }
        $currencyValues[$currency] = $splicedValues;
    }

    return $currencyValues;
}

function mainAction($limit, $currencyToPredict) {
    $allCurrencyValues = getCurrencyValues($limit, $currencyToPredict);

    var_dump('Without full values -> ' . (19 - count($allCurrencyValues)));

    $predictionValues[$currencyToPredict] = $allCurrencyValues[$currencyToPredict];
    unset($allCurrencyValues[$currencyToPredict]);

    $samples = $labels = [];
    $isFirst = true;
    foreach ($predictionValues[$currencyToPredict] as $currentDate => $value) {
        if ($isFirst) {
            $isFirst = false;
            continue;
        }

        $sample = [];
        $prevDateTime = (DateTime::createFromFormat('d_m_Y', $currentDate));
        $prevDateTime->modify('-1 day');
        $prevDate = $prevDateTime->format('d_m_Y');
        $nextDateTime = (DateTime::createFromFormat('d_m_Y', $currentDate));
        $nextDateTime->modify('+1 day');
        $nextDate = $nextDateTime->format('d_m_Y');

        $currentValue = $predictionValues[$currencyToPredict][$currentDate];

        if (isset($predictionValues[$currencyToPredict][$nextDate])) {
            $nextValue = $predictionValues[$currencyToPredict][$nextDate];
            $evolution = number_format(((1 - ($currentValue / $nextValue)) * 100), 1);
            $labels[$nextDate] = getLabelForEvolution($evolution);
        }

        foreach ($allCurrencyValues as $currencyValues) {
            $day1Value = $currencyValues[$prevDate];
            $day2Value = $currencyValues[$currentDate];

            if (empty($day1Value) || empty($day2Value)) {
                throw new Exception('Nu este valoare');
            }

            $sampleEvolution = number_format(((1 - ($day1Value / $day2Value)) * 100), 1);
            $sample[] = $sampleEvolution;
        }
        $samples[$currentDate] = $sample;
    }

    $valuesToPredict = array_pop($samples);
    $samples = array_values($samples);
    $labels = array_values($labels);

    $classifier = new NaiveBayes();
    $classifier->train($samples, $labels);

    $correct = $incorrect = 0;
    foreach ($samples as $key => $sample) {
        $prediction = ($classifier->predict($sample));
        $actual = $labels[$key];

        if ($prediction == $actual) {
            $correct += 1;
        } else {
//            var_dump ('a ' . $actual . ' | p ' . $prediction);
            $incorrect +=1;
        }
    }

    var_dump($correct, $incorrect);
}

function getLabelForEvolution($evolution) {
    if ($evolution < -10) {
        return '-a2';
    }

    if ($evolution <= 0) {
        return '-a1';
    }

    if ($evolution >= 10) {
        return 'a2';
    }

    return 'a1';
}

function getSampleForEvolution($evolution) {
    if ($evolution <= -10) {
        return -3;
    }

    if ($evolution <= -5) {
        return -2;
    }

    if ($evolution <= 0) {
        return -1;
    }

    if ($evolution >= 10) {
        return 3;
    }

    if ($evolution >=5 ) {
        return 2;
    }

    return 1;
}

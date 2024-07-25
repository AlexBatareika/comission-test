<?php

require("Commission.php");

$inputFile = $argv[1];
$exchangeRatesApiKey = '018b0c325634a38636873e75e6fa9266';
$ratesUrl = "http://api.exchangeratesapi.io/latest?access_key=$exchangeRatesApiKey";
//$ratesUrl = "https://api.exchangeratesapi.io/latest";

//Here die() is ok, because without rates nothing will work anyway, no need to continue code execution.
//If we need to get commission value in any case, we can comment lines 15, 19, 36, 37, 46 and 47 and uncomment line 39 and 49
try {
    $rates = json_decode(file_get_contents($ratesUrl), true)['rates'];
} catch (Exception $e) {
    die("Failed to fetch exchange rates: " . $e->getMessage() . "\n");
}

if (!$rates || !is_array($rates)) {
    die("Error: Unable to retrieve or parse exchange rates.\n");
}

$transactions = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($transactions as $transactionJson) {

    $decodedTransaction = json_decode($transactionJson, true);

    if (!is_array($decodedTransaction)) {
        echo "Error: Invalid transaction data.\n";
        continue;
    }

    $commission = new Commission(intval($decodedTransaction["bin"]), floatval($decodedTransaction["amount"]), $decodedTransaction["currency"]);

    if(!$commission->euCheck()){
        echo "Error! Customers country can't be identified\n";
        continue;
        //Otherwise, if we can't prove customer is from EU, we can just act as if he is from non-EU country
        //$commission->setIsEu(false);
    }

    $currency = $commission->getCurrency();
    if (isset($rates[$currency]) && $rates[$currency] >= 0){
        $rate = $rates[$currency];
    } else {
        echo "Error! Customers currency can't be identified\n";
        continue;
        //Otherwise, we can just act as if it's EUR
        //$rate = 1;
    }

    $commission->calculateAmount($rate);
    $commission->outputResult();
}

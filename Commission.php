<?php

class Commission
{
    private int $bin;
    private float $amount;
    private float $fixedAmount = 0.0;
    private string $currency;
    private bool $isEu = false;
    public function __construct(int $bin, float $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getCurrency(): string{
        return $this->currency;
    }
    public function setIsEu($isEu): void{
        $this->isEu = $isEu;
    }

    public function euCheck() : bool {
        $euCodes = [ 'AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK'];
        try {
            $binResults = file_get_contents('https://lookup.binlist.net/' . $this->bin);
        } catch (Exception $e) {
            echo "Error: Unable to fetch BIN data. " . $e->getMessage() . "\n";
            return false;
        }

        $binData = json_decode($binResults);

        if ($binData && isset($binData->country->alpha2)) {
            $this->isEu = in_array($binData->country->alpha2, $euCodes, true);
            return true;
        }
        return false;
    }
    public function calculateAmount($rate) : float {
        $amountFixed = ($this->currency === 'EUR' || $rate == 0) ? $this->amount : $this->amount / $rate;
        $this->fixedAmount = ceil($amountFixed * ($this->isEu ? 0.01 : 0.02) * 100) / 100;
        return $this->fixedAmount;
    }

    public function outputResult() : void{
        echo $this->fixedAmount . "\n";
    }

}

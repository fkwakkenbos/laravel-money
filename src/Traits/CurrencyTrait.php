<?php

namespace Flooris\LaravelMoney\Traits;

use Money\Currency;
use Money\Currencies;
use InvalidArgumentException;
use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Currencies\AggregateCurrencies;
use Money\Exception\UnknownCurrencyException;

trait CurrencyTrait
{
    public static function getDefaultCurrency(): Currency
    {
        return new Currency(config('money.default_currency', 'USD'));
    }

    public static function getCurrencies(): Currencies
    {
        $currencyConfig = config('money.currencies', []);
        $aggregate = [];

        if ($currencyConfig['iso'] ?? false) {
            $aggregate[] = static::createCurrencyListFromSource($currencyConfig['iso'], new ISOCurrencies(), 'ISO');
        }

        if ($currencyConfig['bitcoin'] ?? false) {
            $aggregate[] = static::createCurrencyListFromSource($currencyConfig['bitcoin'], new BitcoinCurrencies(), 'Bitcoin');
        }

        if ($currencyConfig['custom'] ?? false) {
            $aggregate[] = new CurrencyList($currencyConfig['custom']);
        }

        return new AggregateCurrencies($aggregate);
    }

    public static function createCurrencyListFromSource(array|string $config, Currencies $source, string $sourceName): Currencies
    {
        if ($config === 'all') {
            return $source;
        }

        if (is_array($config)) {
            $currencies = [];

            foreach ($config as $currencyCode) {
                $currency = new Currency($currencyCode);

                if (! $source->contains($currency)) {
                    throw new UnknownCurrencyException("Cannot find $sourceName currency {$currency->getCode()}");
                }

                $currencies[$currency->getCode()] = $source->subunitFor($currency);
            }

            return new CurrencyList($currencies);
        }

        throw new InvalidArgumentException("$sourceName config must an array or 'all'");
    }
}

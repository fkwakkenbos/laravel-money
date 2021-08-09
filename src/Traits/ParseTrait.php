<?php

namespace Flooris\LaravelMoney\Traits;

use Money\Currency;
use NumberFormatter;
use Money\Currencies;
use Money\MoneyParser;
use Money\Parser\IntlMoneyParser;
use Money\Parser\DecimalMoneyParser;
use Money\Parser\BitcoinMoneyParser;
use Money\Parser\AggregateMoneyParser;
use Money\Parser\IntlLocalizedDecimalParser;

trait ParseTrait
{
    /**
     * @param MoneyParser[] $parsers
     */
    public static function parseByAggregate(string|float $value, Currency|string|null $currency = null, array $parsers = []): static
    {
        return static::parseByParser(new AggregateMoneyParser($parsers), $value, $currency);
    }

    public static function parseByDecimal(string|float $value, Currency|string|null $currency = null, Currencies $currencies = null): static
    {
        return static::parseByParser(
            new DecimalMoneyParser($currencies ?: static::getCurrencies()),
            $value,
            $currency
        );
    }

    public static function parseByBitcoin(string $value, Currency|string|null $currency = null, int $fractionDigits = 2): static
    {
        return static::parseByParser(
            new BitcoinMoneyParser($fractionDigits),
            $value,
            $currency
        );
    }

    public static function parseByIntl(string $value, ?string $locale = null, Currency|string|null $currency = null, ?Currencies $currencies = null, $style = NumberFormatter::DECIMAL): static
    {
        return static::parseByParser(
            new IntlMoneyParser(
                new NumberFormatter($locale ?: static::getLocale(), $style),
                $currencies ?: static::getCurrencies()
            ),
            $value,
            $currency
        );
    }

    public static function parseByIntlLocalizedDecimal(string|float $value, ?string $locale = null, Currency|string|null $currency = null, ?Currencies $currencies = null, $style = NumberFormatter::DECIMAL): static
    {
        return static::parseByParser(
            new IntlLocalizedDecimalParser(
                new NumberFormatter($locale ?: static::getLocale(), $style),
                $currencies ?: static::getCurrencies()
            ),
            $value,
            $currency
        );
    }

    public static function parseByParser(MoneyParser $parser, string|float $value, Currency|string|null $currency = null): static
    {
        $determinedCurrency = match(true) {
            $currency instanceof Currency => $currency,
            is_string($currency) => new Currency($currency),
            default => static::getDefaultCurrency(),
        };

        return static::fromMoney($parser->parse($value, $determinedCurrency));
    }
}

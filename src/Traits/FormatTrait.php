<?php

namespace Flooris\LaravelMoney\Traits;

use NumberFormatter;
use Money\Currencies;
use Money\MoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Currencies\BitcoinCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\BitcoinMoneyFormatter;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Formatter\IntlLocalizedDecimalFormatter;

trait FormatTrait
{
    /**
     * @param MoneyFormatter[] $formatters
     */
    public function formatByAggregate(array $formatters): string
    {
        return $this->formatByFormatter(new AggregateMoneyFormatter($formatters));
    }

    public function formatByDecimal(?Currencies $currencies = null): string
    {
        return $this->formatByFormatter(new DecimalMoneyFormatter($currencies ?: static::getCurrencies()));
    }

    public function formatByBitcoin($fractionDigits = 2, Currencies $currencies = null): string
    {
        return $this->formatByFormatter(new BitcoinMoneyFormatter($fractionDigits, $currencies ?: new BitcoinCurrencies()));
    }

    public function formatByIntl(?string $locale = null, ?Currencies $currencies = null, int $style = NumberFormatter::CURRENCY): string
    {
        return $this->formatByFormatter(new IntlMoneyFormatter(
            new NumberFormatter($locale ?: static::getLocale(), $style),
            $currencies ?: static::getCurrencies(),
        ));
    }

    public function formatByIntlLocalizedDecimal(?string $locale = null, ?Currencies $currencies = null, $style = NumberFormatter::CURRENCY): string
    {
        return $this->formatByFormatter(new IntlLocalizedDecimalFormatter(
            new NumberFormatter($locale ?: static::getLocale(), $style),
            $currencies ?: static::getCurrencies()
        ));
    }

    public function formatByFormatter(MoneyFormatter $formatter): string
    {
        return $formatter->format($this->money);
    }
}

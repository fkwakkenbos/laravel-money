<?php

namespace Flooris\LaravelMoney;

use Money\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MoneyCast implements CastsAttributes
{
    public function __construct(protected ?string $currency = null)
    {
    }

    public function get($model, string $key, $value, array $attributes): Money|null
    {
        return $value === null || $value === '' ? null : new Money($value, $this->determineCurrency($attributes));
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [$key => $value];
        }

        $money = $value instanceof Money ? $value : new Money($value, $this->determineCurrency($attributes));
        $amount = (int)$money->getAmount();

        if (array_key_exists($this->currency, $attributes)) {
            return [$key => $amount, $this->currency => $money->getCurrency()->getCode()];
        }

        return [$key => $amount];
    }

    public function determineCurrency(array $attributes): Currency
    {
        if ($this->currency) {
            $currencyCode = $attributes[$this->currency] ?? $this->currency;
            $currency = new Currency($currencyCode);
            $currencies = Money::getCurrencies();

            if ($currencies->contains($currency)) {
                return $currency;
            }
        }

        return Money::getDefaultCurrency();
    }
}

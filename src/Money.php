<?php

namespace Flooris\LaravelMoney;

use Money\Currency;
use NumberFormatter;
use JsonSerializable;
use Money\Money as MoneyPHP;
use BadMethodCallException;
use Flooris\LaravelMoney\Traits\ParseTrait;
use Flooris\LaravelMoney\Traits\FormatTrait;
use Flooris\LaravelMoney\Traits\CurrencyTrait;
use Illuminate\Contracts\Support\Arrayable;

class Money implements JsonSerializable, Arrayable
{
    use CurrencyTrait, FormatTrait, ParseTrait;

    protected MoneyPHP $money;

    public function __construct(int|string $value, ?Currency $currency = null)
    {
        $this->money = new MoneyPHP($value, $currency ?? static::getDefaultCurrency());
    }

    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    public function __call(string $method, array $arguments)
    {
        if (! method_exists($this->money, $method)) {
            throw new BadMethodCallException("Method $method doesn't exist");
        }

        return static::convertMoneyResult(
            call_user_func_array(
                [$this->money, $method],
                static::convertMoneyArguments($arguments)
            )
        );
    }

    public static function __callStatic(string $method, array $arguments): static
    {
        if (in_array($method, ['min', 'max', 'avg', 'sum'])) {
            return static::fromMoney(
                call_user_func_array(
                    [MoneyPHP::class, $method],
                    static::convertMoneyArguments($arguments)
                )
            );
        }

        return new Money($arguments[0], new Currency($method));
    }

    public static function getLocale(): string
    {
        return config('money.locale', 'en_US');
    }

    public function getMoney(): MoneyPHP
    {
        return $this->money;
    }

    public function getSymbol(?string $locale = null): string
    {
        $formatter = new NumberFormatter($locale ?: static::getLocale(), NumberFormatter::CURRENCY);
        $formatter->setPattern('¤');
        $formatter->setAttribute(NumberFormatter::MAX_SIGNIFICANT_DIGITS, 0);
        $formattedPrice = $formatter->formatCurrency(0, $this->money->getCurrency()->getCode());
        $zero = $formatter->getSymbol(NumberFormatter::ZERO_DIGIT_SYMBOL);

        return str_replace($zero, '', $formattedPrice);
    }

    public static function fromMoney(MoneyPHP $instance): static
    {
        return new static($instance->getAmount(), $instance->getCurrency());
    }

    public function jsonSerialize(): array
    {
        return array_merge($this->money->jsonSerialize(), [
            'formatted' => $this->formatByIntl(),
            'symbol' => $this->getSymbol(),
        ]);
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    private static function convertMoneyArguments(array $arguments = []): array
    {
        $args = [];

        foreach ($arguments as $argument) {
            $args[] = $argument instanceof static ? $argument->getMoney() : $argument;
        }

        return $args;
    }

    private static function convertMoneyResult(mixed $result): mixed
    {
        return $result instanceof MoneyPHP ? static::fromMoney($result) : $result;
    }


    private static function convertMoneyResults(mixed $result): mixed
    {
        if (!is_array($result)) {
            return static::convertMoneyResult($result);
        }

        $results = [];

        foreach ($result as $item) {
            $results[] = static::convertMoneyResult($item);
        }

        return $results;
    }
}

<?php

namespace IdeaToCode\Nova\Fields\Accounting;

use NumberFormatter;
use Brick\Money\Money;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Currency;
use Brick\Money\Context\CustomContext;
use Symfony\Polyfill\Intl\Icu\Currencies;
use Brick\Money\Currency as BrickCurrency;

class Accounting extends Currency
{

    protected static $currencyCallback;
    protected $typeCallback;

    public static function setCallback(callable|string $cb)
    {
        self::$currencyCallback = $cb;
    }
    // public function __construct($name, $attribute = null, $resolveCallback = null)
    public static function make(...$arguments)
    {

        // $d = parent::__construct($name, $attribute = null, $resolveCallback = null);
        // $d = Currency::make($name, $attribute, $resolveCallback);

        $elem = new static(...$arguments);
        $elem->typeCallback = $elem->defaultTypeCallback();

        $elem->step($elem->getStepValue())
            ->currency(is_callable(self::$currencyCallback) ? (self::$currencyCallback)() : self::$currencyCallback ?? config('nova.currency'))
            ->asHtml()
            ->displayUsing(function ($value, $resource, $attribute) use ($elem) {

                $class = "text-green-500";
                $res = ($elem->typeCallback)($value);

                if ($elem->minorUnits) {
                    $value = $value / (10 ** Currencies::getFractionDigits($elem->currency));
                }


                $fmt = numfmt_create($elem->locale . '@currency=' . $elem->currency, NumberFormatter::CURRENCY);
                $dfmt = numfmt_create($elem->locale . '@currency=' . $elem->currency, NumberFormatter::DECIMAL);

                $cnt = strlen(substr(strrchr($elem->step, "."), 1));

                $dfmt->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $cnt);
                $dfmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $cnt);

                $value = $dfmt->format(abs($value));

                if ($res === true) {
                    $value = "(" . $value . ")";
                    $class = "text-red-500";
                } elseif (is_null($res)) {
                    $class = "";
                }
                $symbol = numfmt_get_symbol($fmt, NumberFormatter::CURRENCY_SYMBOL);

                return view('itc-accounting::field-accounting', compact('value', 'class', 'symbol'))->render();
            });

        return $elem;
    }
    public function type(callable $typeCallback)
    {
        $this->typeCallback = $typeCallback;
        return $this;
    }
    protected function defaultTypeCallback()
    {
        return function ($value) {
            if ($value == 0) return null;
            return $value < 0;
        };
    }
}

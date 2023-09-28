<?php

use NumberFormatter;

use \App\Models\AppconfigModel;

if (!function_exists('currencySide')) {

    function currencySide()
    {
        $appData = (new AppconfigModel())->getAll();

        $fmt = new \NumberFormatter($appData['number_locale'], \NumberFormatter::CURRENCY);
        $fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $appData['currency_symbol']);
        return !preg_match('/^¤/', $fmt->getPattern());
    }
}

if (!function_exists('quantityDecimals')) {

    function quantityDecimals()
    {
        $appData = (new AppconfigModel())->getAll();

        return $appData['quantity_decimals'] ? $appData['quantity_decimals'] : 0;
    }
}

if (!function_exists('totalsDecimals')) {

    function totalsDecimals()
    {
        $appData = (new AppconfigModel())->getAll();

        return $appData['currency_decimals'] ? $appData['currency_decimals'] : 0;
    }
}

if (!function_exists('toCurrency')) {

    function toCurrency($number)
    {
        return toDecimals($number, 'currency_decimals', \NumberFormatter::CURRENCY);
    }
}

if (!function_exists('toCurrencyNoMoney')) {

    function toCurrencyNoMoney($number)
    {
        return toDecimals($number, 'currency_decimals');
    }
}

if (!function_exists('toTaxDecimals')) {

    function toTaxDecimals($number)
    {
        if (empty($number)) {
            return $number;
        }

        return toDecimals($number, 'tax_decimals');
    }
}

if (!function_exists('toQuantityDecimals')) {

    function toQuantityDecimals($number)
    {
        return toDecimals($number, 'quantity_decimals');
    }
}

if (!function_exists('toDecimals')) {

    function toDecimals($number, $decimals, $type = NumberFormatter::DECIMAL)
    {
        $appData = (new AppconfigModel())->getAll();
        if (!isset($number)) {
            return $number;
        }

        $fmt = new NumberFormatter($appData['number_locale'], $type);
        $fmt->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        if (empty($appData['thousands_separator'])) {
            $fmt->setAttribute(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
        }

        $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $appData['currency_symbol']);

        return $fmt->format((float) $number);
    }
}

if (!function_exists('parseDecimals')) {

    function parseDecimals($number)
    {
        if (empty($number)) {
            return $number;
        }

        $appData = (new AppconfigModel())->getAll();

        $fmt = new \NumberFormatter($appData['number_locale'], \NumberFormatter::DECIMAL);
        if (empty($appData['thousands_separator'])) {
            $fmt->setAttribute(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
        }
        return $fmt->parse($number);
    }
}

if (!function_exists('dateformatMomentJs')) {

    function dateformatMomentJs($phpFormat)
    {
        $SYMBOLS_MATCHING = [
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'o',
            'w' => 'e',
            'z' => 'DDD',
            'W' => 'W',
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '', // no equivalent
            'L' => '', // no equivalent
            'o' => 'YYYY',
            'Y' => 'YYYY',
            'y' => 'YY',
            'a' => 'a',
            'A' => 'A',
            'B' => '', // no equivalent
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'SSS',
            'e' => 'zz', // deprecated since version $1.6.0 of moment.js
            'I' => '', // no equivalent
            'O' => '', // no equivalent
            'P' => '', // no equivalent
            'T' => '', // no equivalent
            'Z' => '', // no equivalent
            'c' => '', // no equivalent
            'r' => '', // no equivalent
            'U' => 'X'
        ];

        return strtr($phpFormat, $SYMBOLS_MATCHING);
    }
}

if (!function_exists('dateformatBootstrap')) {

    function dateformatBootstrap($phpFormat)
    {
        $SYMBOLS_MATCHING = [
            // Day
            'd' => 'dd',
            'D' => 'd',
            'j' => 'd',
            'l' => 'dd',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => '',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yyyy',
            'y' => 'yy',
            // Time
            'a' => 'p',
            'A' => 'P',
            'B' => '',
            'g' => 'H',
            'G' => 'h',
            'h' => 'HH',
            'H' => 'hh',
            'i' => 'ii',
            's' => 'ss',
            'u' => ''
        ];

        return strtr($phpFormat, $SYMBOLS_MATCHING);
    }
}

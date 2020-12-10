<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'symbol', 'regex', 'description', 'active',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function getActiveCurrencies($order, $direction)
	{
		return Currency::where('active', 1)->orderBy($order, $direction)->get();
	}

	public function getSymbol()
	{
		$fmtr = new \NumberFormatter( $this->regex, \NumberFormatter::CURRENCY);
		return $fmtr->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
	}

	public function getFormat($forJavaScript = false)
	{
		$fmtr = new \NumberFormatter( $this->regex, \NumberFormatter::CURRENCY );

		return [
				'regex' => $forJavaScript ? str_replace('_', '-', $this->regex) : $this->regex,
				'symbol' => $this->symbol,
				'fdigit' => $fmtr->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS),
				'min' => pow(10, 0-$fmtr->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS)),
				'icon' => $fmtr->getSymbol(\NumberFormatter::CURRENCY_SYMBOL),
			];
	}

	public function getConversionRatio($targetCurrency)
	{
		if ($this->currency_id == (is_int($targetCurrency) ? $targetCurrency : $targetCurrency->currency_id)) {
			return 1.00;
		}

		$fromSymbol = $this->symbol;
		$toSymbol = is_int($targetCurrency) ? Currency::find($targetCurrency)->symbol : $targetCurrency->symbol;

		try {
			$result = Forex::getExchangeRate($fromSymbol, $toSymbol, true);
		} catch (Exception $e) {
			// use hardcode value if API call fails
			return config('currency.conversion.'.$fromSymbol.'.'.$toSymbol);
		}

		return $result;
	}
}

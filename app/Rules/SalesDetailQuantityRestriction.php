<?php

namespace App\Rules;

use App\SalesDetail;
use Illuminate\Contracts\Validation\Rule;

class SalesDetailQuantityRestriction implements Rule
{
    private $date = null;
    private $owner_id = -1;
    private $lineIds = null;
    private $limit = null;
    private $attribute = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($lines, $date, $owner_id)
    {
        $this->lineIds = $lines;
        $this->date = $date;
        $this->ownerId = $owner_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
      $this->attribute = $attribute;
      $salesDetailObj = SalesDetail::find($this->lineIds[intval(explode(".", $attribute)[1])]);
      $numericValue = floatval($value);
			// validate inventory availability
      if ($salesDetailObj->uniqueTradable->stockable) {
        $this->limit = $remainingQuantity = floatval($salesDetailObj->ordered_quantity - $salesDetailObj->shipped_quantity);
  			if ($salesDetailObj->header->isOrder()) {
          $numericInventory = floatval($salesDetailObj->uniqueTradable->getInventory($this->date, $salesDetailObj->header->shipping_location_id, $this->owner_id));
          $this->limit = min($this->limit, $numericInventory);
  				return ($numericValue <= $remainingQuantity) &&
                ($numericValue <= $numericInventory);
  			}
        if ($salesDetailObj->header->isReturn()) {
  				return ($numericValue <= $remainingQuantity);
        }
        return false;
      }
      return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return str_replace([':attribute', ':limit'], [$this->attribute, $this->limit], trans('validation.less_than_or_equal_to'));
    }
}

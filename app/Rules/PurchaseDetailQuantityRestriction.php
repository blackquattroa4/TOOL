<?php

namespace App\Rules;

use App\PurchaseDetail;
use Illuminate\Contracts\Validation\Rule;

class PurchaseDetailQuantityRestriction implements Rule
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
      $purchaseDetailObj = PurchaseDetail::find($this->lineIds[intval(explode(".", $attribute)[1])]);
      $numericValue = floatval($value);
			// validate inventory availability
      if ($purchaseDetailObj->uniqueTradable->stockable) {
        $this->limit = $remainingQuantity = floatval($purchaseDetailObj->ordered_quantity - $purchaseDetailObj->shipped_quantity);
  			if ($purchaseDetailObj->header->isOrder()) {
  				return ($numericValue <= $remainingQuantity);
  			}
        if ($purchaseDetailObj->header->isReturn()) {
          $numericInventory = floatval($purchaseDetailObj->uniqueTradable->getInventory($this->date, $purchaseDetailObj->receiving_location_id, $this->owner_id));
          $this->limit = min($this->limit, $numericInventory);
  				return ($numericValue <= $remainingQuantity) &&
                ($numericValue <= $numericInventory);
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

<?php

namespace App\Validations;

use DB;

class WarehouseBinDuplicationValidation
{
    private $bins = null;

    public function validate($attribute, $value, $parameters, $validator)
    {
      if (is_null($this->bins)) {
        foreach ($validator->getData()['location'] as $index => $location_id) {
          if (!isset($this->bins[$location_id])) {
            $this->bins[$location_id] = [];
          }
          if (!isset($this->bins[$location_id][$validator->getData()['name'][$index]])) {
            $this->bins[$location_id][$validator->getData()['name'][$index]] = 1;
          } else {
            $this->bins[$location_id][$validator->getData()['name'][$index]]++;
          }
        }
      }

      // grab corresponding 'name' parameter.
      // for example, if we are validating 'name.5',
      // then we try to grab location from 'location.5'
      $sourceIndices = explode('.', $attribute);
      $targetLocationIndices = explode('.', $parameters[0]);

      if (count($sourceIndices) != count($targetLocationIndices)) {
        return false;
      }

      // grab location value
      $resultIndices = array_map(function($source, $target) {
        return ($target == '*') ? $source : $target;
      }, $sourceIndices, $targetLocationIndices);

      $targetLocationValue = $validator->getData();
      foreach ($resultIndices as $index) {
        $targetLocationValue = $targetLocationValue[$index];
      }

      return $this->bins[$targetLocationValue][$value] <= 1;
    }
}

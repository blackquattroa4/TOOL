<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_id', 'purpose', 'is_default', 'name', 'unit', 'street', 'district', 'city', 'state', 'country', 'zipcode',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getAddressesIndexedByEntity($entity_type, $address_purpose) {
      //DB::statement('SET GLOBAL group_concat_max_len = 1000000');
      return json_decode(DB::select("
        SELECT
          concat('{',group_concat(t3.binding),'}') as binding
        FROM
          (SELECT
            concat('\"',t2.entity_id,'\":[',group_concat(t2.binding),']') as binding
          FROM
            (SELECT
              t1.entity_id, concat('{\"id\":',t1.id,',\"is_default\":',t1.is_default,
                ',\"name\":\"',t1.name,'\",\"street\":\"',t1.street,
                '\",\"unit\":\"',t1.unit,'\",\"district\":\"',t1.district,
                '\",\"city\":\"',t1.city,'\",\"state\":\"',t1.state,
                '\",\"country\":\"',t1.country,'\",\"zipcode\":\"',t1.zipcode,
                '\"}') as binding
            FROM
              (SELECT
                addresses.*
              FROM
                addresses
              LEFT JOIN taxable_entities ON taxable_entities.id = addresses.entity_id
              WHERE taxable_entities.type in ('" . implode("','", $entity_type) . "') AND addresses.purpose = '" . $address_purpose . "') t1) t2
            GROUP BY t2.entity_id) t3")[0]->binding);
    }

}

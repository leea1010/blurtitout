<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertySaleHistory extends Model
{
    protected $guarded = [];

    public static function findByPropertyNumber($propertyNumber)
    {
        return self::where('property_number', $propertyNumber)->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class LandlordModel extends Model
{
    protected $connection = 'landlord';
}

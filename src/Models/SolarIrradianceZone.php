<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class SolarIrradianceZone extends Model
{
    protected $connection = 'iq';
    protected $table = 'tRegions';
    protected $primaryKey = 'Id';

    public $timestamps = false;
    
    public static function byCode($code)
    {
        return static::firstWhere('ZoneId', $code);
    }
}
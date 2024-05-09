<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tRegions';
    protected $primaryKey = 'Id';

}
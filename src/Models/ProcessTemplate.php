<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessTemplate extends Model
{
    protected $connection = 'iq';
    protected $table = 'tProcessTemplate';
    protected $primaryKey = 'Id';
    
    public $timestamps = false;
}
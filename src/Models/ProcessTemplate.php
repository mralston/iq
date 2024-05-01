<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessTemplate extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tProcessTemplate';
    protected $primaryKey = 'Id';
}
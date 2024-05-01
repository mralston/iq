<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tUser';
    protected $primaryKey = 'Id';

}
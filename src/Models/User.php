<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'iq';
    protected $table = 'tUsers';
    protected $primaryKey = 'Id';

}
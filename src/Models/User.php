<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'iq';
    protected $table = 'tUsers';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';

    protected function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

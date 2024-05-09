<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Mralston\Iq\Models\Scopes\VatRateScope;

class VatRate extends Model
{
    protected $connection = 'iq';
    protected $table = 'tLists';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';
    
    protected static function booted()
    {
        static::addGlobalScope(new VatRateScope);
    }
}
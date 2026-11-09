<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Mralston\Iq\Models\Concerns\UsesIqTimezone;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mralston\Iq\Models\Scopes\BranchScope;

class Branch extends Model
{
    use UsesIqTimezone;

    protected $connection = 'iq';
    protected $table = 'tLists';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'BranchId', 'Id');
    }
}

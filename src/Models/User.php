<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Mralston\Iq\Models\Concerns\UsesIqTimezone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    use UsesIqTimezone;

    protected $connection = 'iq';
    protected $table = 'tUsers';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }
}

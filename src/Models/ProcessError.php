<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Mralston\Iq\Models\Concerns\UsesIqTimezone;

class ProcessError extends Model
{
    use UsesIqTimezone;

    protected $connection = 'iq';
    protected $table = 'Process_Errors';
    protected $primaryKey = 'ErrorID';

    const UPDATED_AT = null;

    protected $fillable = [
        'ErrorNumber',
        'ErrorSeverity',
        'ErrorState',
        'ErrorProcedure',
        'ErrorLine',
        'ErrorMessage',
    ];
}

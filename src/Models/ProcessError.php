<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessError extends Model
{
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

<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessAction extends Model
{
    protected $connection = 'iq';
    protected $table = 'tProcessActions';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'DateCreated';
    const UPDATED_AT = null;

    protected $fillable = [
        'CustomerId',
        'ProcessId',
        'Description',
        'DateDue',
        'DateChecked',
        'SequenceId',
        'BranchId',
        'DecOrProc',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerId', 'Id');
    }

    public function processTemplate()
    {
        return $this->belongsTo(ProcessTemplate::class, 'ProcessId', 'Id');
    }
}

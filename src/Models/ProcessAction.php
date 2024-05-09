<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

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

}
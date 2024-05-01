<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessAction extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tProcessActions';
    protected $primaryKey = 'Id';
    
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
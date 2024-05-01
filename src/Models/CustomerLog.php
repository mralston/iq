<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLog extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tCustLog';
    protected $primaryKey = 'Id';
    
    protected $fillable = [
        'CustomerId',
        'Name',
        'Postcode',
        'SortName',
    ];

}
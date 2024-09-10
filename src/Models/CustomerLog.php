<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLog extends Model
{
    protected $connection = 'iq';
    protected $table = 'tCustLog';
    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'CustomerId',
        'Name',
        'Postcode',
        'SortName',
    ];

}

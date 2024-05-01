<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tUs';
    protected $primaryKey = 'Id';
    
    protected $fillable = [
        'LastCustInvoice',
    ];

}
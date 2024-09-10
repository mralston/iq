<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'iq';
    protected $table = 'tUs';
    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'LastCustInvoice',
    ];
}

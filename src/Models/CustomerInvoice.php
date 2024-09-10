<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    protected $connection = 'iq';
    protected $table = 'tCustomerInvoice';
    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'CustomerId',
        'InvDate',
        'UserId',
        'InvoiceNo',
        'VATRateId',
        'AmountDue',
        'OrderId',
        'Description',
        'InvType',
        'Commissioned',
        'ActAmt',
    ];

}

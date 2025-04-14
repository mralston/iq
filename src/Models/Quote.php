<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $connection = 'iq';
    protected $table = 'tQuotes';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'CustomerID',
        'QuoteID',
        'MpanNumber',
        'ExportTariff',
        'FeedInTariff',
    ];
}

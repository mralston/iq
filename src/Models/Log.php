<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Mralston\Iq\Models\Concerns\UsesIqTimezone;

class Log extends Model
{
    use UsesIqTimezone;

    protected $connection = 'iq';
    protected $table = 'logtable';

    const UPDATED_AT = false;

    protected $fillable = [
        'quoteid',
        'iq_id',
        'customer_portal_event_id',
        'eventtype',
        'source_app',
        'brandkey',
        'data',
        'uuid',
    ];
}

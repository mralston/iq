<?php

namespace Mralston\Iq\Models\Concerns;

use Illuminate\Support\Carbon;

/**
 * IQ stores local wall-clock times, so the timestamps Eloquent stamps on create and update are
 * taken in config('iq.timezone'), or the application's timezone when that is not set.
 */
trait UsesIqTimezone
{
    public function freshTimestamp()
    {
        return Carbon::now(static::iqTimezone());
    }

    public static function iqTimezone(): string
    {
        return config('iq.timezone') ?? config('app.timezone') ?? date_default_timezone_get();
    }
}

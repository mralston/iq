<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One day of a customer's installation. An installation can take several days, and a day that is
 * cancelled or moved keeps its row with that status rather than being removed.
 */
class InstallDay extends Model
{
    protected $connection = 'iq';
    protected $table = 'tInstallDays';
    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'CustomerId',
        'InstallDay',
        'StatusId',
        'CommentId',
        'IssueCategoryId',
        'PrimaryTeam',
        'SecondaryTeam',
        'Notes',
    ];

    protected $casts = [
        'InstallDay' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerId', 'Id');
    }
}

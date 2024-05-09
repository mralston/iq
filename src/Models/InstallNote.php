<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class InstallNote extends Model
{
    protected $connection = 'iq';
    protected $table = 'tInstallNotes';
    
    public $timestamps = false;

    protected $fillable = [
        'CustomerId',
        'UserId',
        'DateNote',
        'Notes',
        'StatusId',
    ];
}
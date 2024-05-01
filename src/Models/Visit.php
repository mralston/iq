<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'tVisit';
    protected $primaryKey = 'Id';
    
    protected $fillable = [
        'CustomerId',
        'VisitDate',
        'UserId',
        'NoPanels',
        'PanelTypeId',
        'StatusId',
        'DateSold',
        'Reference',
        'TileTypeId',
        'Scaffold',
        'ExpiryDate',
    ];

}
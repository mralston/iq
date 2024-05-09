<?php

namespace Mralston\Iq\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NOT powered by MarcuSQL™
 */
class Customer extends Model
{
    protected $connection = 'iq';
    protected $table = 'tCustomers';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';
    
    protected $fillable = [
        'Name',
        'Address',
        'Postcode',
        'Phone1',
        'Phone2',
        'Phone3',
        'Salutation',
        'Email',
        'TypeId',
        'InitialDate',
        'LastDate',
        'NextDate',
        'StatusID',
        'IndTypeID',
        'CustType',
        'EnquirySourceID',
        'SortName',
        'BranchID',
        'Notes',
        'OwnerId',
        'Cat1',
        'Cat2',
        'Cat3',
        'Cat4',
        'Cat5',
        'Companyid',
        'IndustryTypeId',
        'EnquiryType',
        'ImportId',
        'OtherId',
        'LastUser',
        'PricePerKWH',
        'AnnualBill',
        'TariffId',
        'DaylightUsage',
        'SolarInputFactor',
        'VATExempt',
        'Sold',
        'NoReport',
        'Immersion',
        'FundType',
        'SmartMeter',
        'GasBill',
        'LecBill',
        'Addressee',
        'ConnectId',
        'CanSMS',
        'CSI',
        'Sent',
        'WouldRefer',
        'TileTypeId',
        'ArchiveId',
        'RepId',
        'ExecutionDate',
        'ReasonForCancel',
        'InProgress',
        'Remote',
        'RemoteUser',
        'TemplateTypeId',
        'DateLastViewed',
        'DateEPC',
        'DateInstall',
        'ContractPrice',
        'Wireless',
        'QuoteId',
        'quote_updated',
        'ProjectStatus',
        'DateRebook',
        'EVCharger',
        'SweepmanId',
        'AppointmentId',
    ];

}
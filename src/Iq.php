<?php

namespace Mralston\Iq;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mralston\Iq\Enums\Iq as Enum;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\User;

class Iq
{
    public static function createCustomer(array $attrs, User $rep, Company $company): Customer
    {
        // TODO: Validate $attrs

        return DB::transaction(function () use ($attrs, $rep, $company) {

            // TODO: Create Customer
            $customer = Customer::create([
                'Name' => collect([
                    $attrs['title'],
                    $attrs['first_name'],
                    $attrs['last_name']
                ])->join(' '),
                'Address' => $attrs['address'],
                'Postcode' => $attrs['post_code'],
                'Phone1' => $attrs['phone1'],
                'Phone2' => $attrs['phone2'],
                'Phone3' => $attrs['phone3'],
                'Salutation' => $attrs['salutation'],
                'Email' => $attrs['email'],
                'TypeId' => $attrs['type'] ?? Enum::DEFAULT_TYPE_ID,
                'InitialDate' => $attrs['contract_signed_at'],
                'LastDate' => Carbon::now(),
                'NextDate' => $attrs['next_date'] ?? Carbon::now(),
                'StatusID' => $attrs['status'] ?? Enum::DEFAULT_CUSTOMER_STATUS,
                'IndTypeID' => $attrs['individual_type'] ?? Enum::DEFAULT_INDIVIDUAL_TYPE,
                'CustType' => $attrs['customer_type'] ?? Enum::DEFAULT_CUSTOMER_TYPE,
                'EnquirySourceID' => $attrs['enquiry_source'] ?? Enum::DEFAULT_ENQUIRY_SOURCE,
                'SortName' => $attrs['last_name'],
                'BranchID' => $rep->BranchId,
                'Notes' => null, // TODO: Piece this together
                'OwnerId' => $rep->id,
                'Cat1' => $attrs['category1'] ?? Enum::DEFAULT_CATEGORY,
                'Cat2' => $attrs['category2'] ?? Enum::DEFAULT_CATEGORY,
                'Cat3' => $attrs['category3'] ?? Enum::DEFAULT_CATEGORY,
                'Cat4' => $attrs['category4'] ?? Enum::DEFAULT_CATEGORY,
                'Cat5' => $attrs['category5'] ?? Enum::DEFAULT_CATEGORY,
                'Companyid' => $company->id,
                'IndustryTypeId' => $attrs['industry_type'] ?? Enum::DEFAULT_INDUSTRY_TYPE,
                'EnquiryType' => $attrs['enquiry_type'] ?? Enum::DEFAULT_ENQUIRY_TYPE,
                'ImportId' => $attrs['model_id'],
                'OtherId' => $attrs['other'] ?? Enum::DEFAULT_INTEGER,
                'LastUser' => config('iq.auto_user'),
                'PricePerKWH' => Enum::DEFAULT_PRICE_PER_KWH,
                'AnnualBill' => $attrs['annual_bill'],
                'TariffId' => $attrs['tariff'] ?? Enum::DEFAULT_TARIFF,
                'DaylightUsage' => $attrs['daylight_usage'] ?? Enum::DEFAULT_DAYLIGHT_USAGE,
                'SolarInputFactor' => null, // TODO: Map the solar irradiance zone code from the app to a record from tRegions (note some regions are missing!) and use tRegions.SolarRadiationValue
                'VATExempt' => $attrs['vat_exempt'] ?? Enum::DEFAULT_VAT_EXEMPT,
                'Sold' => $attrs['sold'] ?? 0,
                'NoReport' => $attrs['no_report'] ?? 0,
                'Immersion' => $attrs['immersion'] ?? 0,
                'FundType', // TODO: Populate value
                'SmartMeter' => $attrs['smart_meter'] ?? 0,
                'GasBill' => $attrs['gas_bill'] ?? 0,
                'LecBill' => $attrs['electricity_bill'] ?? 0,
                'Addressee', // TODO: Populate value
                'ConnectId' => $attrs['connect'] ?? 0,
                'CanSMS', // TODO: Set to 1 if phone number begins with 07
                'CSI' => $attrs['csi'] ?? 0,
                'Sent' => $attrs['sent'] ?? 0,
                'WouldRefer' => $attrs['would_refer'] ?? 0,
                'TileTypeId', // TODO: Populate value
                'ArchiveId' => 0,
                'RepId',
                'ExecutionDate' => $attrs['execution_date'] ?? null,
                'ReasonForCancel' => 0,
                'InProgress' => $attrs['in_progress'] ?? 0,
                'Remote' => $attrs['remote'] ?? 0,
                'RemoteUser' => $attrs['remote_user'] ?? 0,
                'TemplateTypeId',
                'DateLastViewed' => Carbon::now(),
                'DateEPC' => '1899-12-30', // TODO: WTF?
                'DateInstall' => $attrs['install_date'] ?? null,
                'ContractPrice' => $attrs['contract_price'] ?? 0,
                'Wireless' => $attrs['wireless'] ?? 1,
                'QuoteId' => $attrs['model_id'],
                'quote_updated', // TODO: Populate value
                'ProjectStatus' => $attrs['project_status'] ?? 0,
                'DateRebook' => '1899-12-30', // TODO: WTF?
                'EVCharger', // TODO: Populate value
                'SweepmanId' => $attrs['sweep_man'] ?? 0,
                'AppointmentId' => $attrs['appointment_id'],
            ]);

            // TODO: Create InstallNote

            // TODO: Create CustomerLog

            // TODO: Create Visit

            // TODO: Create CustomerInvoice

            // TODO: Create ProcessAction for installation of solar / battery / toy products

            // TODO: Create additional ProcessAction for EV charger??? TBC

            return $customer;
        });
    }
}
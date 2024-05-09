<?php

namespace Mralston\Iq;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mralston\Iq\Enums\Iq as Enum;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\Region;
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
                'Notes' => $this->buildNotes(
                    $attrs['quote_notes'],
                    $attrs['additional_notes'],
                    $attrs['battery_notes'],
                    $attrs['contract_notes']
                ),
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
                'SolarInputFactor' => $this->getSolarInputFactor($attrs['sap_zone']),
                'VATExempt' => $attrs['vat_exempt'] ?? Enum::DEFAULT_VAT_EXEMPT,
                'Sold' => $attrs['sold'] ?? 0,
                'NoReport' => $attrs['no_report'] ?? 0,
                'Immersion' => $attrs['immersion'] ?? 0,
                'FundType' => $attrs['fund_type'] ?? 0,
                'SmartMeter' => $attrs['smart_meter'] ?? 0,
                'GasBill' => $attrs['gas_bill'] ?? 0,
                'LecBill' => $attrs['electricity_bill'] ?? 0,
                'Addressee' => $attrs['salutation'] ?? null,
                'ConnectId' => $attrs['connect'] ?? 0,
                'CanSMS' => intval($this->canSms(
                    $attrs['phone1'] ?? null,
                        $attrs['phone2'] ?? null,
                        $attrs['phone3'] ?? null
                )),
                'CSI' => $attrs['csi'] ?? 0,
                'Sent' => $attrs['sent'] ?? 0,
                'WouldRefer' => $attrs['would_refer'] ?? 0,
                'TileTypeId', // TODO: Populate value
                'ArchiveId' => 0,
                /* RepId includes customer ID, so it is created after the customer record is created */
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

            // Fill in RepId (repository ID) after customer record has been created as it contains the customer ID
            $customer->update([
                'RepId' => $this->buildRepositoryId($attrs['post_code'], $attrs['last_name'], $customer->id)
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

    private function canSms(...$phoneNumbers): bool
    {
        foreach (func_get_args() as $phoneNumber) {
            if (Str::of($phoneNumber)->substr(0, 2) == '07') {
                return true;
            }
        }

        return false;
    }

    private function buildNotes(?string $quoteNotes = null, ?string $additionalNotes = null, ?string $batteryNotes = null, ?string $contractNotes = null): string
    {
        $compiledNotes = '***** ' .
            Carbon::now()->format('d/m/Y h:i A') .
            ' (Quote Notes) *****' .
            "\r\n";

        if (!empty($quoteNotes)) {
            $compiledNotes .= $quoteNotes;
        }

        if (!empty($additionalNotes)) {
            $compiledNotes .= ' ' . $additionalNotes;
        }

        if (!empty($batteryNotes)) {
            $compiledNotes .= ' ' . $batteryNotes;
        }

        if (!empty($contractNotes)) {
            $compiledNotes .= ' ' . $contractNotes;
        }

        return $compiledNotes;
    }

    private function getSolarInputFactor(string $sapZoneCode): ?string
    {
        try {
            return Region::firstWhere('ZoneId', $sapZoneCode)
                ->SolarRadiationValue;
        } catch (\Throwable $ex) {
            return null;
        }
    }

    private function buildRepositoryId(string $postCode, string $surname, $customerId)
    {
        return $postCode . '_' .
            Str::of($surname)->upper()->substr(0, 3) . '_' .
            $customerId;
    }
}
<?php

namespace Mralston\Iq\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mralston\Iq\Enums\Iq as Enum;
use Mralston\Iq\Models\Branch;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\EnquirySource;
use Mralston\Iq\Models\SolarIrradianceZone;
use Mralston\Iq\Models\Status;
use Mralston\Iq\Models\Tariff;
use Mralston\Iq\Models\User;

/**
 * NOT powered by MarcuSQL™
 */
class CustomerService
{
    protected ?array $attrs = [];
    protected ?User $rep = null;
    protected ?Company $company = null;
    protected ?Tariff $tariff = null;
    protected ?SolarIrradianceZone $solarIrradianceZone = null;
    protected ?Status $status = null;
    protected ?EnquirySource $enquirySource = null;
    
    public function withAttrs(?array $attrs = []): self
    {
        $this->attrs = $attrs;
        return $this;
    }
    
    public function withBranch(?Branch $branch = null): self
    {
        $this->branch = $branch;
        return $this;
    }
    
    public function withCompany(?Company $company = null): self
    {
        $this->company = $company;
        return $this;
    }
    
    public function withEnquirySource(?EnquirySource $enquirySource = null): self
    {
        $this->enquirySource = $enquirySource;
        return $this;
    }
    
    public function withRep(?User $rep = null): self
    {
        $this->rep = $rep;
        return $this;
    }
    
    public function withSolarIrradianceZone(?SolarIrradianceZone $solarIrradianceZone = null): self
    {
        $this->solarIrradianceZone = $solarIrradianceZone;
        return $this;
    }
    
    public function withStatus(?Status $status = null): self
    {
        $this->status = $status;
        return $this;
    }

    public function withTariff(?Tariff $tariff = null): self
    {
        $this->tariff = $tariff;
        return $this;
    }

    public function create(): Customer
    {
        // TODO: Validate $this->attrs

        return DB::transaction(function () {

            // Create Customer
            $customer = Customer::create([
                'Name' => collect([
                    $this->attrs['title'],
                    $this->attrs['first_name'],
                    $this->attrs['last_name']
                ])->join(' '),
                'Address' => $this->attrs['address'],
                'Postcode' => $this->attrs['post_code'],
                'Phone1' => $this->attrs['phone1'] ?? null,
                'Phone2' => $this->attrs['phone2'] ?? null,
                'Phone3' => $this->attrs['phone3'] ?? null,
                'Salutation' => $this->attrs['salutation'] ?? collect([
                    $this->attrs['title'],
                    $this->attrs['last_name']
                ])->join(' '),
                'Email' => $this->attrs['email'],
                'TypeId' => $this->attrs['type_id'] ?? Enum::DEFAULT_TYPE_ID,
                'InitialDate' => $this->attrs['contract_signed_at'],
                'LastDate' => $this->attrs['last_date'] ?? Carbon::now(),
                'NextDate' => $this->attrs['next_date'] ?? Carbon::now(),
                'StatusID' => optional($this->status)->Id ?? Enum::DEFAULT_STATUS_ID,
                'IndTypeID' => $this->attrs['ind_type_id'] ?? Enum::DEFAULT_INDIVIDUAL_TYPE,
                'CustType' => $this->attrs['customer_type'] ?? Enum::DEFAULT_CUSTOMER_TYPE,
                'EnquirySourceID' => optional($this->enquirySource)->Id ?? Enum::DEFAULT_ENQUIRY_SOURCE,
                'SortName' => $this->attrs['last_name'],
                'BranchID' => optional($this->branch)->Id ?? optional($this->rep)->BranchId,
                'Notes' => $this->buildNotes(
                    $this->attrs['quote_notes'] ?? null,
                    $this->attrs['additional_notes'] ?? null,
                    $this->attrs['battery_notes'] ?? null,
                    $this->attrs['contract_notes'] ?? null
                ),
                'OwnerId' => $this->rep->id,
                'Cat1' => $this->attrs['category1'] ?? Enum::DEFAULT_CATEGORY,
                'Cat2' => $this->attrs['category2'] ?? Enum::DEFAULT_CATEGORY,
                'Cat3' => $this->attrs['category3'] ?? Enum::DEFAULT_CATEGORY,
                'Cat4' => $this->attrs['category4'] ?? Enum::DEFAULT_CATEGORY,
                'Cat5' => $this->attrs['category5'] ?? Enum::DEFAULT_CATEGORY,
                'Companyid' => optional($this->company)->Id,
                'IndustryTypeId' => $this->attrs['industry_type_id'] ?? Enum::DEFAULT_INDUSTRY_TYPE,
                'EnquiryType' => $this->attrs['enquiry_type'] ?? Enum::DEFAULT_ENQUIRY_TYPE,
                'ImportId' => $this->attrs['model_id'],
                'OtherId' => $this->attrs['other_id'] ?? Enum::DEFAULT_INTEGER,
                'LastUser' => config('iq.auto_user'),
                'PricePerKWH' => Enum::DEFAULT_PRICE_PER_KWH,
                'AnnualBill' => $this->attrs['annual_bill'],
                'TariffId' => optional($this->tariff)->Id ?? Enum::DEFAULT_TARIFF_ID,
                'DaylightUsage' => $this->attrs['daylight_usage'] ?? Enum::DEFAULT_DAYLIGHT_USAGE,
                'SolarInputFactor' => optional($this->solarIrradianceZone)->SolarRadiationValue,
                'VATExempt' => $this->attrs['vat_exempt'] ?? Enum::DEFAULT_VAT_EXEMPT,
                'Sold' => $this->attrs['sold'] ?? 0,
                'NoReport' => $this->attrs['no_report'] ?? 0,
                'Immersion' => $this->attrs['immersion'] ?? 0,
                'FundType' => $this->attrs['fund_type'] ?? 0,
                'SmartMeter' => $this->attrs['smart_meter'] ?? 0,
                'GasBill' => $this->attrs['gas_bill'] ?? 0,
                'LecBill' => $this->attrs['electricity_bill'] ?? 0,
                'Addressee' => $this->attrs['salutation'] ?? null,
                'ConnectId' => $this->attrs['connect'] ?? 0,
                'CSI' => $this->attrs['csi'] ?? 0,
                'Sent' => $this->attrs['sent'] ?? 0,
                'WouldRefer' => $this->attrs['would_refer'] ?? 0,
                'TileTypeId' => $this->attrs['tile_type_id'] ?? null, // TODO: Populate value
                'ArchiveId' => 0,
                'ExecutionDate' => $this->attrs['execution_date'] ?? null,
                'ReasonForCancel' => 0,
                'InProgress' => $this->attrs['in_progress'] ?? 0,
                'Remote' => $this->attrs['remote'] ?? 0,
                'RemoteUser' => $this->attrs['remote_user'] ?? 0,
                'TemplateTypeId',
                'DateLastViewed' => Carbon::now(),
                'DateEPC' => '1899-12-30', // TODO: WTF?
                'DateInstall' => $this->attrs['install_date'] ?? null,
                'ContractPrice' => $this->attrs['contract_price'] ?? 0,
                'Wireless' => $this->attrs['wireless'] ?? 1,
                'QuoteId' => $this->attrs['model_id'],
                'quote_updated', // TODO: Populate value
                'ProjectStatus' => $this->attrs['project_status'] ?? 0,
                'DateRebook' => $this->attrs['date_rebook'] ?? '1899-12-30', // TODO: WTF?
                'EVCharger' => $this->attrs['ev_charger'] ?? null, // TODO: Populate value
                'SweepmanId' => $this->attrs['sweep_man_id'] ?? 0,
                'AppointmentId' => $this->attrs['appointment_id'] ?? null,
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


}
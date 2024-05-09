<?php

namespace Mralston\Iq\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mralston\Iq\Enums\Constants;
use Mralston\Iq\Models\Branch;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\CustomerInvoice;
use Mralston\Iq\Models\EnquirySource;
use Mralston\Iq\Models\InstallNote;
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
    protected ?VatRate $vatRate = null;
    protected ?TemplateType $templateType = null;
    
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
    
    public function withVatRate(?VatRate $vatRate = null): self
    {
        $this->vatRate = $vatRate;
        return $this;
    }
    
    public function withTemplateType(?TemplateType $templateType = null): self
    {
        $this->templateType = $templateType;
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
                'TypeId' => $this->attrs['type_id'] ?? Constants::DEFAULT_TYPE_ID,
                'InitialDate' => $this->attrs['contract_signed_at'] ?? Carbon::now(),
                'LastDate' => $this->attrs['last_date'] ?? Carbon::now(),
                'NextDate' => $this->attrs['next_date'] ?? Carbon::now(),
                'StatusID' => optional($this->status)->Id ?? Constants::DEFAULT_CUSTOMER_STATUS_ID,
                'IndTypeID' => $this->attrs['ind_type_id'] ?? Constants::DEFAULT_INDIVIDUAL_TYPE,
                'CustType' => $this->attrs['customer_type'] ?? Constants::DEFAULT_CUSTOMER_TYPE,
                'EnquirySourceID' => optional($this->enquirySource)->Id ?? Constants::DEFAULT_ENQUIRY_SOURCE,
                'SortName' => Str::of($this->attrs['last_name'])->upper()->trim(),
                'BranchID' => optional($this->branch)->Id ?? optional($this->rep)->BranchId,
                'Notes' => $this->buildNotes(
                    $this->attrs['quote_notes'] ?? null,
                    $this->attrs['additional_notes'] ?? null,
                    $this->attrs['battery_notes'] ?? null,
                    $this->attrs['contract_notes'] ?? null
                ),
                'OwnerId' => $this->rep->id,
                'Cat1' => $this->attrs['category1'] ?? Constants::DEFAULT_CATEGORY,
                'Cat2' => $this->attrs['category2'] ?? Constants::DEFAULT_CATEGORY,
                'Cat3' => $this->attrs['category3'] ?? Constants::DEFAULT_CATEGORY,
                'Cat4' => $this->attrs['category4'] ?? Constants::DEFAULT_CATEGORY,
                'Cat5' => $this->attrs['category5'] ?? Constants::DEFAULT_CATEGORY,
                'Companyid' => optional($this->company)->Id,
                'IndustryTypeId' => $this->attrs['industry_type_id'] ?? Constants::DEFAULT_INDUSTRY_TYPE,
                'EnquiryType' => $this->attrs['enquiry_type'] ?? Constants::DEFAULT_ENQUIRY_TYPE,
                'ImportId' => $this->attrs['model_id'],
                'OtherId' => $this->attrs['other_id'] ?? Constants::DEFAULT_INTEGER,
                'LastUser' => config('iq.auto_user'),
                'PricePerKWH' => Constants::DEFAULT_PRICE_PER_KWH,
                'AnnualBill' => $this->attrs['annual_bill'],
                'TariffId' => optional($this->tariff)->Id ?? Constants::DEFAULT_TARIFF_ID,
                'DaylightUsage' => $this->attrs['daylight_usage'] ?? Constants::DEFAULT_DAYLIGHT_USAGE,
                'SolarInputFactor' => optional($this->solarIrradianceZone)->SolarRadiationValue,
                'VATExempt' => $this->attrs['vat_exempt'] ?? Constants::DEFAULT_VAT_EXEMPT,
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


            // Create InstallNote
            InstallNote::create([
                'CustomerId' => $customer->id,
                'UserId' => optional($this->rep)->id,
                'DateNote' => $this->attrs['contract_signed_at'] ?? Carbon::now(),
                'Notes' => 'New Install',
                'StatusId' => 0
            ]);

            // Create CustomerLog
            CustomerLog::create([
                'CustomerId' => $customer->id,
                'Name' => collect([
                    $this->attrs['title'],
                    $this->attrs['first_name'],
                    $this->attrs['last_name']
                ])->join(' '),
                'Postcode' => $this->attrs['post_code'],
                'SortName' => Str::of($this->attrs['last_name'])->upper()->trim()
            ]);
            
            // Increment LastOrderNo on company
            $this->company->update([
                $this->company->LastOrderNo = $this->company->LastOrderNo + 1
            ]);

            // Create Visit
            Visit::create([
                'CustomerId' => $customer->Id,
                'VisitDate' => Carbon::now(),
                'UserId' => config('iq.auto_user'),
                'NoPanels' => $this->attrs['panel_quantity'] ?? null,
                'PanelTypeId' => $this->attrs['panel_type_id'] ?? null,
                'StatusId' => Constants::DEFAULT_VISIT_STATUS_ID,
                'DateSold' => $this->attrs['contract_signed_at'] ?? Carbon::now(),
                'Reference' => 'AU/' . $this->company->LastOrderNo,
                'TileTypeId' => $this->attrs['tile_type_id'] ?? null,
                'Scaffold' => $this->attrs['scaffold_required'] ?? null,
                'ExpiryDate' => Carbon::now()
            ]);
            
            // Increment LastCustInvoice on company
            $this->company->update([
                $this->company->LastCustInvoice = $this->company->LastCustInvoice + 1
            ]);

            // Create CustomerInvoice
            CustomerInvoice::create([
                'CustomerId' => $customer->id,
                'InvDate' => Carbon::now(),
                'UserId' => config('iq.auto_user'),
                'InvoiceNo' => $this->company->LastCustInvoice,
                'VATRateId' => optional($this->vatRate)->id,
                'AmountDue' => $this->attrs['contract_price'] ?? null,
                'OrderId' => null,
                'Description' => $this->attrs['order_description'] ?? null,
                'InvType' => $this->attrs['invoice_type'] ?? null,
                'Commissioned' => 1,
                'ActAmt' => $this->attrs['contract_price'] ?? null
            ]);
            
            // Create ProcessActions for installation of solar / battery / toy products
            ProcessTemplate::where('active', 1)
                ->where('startup', 1)
                ->where('TemplateTypeId', $this->templateType->Id)
                ->get()
                ->each(function ($processTemplate) use ($customer) {
                    ProcessAction::create([
                        'CustomerId' => $customer->id,
                        'ProcessId' => $processTemplatee->Id,
                        'Description' => $processTemplate->Description,
                        'DateDue' => Carbon::today()->addDays($processTemplate->ElapseDays),
                        'DateChecked' => '1899-12-30',
                        'SequenceId' => $this->templateType->ProcessId,
                        'BranchId' => Constants::HEAD_OFFICE_BRANCH_ID,
                        'DecOrProc' => $processTemplate->DecOrProc
                    ]);
                });
                
            // TODO: execute complete_Process on the startup process

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
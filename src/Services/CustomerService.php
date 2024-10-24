<?php

namespace Mralston\Iq\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mralston\Iq\Enums\Constants;
use Mralston\Iq\Exceptions\NoCustomerException;
use Mralston\Iq\Models\Branch;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\CustomerLog;
use Mralston\Iq\Models\CustomerInvoice;
use Mralston\Iq\Models\EnquirySource;
use Mralston\Iq\Models\InstallNote;
use Mralston\Iq\Models\PanelType;
use Mralston\Iq\Models\ProcessTemplate;
use Mralston\Iq\Models\ProcessAction;
use Mralston\Iq\Models\SolarIrradianceZone;
use Mralston\Iq\Models\Status;
use Mralston\Iq\Models\Tariff;
use Mralston\Iq\Models\TemplateType;
use Mralston\Iq\Models\TileType;
use Mralston\Iq\Models\User;
use Mralston\Iq\Models\VatRate;
use Mralston\Iq\Models\Visit;

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
    protected ?PanelType $panelType = null;
    protected ?TileType $tileType = null;
    protected ?array $notes = [];
    
    protected ?Customer $customer = null;
    protected ?Visit $visit = null;
    
    public function withAttrs(?array $attrs = []): self
    {
        // TODO: Validate $attrs
        
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
    
    public function withCustomer(?Customer $customer = null): self
    {
        $this->customer = $customer;
        return $this;
    }
    
    public function withPanelType(?PanelType $panelType = null): self
    {
        $this->panelType = $panelType;
        return $this;
    }

    public function withTileType(?TileType $tileType = null): self
    {
        $this->tileType = $tileType;
        return $this;
    }

    public function withNotes(string $type, ?string $notes = null)
    {
        if (empty($notes)) {
            unset($this->notes[$type]);
            return $this;
        }

        $this->notes[$type] = $notes;
        return $this;
    }

    public function create(): Customer
    {
        return DB::transaction(function () {

            $customer = $this->createCustomer();

            $this->createInstallNote();
            $this->createCustomerLog();
            $this->visit = $this->createVisit();
            $this->createCustomerInvoice();
            $this->createProcessActions();
            // TODO: Create additional ProcessAction for EV charger??? TBC

            return $customer;
        });
    }

    private function buildNotes(): string
    {
        return '***** ' .
            Carbon::now()->format('d/m/Y h:i A') .
            ' (Quote Notes) *****' .
            "\r\n" .
            collect($this->notes)->implode(' ');
    }

    private function createCustomer(): Customer
    {
        $fullName = collect([
            $this->attrs['title'] ?? null,
            $this->attrs['first_name'] ?? null,
            $this->attrs['last_name'] ?? null
        ])
            ->reject(fn ($value) => empty($value))
            ->join(' ');

        $salutation = collect([
            $this->attrs['title'] ?? null,
            $this->attrs['last_name'] ?? null
        ])
            ->reject(fn ($value) => empty($value))
            ->join(' ');

        $now = Carbon::now();

        $customerData = [
            'Name' => $this->attrs['customer_name'] ?? $fullName,
            'Address' => $this->attrs['address'],
            'Postcode' => $this->attrs['post_code'],
            'Phone1' => $this->attrs['phone1'] ?? null,
            'Phone2' => $this->attrs['phone2'] ?? null,
            'Phone3' => $this->attrs['phone3'] ?? null,
            'Salutation' => $this->attrs['salutation'] ?? $salutation,
            'Email' => $this->attrs['email'],
            'TypeId' => $this->attrs['type_id'] ?? Constants::DEFAULT_TYPE_ID,
            'InitialDate' => $this->attrs['contract_signed_at'] ?? $now,
            'LastDate' => $this->attrs['last_date'] ?? $now,
            'NextDate' => $this->attrs['next_date'] ?? $now,
            'StatusID' => $this->status?->Id ?? Constants::DEFAULT_CUSTOMER_STATUS_ID,
            'IndTypeID' => $this->attrs['ind_type_id'] ?? Constants::DEFAULT_INDIVIDUAL_TYPE,
            'CustType' => $this->attrs['customer_type'] ?? Constants::DEFAULT_CUSTOMER_TYPE,
            'EnquirySourceID' => $this->enquirySource?->Id ?? Constants::DEFAULT_ENQUIRY_SOURCE,
            'SortName' => Str::of($this->attrs['last_name'])->upper()->trim(),
            'BranchID' => $this->branch?->Id ?? $this->rep?->BranchId,
            'Notes' => $this->buildNotes(),
            'OwnerId' => $this->rep->Id,
            'Cat1' => $this->attrs['category1'] ?? Constants::DEFAULT_CATEGORY,
            'Cat2' => $this->attrs['category2'] ?? Constants::DEFAULT_CATEGORY,
            'Cat3' => $this->attrs['category3'] ?? Constants::DEFAULT_CATEGORY,
            'Cat4' => $this->attrs['category4'] ?? Constants::DEFAULT_CATEGORY,
            'Cat5' => $this->attrs['category5'] ?? Constants::DEFAULT_CATEGORY,
            'Companyid' => $this->company?->Id,
            'IndustryTypeId' => $this->attrs['industry_type_id'] ?? Constants::DEFAULT_INDUSTRY_TYPE,
            'EnquiryType' => $this->attrs['enquiry_type'] ?? Constants::DEFAULT_ENQUIRY_TYPE,
            'ImportId' => $this->attrs['model_id'],
            'OtherId' => $this->attrs['other_id'] ?? Constants::DEFAULT_INTEGER,
            'LastUser' => config('iq.auto_user'),
            'PricePerKWH' => Constants::DEFAULT_PRICE_PER_KWH,
            'AnnualBill' => $this->attrs['annual_bill'],
            'TariffId' => $this->tariff?->Id ?? Constants::DEFAULT_TARIFF_ID,
            'DaylightUsage' => $this->attrs['daylight_usage'] ?? Constants::DEFAULT_DAYLIGHT_USAGE,
            'SolarInputFactor' => $this->solarIrradianceZone?->SolarRadiationValue,
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
            'TileTypeId' => $this->tileType?->Id,
            'ArchiveId' => 0,
            'ExecutionDate' => $this->attrs['finance_execution_date'] ?? '1899-12-30',
            'ReasonForCancel' => 0,
            'InProgress' => $this->attrs['in_progress'] ?? 0,
            'Remote' => $this->attrs['remote'] ?? 0,
            'RemoteUser' => $this->attrs['remote_user'] ?? 0,
            'TemplateTypeId' => $this->templateType?->Id,
            'DateLastViewed' => $now,
            'DateEPC' => $this->attrs['epc_date'] ?? '1899-12-30',
            'DateInstall' => $this->attrs['install_date'] ?? null,
            'ContractPrice' => $this->attrs['contract_price'] ?? 0,
            'Wireless' => $this->attrs['wireless'] ?? 1,
            'QuoteId' => $this->attrs['model_id'],
            'quote_updated' => $this->attrs['quote_updated'] ?? null,
            'ProjectStatus' => $this->attrs['project_status'] ?? 0,
            'DateRebook' => $this->attrs['rebook_date'] ?? '1899-12-30',
            'EVCharger' => $this->attrs['evcharger_quantity'] ?? 0,
            'SweepmanId' => $this->attrs['sweep_man_id'] ?? 0,
            'AppointmentId' => $this->attrs['appointment_id'] ?? null,
        ];

        return $this->customer = Customer::create($customerData);
    }

    public function createInstallNote(): InstallNote
    {
        if (empty($this->customer)) {
            throw new NoCustomerException();
        }

        return InstallNote::create([
            'CustomerId' => $this->customer->Id,
            'UserId' => $this->rep?->Id,
            'DateNote' => $this->attrs['contract_signed_at'] ?? Carbon::now(),
            'Notes' => 'New Install',
            'StatusId' => 0
        ]);
    }

    public function createCustomerLog(): CustomerLog
    {
        if (empty($this->customer)) {
            throw new NoCustomerException();
        }

        return CustomerLog::create([
            'CustomerId' => $this->customer->Id,
            'Name' => $this->attrs['customer_name'] ?? collect([
                $this->attrs['title'],
                $this->attrs['first_name'],
                $this->attrs['last_name']
            ])->join(' '),
            'Postcode' => $this->attrs['post_code'],
            'SortName' => Str::of($this->attrs['last_name'])->upper()->trim()
        ]);
    }

    public function createVisit(): Visit
    {
        if (empty($this->customer)) {
            throw new NoCustomerException();
        }

        // Increment LastOrderNo on company
        $this->company->update([
            $this->company->LastOrderNo = $this->company->LastOrderNo + 1
        ]);

        // Create Visit
        return Visit::create([
            'CustomerId' => $this->customer->Id,
            'VisitDate' => Carbon::now(),
            'UserId' => config('iq.auto_user'),
            'NoPanels' => $this->attrs['panel_quantity'] ?? null,
            'PanelTypeId' => $this->panelType?->id,
            'StatusId' => Constants::DEFAULT_VISIT_STATUS_ID,
            'VatRateId' => $this->vatRate?->Id,
            'DateSold' => $this->attrs['contract_signed_at'] ?? Carbon::now(),
            'Reference' => 'AU/' . $this->company->LastOrderNo,
            'TileTypeId' => $this->tileType?->Id,
            'Scaffold' => $this->attrs['scaffold_required'] ?? 0, // TODO: Populate
            'ExpiryDate' => Carbon::now()
        ]);
    }

    public function createCustomerInvoice(): CustomerInvoice
    {
        if (empty($this->customer)) {
            throw new NoCustomerException();
        }

        // Increment LastCustInvoice on company
        $this->company->update([
            $this->company->LastCustInvoice = $this->company->LastCustInvoice + 1
        ]);

        // Create CustomerInvoice
        return CustomerInvoice::create([
            'CustomerId' => $this->customer->Id,
            'InvDate' => Carbon::now(),
            'UserId' => config('iq.auto_user'),
            'InvoiceNo' => $this->company->LastCustInvoice,
            'VATRateId' => $this->vatRate?->Id,
            'AmountDue' => $this->attrs['contract_price'] ?? null,
            'OrderId' => $this->visit?->Id, // At Richard Gregory's request
            'Description' => $this->attrs['order_description'] ?? null,
            'InvType' => $this->attrs['invoice_type'] ?? null,
            'Commissioned' => 1,
            'ActAmt' => $this->attrs['contract_price'] ?? null
        ]);
    }

    public function createProcessActions(): Collection
    {
        if (empty($this->customer)) {
            throw new NoCustomerException();
        }

        $processActions = ProcessTemplate::where('Active', true)
            ->where('Startup', 1)
            ->where('TemplateTypeId', $this->templateType->Id)
            ->get()
            ->map(function ($processTemplate) {
                return ProcessAction::create([
                    'CustomerId' => $this->customer->Id,
                    'ProcessId' => $processTemplate->Id,
                    'Description' => $processTemplate->Description,
                    'DateDue' => Carbon::today()->addDays($processTemplate->ElapseDays),
                    'DateChecked' => '1899-12-30',
                    'SequenceId' => $this->templateType->ProcessId,
                    'BranchId' => Constants::HEAD_OFFICE_BRANCH_ID,
                    'DecOrProc' => $processTemplate->DecOrProc
                ]);
            });

        // Fetch the startup process template
        $startupProcessTemplate = ProcessTemplate::where('TemplateTypeId', $this->templateType->Id)
            ->where('Active', true)
            ->where('Startup', 1)
            ->first();

        // Fetch the process action matching that startup process template
        if (!empty($startupProcessTemplate)) {
            $startupProcessAction = $this->customer
                ->processActions()
                ->firstWhere('ProcessId', $startupProcessTemplate->Id);
        }

        // If there was a startup process action for the customer, Execute complete_Process stored procedure
        if (!empty($startupProcessAction)) {
            DB::connection('iq')
                ->statement('EXEC complete_Process ?, ?, ?', [
                    $startupProcessTemplate->Id,
                    $this->customer->Id,
                    config('iq.auto_user')
                ]);
        }

        return $processActions;
    }
}

<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mralston\Iq\Exceptions\NoQuoteException;
use Mralston\Iq\Facades\Customer as CustomerFacade;
use Mralston\Iq\Models\Company;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Models\PanelType;
use Mralston\Iq\Models\TemplateType;
use Mralston\Iq\Models\TileType;
use Mralston\Iq\Models\User;
use Mralston\Iq\Models\VatRate;
use Mralston\Iq\Services\CustomerService;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-07-15 09:30:00', 'UTC'));
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * @return array{company: Company, rep: User, panelType: PanelType, tileType: TileType, vatRate: VatRate, templateType: TemplateType}
 */
function seedIq(): array
{
    $iq = DB::connection('iq');

    $iq->table('tUs')->insert(['Id' => 1, 'LastOrderNo' => 100, 'LastCustInvoice' => 500]);
    $iq->table('tUsers')->insert(['Id' => 7, 'BranchId' => 20, 'Email' => 'w.riker@example.test']);
    $iq->table('tLists')->insert([
        ['Id' => 20, 'Type' => 'BRANCH', 'Description' => 'Starbase 74', 'ProcessId' => null],
        ['Id' => 30, 'Type' => 'PANELTYPE', 'Description' => 'Panel', 'ProcessId' => null],
        ['Id' => 31, 'Type' => 'TILETYPE', 'Description' => 'Tile', 'ProcessId' => null],
        ['Id' => 32, 'Type' => 'VATRATES', 'Description' => '0%', 'ProcessId' => null],
        ['Id' => 33, 'Type' => 'TEMPLATETYPE', 'Description' => 'Solar', 'ProcessId' => 9],
    ]);
    $iq->table('tProcessTemplate')->insert([
        'Id' => 40, 'Active' => true, 'Startup' => 1, 'TemplateTypeId' => 33,
        'Description' => 'Welcome call', 'ElapseDays' => 2, 'DecOrProc' => 1,
    ]);

    return [
        'company' => Company::find(1),
        'rep' => User::find(7),
        'panelType' => PanelType::find(30),
        'tileType' => TileType::find(31),
        'vatRate' => VatRate::find(32),
        'templateType' => TemplateType::find(33),
    ];
}

/**
 * The attributes the portal passes, with a quote id.
 */
function portalAttrs(array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'Mr William Riker',
        'last_name' => 'Riker',
        'address' => '1 Example Road, Alaska',
        'post_code' => 'AK1 1AA',
        'phone2' => '07700900001',
        'email' => 'w.riker@example.test',
        'model_id' => 1701,
        'annual_bill' => 1200,
        'contract_price' => 9999.50,
        'panel_quantity' => 12,
    ], $overrides);
}

function service(array $fixtures): CustomerService
{
    return app(CustomerService::class)
        ->withCompany($fixtures['company'])
        ->withRep($fixtures['rep'])
        ->withPanelType($fixtures['panelType'])
        ->withTileType($fixtures['tileType'])
        ->withVatRate($fixtures['vatRate'])
        ->withTemplateType($fixtures['templateType']);
}

it('writes the customer, install note, log, visit, invoice, process actions and quote', function () {
    $customer = service(seedIq())->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');

    expect($customer->Id)->toBeInt()
        ->and($iq->table('tCustomers')->count())->toBe(1)
        ->and($iq->table('tInstallNotes')->count())->toBe(1)
        ->and($iq->table('tCustLog')->count())->toBe(1)
        ->and($iq->table('tVisits')->count())->toBe(1)
        ->and($iq->table('tCustomerInvoice')->count())->toBe(1)
        ->and($iq->table('tProcessActions')->count())->toBe(1)
        ->and($iq->table('tQuotes')->count())->toBe(1);

    $row = $iq->table('tCustomers')->first();
    expect($row->Name)->toBe('Mr William Riker')
        ->and((int) $row->ImportId)->toBe(1701)
        ->and((int) $row->QuoteId)->toBe(1701)
        ->and((int) $row->BranchID)->toBe(20)
        ->and((int) $row->CanSMS)->toBe(1)
        ->and($row->RepId)->toBe('AK1 1AA_RIK_' . $customer->Id)
        ->and($row->NovaQuoteUuid)->toBeNull();

    $quote = $iq->table('tQuotes')->first();
    expect($quote->CustomerId)->toBe($customer->Id)
        ->and($quote->QuoteId)->toBe(1701);

    $visit = $iq->table('tVisits')->first();
    expect($visit->VatRateId)->toBe(32)
        ->and($visit->NoPanels)->toBe(12);
});

it('takes the order and invoice numbers from the company row', function () {
    $fixtures = seedIq();

    service($fixtures)->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tVisits')->value('Reference'))->toBe('AU/101')
        ->and(DB::connection('iq')->table('tCustomerInvoice')->value('InvoiceNo'))->toBe(501)
        ->and($fixtures['company']->LastOrderNo)->toBe(101)
        ->and($fixtures['company']->isDirty())->toBeFalse();

    service($fixtures)->withAttrs(portalAttrs(['model_id' => 1702]))->create();

    $company = DB::connection('iq')->table('tUs')->first();
    expect($company->LastOrderNo)->toBe(102)
        ->and($company->LastCustInvoice)->toBe(502)
        ->and(DB::connection('iq')->table('tVisits')->orderByDesc('Id')->value('Reference'))->toBe('AU/102');
});

it('reads the order number from the database, not a stale company instance', function () {
    $fixtures = seedIq();
    DB::connection('iq')->table('tUs')->update(['LastOrderNo' => 250]);

    service($fixtures)->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tVisits')->value('Reference'))->toBe('AU/251');
});

it('rolls every write back when create fails part way', function () {
    $fixtures = seedIq();

    expect(fn () => service($fixtures)->withAttrs(portalAttrs(['model_id' => null]))->create())
        ->toThrow(NoQuoteException::class);

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->count())->toBe(0)
        ->and($iq->table('tInstallNotes')->count())->toBe(0)
        ->and($iq->table('tCustLog')->count())->toBe(0)
        ->and($iq->table('tVisits')->count())->toBe(0)
        ->and($iq->table('tCustomerInvoice')->count())->toBe(0)
        ->and($iq->table('tProcessActions')->count())->toBe(0)
        ->and($iq->table('tUs')->value('LastOrderNo'))->toBe(100)
        ->and($iq->table('tUs')->value('LastCustInvoice'))->toBe(500);
});

it('writes the panel type id', function () {
    service(seedIq())->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tVisits')->value('PanelTypeId'))->toBe(30);
});

it('stamps dates in the application timezone when iq.timezone is not set', function () {
    config(['app.timezone' => 'UTC', 'iq.timezone' => null]);

    service(seedIq())->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->value('InitialDate'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tVisits')->value('VisitDate'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tCustomers')->value('Notes'))->toStartWith('***** 15/07/2026 09:30 AM (Quote Notes) *****');
});

it('stamps dates in iq.timezone when it is set', function () {
    config(['app.timezone' => 'UTC', 'iq.timezone' => 'Europe/London']);

    service(seedIq())->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->value('InitialDate'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tCustomers')->value('DateLastViewed'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tInstallNotes')->value('DateNote'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tVisits')->value('VisitDate'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tVisits')->value('DateSold'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tVisits')->value('ExpiryDate'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tCustomerInvoice')->value('InvDate'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tCustomers')->value('Notes'))->toStartWith('***** 15/07/2026 10:30 AM (Quote Notes) *****');
});

it('takes the process action due date from the UK calendar day', function () {
    config(['app.timezone' => 'UTC', 'iq.timezone' => 'Europe/London']);
    Carbon::setTestNow(Carbon::parse('2026-07-15 23:30:00', 'UTC'));

    service(seedIq())->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tProcessActions')->value('DateDue'))->toBe('2026-07-18 00:00:00');
});

it('writes NovaQuoteUuid when it is set and finds the customer by it', function () {
    $uuid = '9f1c2d3e-4b5a-4c6d-8e7f-0a1b2c3d4e5f';

    $customer = service(seedIq())->withNovaQuoteUuid($uuid)->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tCustomers')->value('NovaQuoteUuid'))->toBe($uuid)
        ->and(Customer::findByNovaQuoteUuid($uuid)?->Id)->toBe($customer->Id)
        ->and(Customer::findByNovaQuoteUuid('00000000-0000-0000-0000-000000000000'))->toBeNull();
});

it('never writes NovaQuoteUuid when it is not set, so IQ without the column keeps working', function () {
    $this->dropIqSchema();
    $this->createIqSchema(withNovaQuoteUuid: false);

    $customer = service(seedIq())->withAttrs(portalAttrs())->create();

    expect(DB::connection('iq')->table('tCustomers')->where('Id', $customer->Id)->exists())->toBeTrue();
});

it('creates a Nova sale without a model id, with quote id 0 and no import or quote id on the customer', function () {
    $uuid = '9f1c2d3e-4b5a-4c6d-8e7f-0a1b2c3d4e5f';
    $attrs = portalAttrs();
    unset($attrs['model_id']);

    $customer = service(seedIq())->withNovaQuoteUuid($uuid)->withAttrs($attrs)->create();

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->value('ImportId'))->toBeNull()
        ->and($iq->table('tCustomers')->value('QuoteId'))->toBeNull()
        ->and($iq->table('tQuotes')->where('CustomerId', $customer->Id)->value('QuoteId'))->toBe(0);
});

it('still requires a model id when no Nova quote uuid is set', function () {
    $attrs = portalAttrs();
    unset($attrs['model_id']);

    expect(fn () => service(seedIq())->withAttrs($attrs)->create())->toThrow(NoQuoteException::class)
        ->and(DB::connection('iq')->table('tCustomers')->count())->toBe(0);
});

it('runs under Model::shouldBeStrict()', function () {
    expect(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();

    $fixtures = seedIq();
    $rep = $fixtures['rep']->load('branch');

    $customer = service($fixtures)
        ->withBranch($rep->branch)
        ->withNovaQuoteUuid('9f1c2d3e-4b5a-4c6d-8e7f-0a1b2c3d4e5f')
        ->withNotes('quote', 'Two arrays on the south face')
        ->withAttrs(['first_name' => 'William'] + portalAttrs(['customer_name' => null]))
        ->create();

    expect($customer->Name)->toBe('William Riker')
        ->and(DB::connection('iq')->table('tCustLog')->value('Name'))->toBe(' William Riker')
        ->and((int) DB::connection('iq')->table('tCustomers')->value('BranchID'))->toBe(20);
});

it('clears builder state after create so the next customer starts clean', function () {
    $fixtures = seedIq();
    $service = service($fixtures)->withNovaQuoteUuid('9f1c2d3e-4b5a-4c6d-8e7f-0a1b2c3d4e5f');
    $service->withAttrs(portalAttrs())->create();

    $service->withCompany($fixtures['company'])
        ->withRep($fixtures['rep'])
        ->withTemplateType($fixtures['templateType'])
        ->withAttrs(portalAttrs(['model_id' => 1702]))
        ->create();

    $second = DB::connection('iq')->table('tCustomers')->orderByDesc('Id')->first();
    $secondVisit = DB::connection('iq')->table('tVisits')->orderByDesc('Id')->first();
    expect($second->NovaQuoteUuid)->toBeNull()
        ->and($secondVisit->PanelTypeId)->toBeNull()
        ->and($secondVisit->VatRateId)->toBeNull();
});

it('clears builder state when create fails', function () {
    $fixtures = seedIq();
    $service = service($fixtures);

    expect(fn () => $service->withAttrs(portalAttrs(['model_id' => null]))->create())->toThrow(NoQuoteException::class);

    $service->withCompany($fixtures['company'])
        ->withRep($fixtures['rep'])
        ->withTemplateType($fixtures['templateType'])
        ->withAttrs(portalAttrs())
        ->create();

    expect(DB::connection('iq')->table('tVisits')->value('PanelTypeId'))->toBeNull();
});

it('gives each facade call a fresh service', function () {
    $fixtures = seedIq();

    CustomerFacade::withPanelType($fixtures['panelType']);

    CustomerFacade::withCompany($fixtures['company'])
        ->withRep($fixtures['rep'])
        ->withTemplateType($fixtures['templateType'])
        ->withAttrs(portalAttrs())
        ->create();

    expect(DB::connection('iq')->table('tVisits')->value('PanelTypeId'))->toBeNull();
});

it('writes the same rows without strict mode, as the portal runs it', function () {
    Model::shouldBeStrict(false);

    $fixtures = seedIq();
    $customer = service($fixtures)->withBranch($fixtures['rep']->branch)->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');
    expect((int) $iq->table('tCustomers')->value('BranchID'))->toBe(20)
        ->and($iq->table('tVisits')->value('Reference'))->toBe('AU/101')
        ->and($iq->table('tVisits')->value('PanelTypeId'))->toBe(30)
        ->and($iq->table('tCustomerInvoice')->value('InvoiceNo'))->toBe(501)
        ->and($iq->table('tQuotes')->where('CustomerId', $customer->Id)->value('QuoteId'))->toBe(1701);
});

it('stamps Eloquent timestamps in iq.timezone when it is set', function () {
    config(['app.timezone' => 'UTC', 'iq.timezone' => 'Europe/London']);

    service(seedIq())->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->value('Created'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tCustomers')->value('Updated'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tProcessActions')->value('DateCreated'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tQuotes')->value('CreatedAt'))->toBe('2026-07-15 10:30:00')
        ->and($iq->table('tQuotes')->value('UpdatedAt'))->toBe('2026-07-15 10:30:00');
});

it('stamps Eloquent timestamps in the application timezone when iq.timezone is not set', function () {
    config(['app.timezone' => 'UTC', 'iq.timezone' => null]);

    service(seedIq())->withAttrs(portalAttrs())->create();

    $iq = DB::connection('iq');
    expect($iq->table('tCustomers')->value('Created'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tCustomers')->value('Updated'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tProcessActions')->value('DateCreated'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tQuotes')->value('CreatedAt'))->toBe('2026-07-15 09:30:00')
        ->and($iq->table('tQuotes')->value('UpdatedAt'))->toBe('2026-07-15 09:30:00');
});

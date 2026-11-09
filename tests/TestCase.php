<?php

namespace Mralston\Iq\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mralston\Iq\Providers\IqServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Runs the package against an in-memory SQLite database registered as the iq connection, with
 * only the IQ tables and columns the customer service reads or writes.
 */
abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [IqServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.timezone', 'UTC');
        $app['config']->set('database.default', 'iq');
        $app['config']->set('database.connections.iq', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Model::shouldBeStrict();

        $this->createIqSchema();
    }

    protected function tearDown(): void
    {
        Model::shouldBeStrict(false);

        parent::tearDown();
    }

    /**
     * @param  bool  $withNovaQuoteUuid  whether tCustomers has the NovaQuoteUuid column, which
     *                                   IQ databases only have once it has been added for Nova
     */
    protected function createIqSchema(bool $withNovaQuoteUuid = true): void
    {
        $schema = Schema::connection('iq');

        $schema->create('tUs', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('LastOrderNo')->default(0);
            $table->integer('LastCustInvoice')->default(0);
        });

        $schema->create('tUsers', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('BranchId')->nullable();
            $table->string('Email')->nullable();
            $table->dateTime('Created')->nullable();
            $table->dateTime('Updated')->nullable();
        });

        $schema->create('tLists', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Type');
            $table->string('Description')->nullable();
            $table->integer('ProcessId')->nullable();
            $table->dateTime('Created')->nullable();
            $table->dateTime('Updated')->nullable();
        });

        $schema->create('tRegions', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('ZoneId');
            $table->decimal('SolarRadiationValue')->nullable();
        });

        $schema->create('tCustomers', function (Blueprint $table) use ($withNovaQuoteUuid) {
            $table->increments('Id');
            foreach ([
                'Name', 'Address', 'Postcode', 'Phone1', 'Phone2', 'Phone3', 'Salutation', 'Email',
                'SortName', 'Notes', 'Addressee', 'RepId', 'CustGuid',
            ] as $column) {
                $table->text($column)->nullable();
            }
            foreach ([
                'TypeId', 'StatusID', 'IndTypeID', 'CustType', 'EnquirySourceID', 'BranchID', 'OwnerId',
                'Cat1', 'Cat2', 'Cat3', 'Cat4', 'Cat5', 'Companyid', 'IndustryTypeId', 'EnquiryType',
                'ImportId', 'OtherId', 'LastUser', 'PricePerKWH', 'AnnualBill', 'TariffId',
                'DaylightUsage', 'SolarInputFactor', 'VATExempt', 'Sold', 'NoReport', 'Immersion',
                'FundType', 'SmartMeter', 'GasBill', 'LecBill', 'ConnectId', 'CanSMS', 'CSI', 'Sent',
                'WouldRefer', 'TileTypeId', 'ArchiveId', 'ReasonForCancel', 'InProgress', 'Remote',
                'RemoteUser', 'TemplateTypeId', 'ContractPrice', 'Wireless', 'QuoteId',
                'ProjectStatus', 'EVCharger', 'SweepmanId', 'AppointmentId', 'SvcPackId',
            ] as $column) {
                $table->decimal($column, 12, 2)->nullable();
            }
            foreach ([
                'InitialDate', 'LastDate', 'NextDate', 'ExecutionDate', 'DateLastViewed', 'DateEPC',
                'DateInstall', 'quote_updated', 'DateRebook', 'Created', 'Updated',
            ] as $column) {
                $table->dateTime($column)->nullable();
            }
            if ($withNovaQuoteUuid) {
                $table->string('NovaQuoteUuid', 36)->nullable();
            }
        });

        $schema->create('tInstallNotes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('CustomerId');
            $table->integer('UserId')->nullable();
            $table->dateTime('DateNote')->nullable();
            $table->text('Notes')->nullable();
            $table->integer('StatusId')->nullable();
        });

        $schema->create('tCustLog', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('CustomerId');
            $table->string('Name')->nullable();
            $table->string('Postcode')->nullable();
            $table->string('SortName')->nullable();
        });

        $schema->create('tVisits', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('CustomerId');
            $table->dateTime('VisitDate')->nullable();
            $table->integer('UserId')->nullable();
            $table->integer('NoPanels')->nullable();
            $table->integer('PanelTypeId')->nullable();
            $table->integer('StatusId')->nullable();
            $table->integer('VatRateId')->nullable();
            $table->dateTime('DateSold')->nullable();
            $table->string('Reference')->nullable();
            $table->integer('TileTypeId')->nullable();
            $table->integer('Scaffold')->nullable();
            $table->dateTime('ExpiryDate')->nullable();
        });

        $schema->create('tCustomerInvoice', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('CustomerId');
            $table->dateTime('InvDate')->nullable();
            $table->integer('UserId')->nullable();
            $table->integer('InvoiceNo')->nullable();
            $table->integer('VATRateId')->nullable();
            $table->decimal('AmountDue', 12, 2)->nullable();
            $table->integer('OrderId')->nullable();
            $table->string('Description')->nullable();
            $table->integer('InvType')->nullable();
            $table->integer('Commissioned')->nullable();
            $table->decimal('ActAmt', 12, 2)->nullable();
        });

        $schema->create('tProcessTemplate', function (Blueprint $table) {
            $table->increments('Id');
            $table->boolean('Active');
            $table->integer('Startup');
            $table->integer('TemplateTypeId');
            $table->string('Description');
            $table->integer('ElapseDays');
            $table->integer('DecOrProc');
        });

        $schema->create('tProcessActions', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('CustomerId');
            $table->integer('ProcessId');
            $table->string('Description')->nullable();
            $table->dateTime('DateDue')->nullable();
            $table->dateTime('DateChecked')->nullable();
            $table->integer('SequenceId')->nullable();
            $table->integer('BranchId')->nullable();
            $table->integer('DecOrProc')->nullable();
            $table->dateTime('DateCreated')->nullable();
        });

        $schema->create('tQuotes', function (Blueprint $table) {
            $table->increments('Id');
            $table->integer('CustomerId');
            $table->integer('QuoteId');
            $table->string('MpanNumber')->nullable();
            $table->string('ExportTariff')->nullable();
            $table->string('FeedInTariff')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
        });
    }

    protected function dropIqSchema(): void
    {
        foreach ([
            'tUs', 'tUsers', 'tLists', 'tRegions', 'tCustomers', 'tInstallNotes', 'tCustLog', 'tVisits',
            'tCustomerInvoice', 'tProcessTemplate', 'tProcessActions', 'tQuotes',
        ] as $table) {
            Schema::connection('iq')->drop($table);
        }
    }
}

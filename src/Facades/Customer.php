<?php

namespace Mralston\Iq\Facades;

use Illuminate\Support\Facades\Facade;
use Mralston\Iq\Services\CustomerService;

/**
 * @method static withAttrs(array $attrs): self
 * @method static withRep(User $rep): self
 * @method static withCompany(Company $company): self
 * @method static withTariff(Tariff $tariff): self
 * @method static withSolarIrradianceZone(SolarIrradianceZone $solarIrradianceZone): self
 * @method static withStatus(Status $status): self
 * @method static withEnquirySource(EnquirySource $enquirySource): self
 * @method static withNovaQuoteUuid(?string $uuid): self
 * @method static create(): \Mralston\Iq\Models\Customer
 *
 * @see \Mralston\Iq\Services\CustomerService
 */
class Customer extends Facade
{
    /**
     * Each call through the facade starts from a fresh CustomerService, so builder state set for
     * one customer can never be picked up by the next.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CustomerService::class;
    }
}

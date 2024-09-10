<?php

namespace Mralston\Iq\Observers;

use Illuminate\Support\Str;
use Mralston\Iq\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the Customer "creating" event.
     *
     * @param  \Mralston\Iq\Models\Customer  $customer
     * @return void
     */
    public function creating(Customer $customer)
    {
        $customer->CanSMS = intval($this->canSms(
            $customer->Phone1,
            $customer->Phone2,
            $customer->Phone3
        ));
    }

    /**
     * Handle the Customer "created" event.
     *
     * @param  \Mralston\Iq\Models\Customer  $customer
     * @return void
     */
    public function created(Customer $customer)
    {
        // fresh() is used to prevent
        // "SQLSTATE[HY000]: General error: 20018 Cannot update identity column 'Id'. [20018] (severity 16)"
        $customer->fresh()->update([
            'RepId' => $this->buildRepositoryId($customer->Postcode, $customer->SortName, $customer->Id)
        ]);
    }

    /**
    * Handle the Customer "updating" event.
    *
    * @param  \Mralston\Iq\Models\Customer  $customer
    * @return void
    */
    public function updating(Customer $customer)
    {
        $customer->CanSMS = intval($this->canSms(
            $customer->Phone1,
            $customer->Phone2,
            $customer->Phone3
        ));
    }

    /**
     * Handle the Customer "updated" event.
     *
     * @param  \Mralston\Iq\Models\Customer  $customer
     * @return void
     */
    public function updated(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "deleted" event.
     *
     * @param  \Mralston\Iq\Models\Customer  $customer
     * @return void
     */
    public function deleted(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "forceDeleted" event.
     *
     * @param  \Mralston\Iq\Models\Customer  $customer
     * @return void
     */
    public function forceDeleted(Customer $customer)
    {
        //
    }

    private static function buildRepositoryId(string $postCode, string $surname, $customerId): string
    {
        return $postCode . '_' .
            Str::of($surname)
                ->upper()
                ->replace([' ', "\r", "\n", "\t"], '')
                ->substr(0, 3) . '_' . $customerId;
    }

    private static function canSms(): bool
    {
        foreach (func_get_args() as $phoneNumber) {
            if (Str::of($phoneNumber)->substr(0, 2) == '07') {
                return true;
            }
        }

        return false;
    }
}

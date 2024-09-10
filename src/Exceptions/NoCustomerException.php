<?php

namespace Mralston\Iq\Exceptions\NoCustomerException;

use \Exception;

class NoCustomerException extends Exception
{
    protected string $message = 'Customer record must be created or provided first.';
}

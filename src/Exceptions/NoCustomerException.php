<?php

namespace Mralston\Iq\Exceptions;

use \Exception;

class NoCustomerException extends Exception
{
    protected $message = 'Customer record must be created or provided first.';
}

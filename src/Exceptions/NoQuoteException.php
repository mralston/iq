<?php

namespace Mralston\Iq\Exceptions;

use \Exception;

class NoQuoteException extends Exception
{
    protected $message = 'Quote ID must be provided';
}

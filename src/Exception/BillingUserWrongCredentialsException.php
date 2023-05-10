<?php

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class BillingUserWrongCredentialsException extends NotAcceptableHttpException
{
}

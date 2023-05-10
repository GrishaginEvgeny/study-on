<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Throwable;

class BillingValidationException extends \Exception
{
    public const VALIDATION_ERROR = "Ошибка валидации со стороны биллинга.";

    private $errors;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors = [])
    {
        $this->errors = $errors;
        parent::__construct(self::VALIDATION_ERROR, $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

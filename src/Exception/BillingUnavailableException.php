<?php

namespace App\Exception;

class BillingUnavailableException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey():string
    {
        return 'Сервис временно недоступен. Попробуйте авторизоваться позднее.';
    }
}
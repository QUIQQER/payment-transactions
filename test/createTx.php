<?php

define('SYSTEM_INTERN', true);

require_once 'bootstrap.php';

use QUI\ERP\Accounting\Payments\Transactions;
use QUI\ERP\Currency\Handler as Currencies;

$Currency    = Currencies::getCurrency('EUR');
$Transaction = Transactions\Factory::createPaymentTransaction(100, $Currency);

var_dump($Transaction);
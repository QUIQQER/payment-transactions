<?php

define('SYSTEM_INTERN', true);

require_once 'bootstrap.php';

use QUI\ERP\Accounting\Payments\Transactions;
use QUI\ERP\Currency\Handler as Currencies;

$Currency    = Currencies::getCurrency('EUR');
$Transaction = Transactions\Factory::createPaymentTransaction(100, $Currency);

$Transaction->setData('myKey', 'huhu');
$Transaction->updateData();

$txId = $Transaction->getTxId();

$Transaction = new Transactions\Transaction($txId);

var_dump($Transaction->getData('myKey'));

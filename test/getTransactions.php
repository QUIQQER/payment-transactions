<?php

define('SYSTEM_INTERN', true);

require_once 'bootstrap.php';

$Transactions = \QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
$txs          = $Transactions->getTransactionsByHash('baa5743a-fb70-11e7-80d1-bcaec5e13890');

var_dump($txs);

<?php

/**
 * This file contains package_quiqqer_payment-transactions_ajax_backend_hasRefund
 */

/**
 * Returns if the transaction is refundable
 *
 * @param string $txId
 * @return boolean
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_hasRefund',
    function ($txId) {
        $Transactions = QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
        $Transaction  = $Transactions->get($txId);

        return $Transaction->getPayment()->refundSupport();
    },
    ['txId'],
    'Permission::checkAdminUser'
);

<?php

/**
 * This file contains package_quiqqer_payment-transactions_ajax_backend_refund
 */

/**
 * Returns transaction list for a grid
 *
 * @param string $params - JSON query params
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_refund',
    function ($txId, $refund, $message = '') {
        $Transactions = QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
        $Transaction  = $Transactions->get($txId);

        $Transaction->refund($refund, $message);
    },
    ['txId', 'refund', 'message'],
    'Permission::checkAdminUser'
);

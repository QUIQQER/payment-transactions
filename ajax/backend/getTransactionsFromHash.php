<?php

/**
 * This file contains package_quiqqer_payment-transactions_ajax_backend_getTransactionsFromHash
 */

/**
 * Returns transaction from a specific hash
 *
 * @param string $params - JSON query params
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_getTransactionsFromHash',
    function ($hash) {
        $Handler = QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
        $list    = $Handler->getTransactionsByHash($hash);
        $result  = [];

        foreach ($list as $Transaction) {
            $result[] = $Transaction->getAttributes();
        }

        return $result;
    },
    ['hash'],
    'Permission::checkAdminUser'
);

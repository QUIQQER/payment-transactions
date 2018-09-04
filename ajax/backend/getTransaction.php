<?php

/**
 * This file contains package_quiqqer_payment-transactions_ajax_backend_getTransaction
 */

/**
 * Return the details of a specific transaction
 *
 * @param string $txId - Transaction ID
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_getTransaction',
    function ($txId) {
        $Handler     = QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
        $Transaction = $Handler->get($txId);
        $Currency    = $Transaction->getCurrency();
        $attributes  = $Transaction->getAttributes();

        $attributes['amount_formatted'] = $Currency->format($attributes['amount']);
        $attributes['data']             = json_decode($attributes['data'], true);
        $attributes['payment']          = $Transaction->getPayment()->toArray();

        return $attributes;
    },
    ['txId'],
    'Permission::checkAdminUser'
);

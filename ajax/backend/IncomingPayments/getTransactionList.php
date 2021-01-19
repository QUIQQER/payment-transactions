<?php

/**
 * Add a payment to an invoice
 *
 * @param string|integer $payments - List of payments
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_IncomingPayments_getTransactionList',
    function ($hash) {
        $Handler  = QUI\ERP\Accounting\Payments\Transactions\Handler::getInstance();
        $list     = $Handler->getTransactionsByHash($hash);
        $payments = [];

        foreach ($list as $Transaction) {
            $payments[] = $Transaction->getAttributes();
        }

        $result   = [];
        $Locale   = QUI::getLocale();
        $Currency = QUI\ERP\Defaults::getCurrency();
        $Payments = QUI\ERP\Accounting\Payments\Payments::getInstance();

        foreach ($payments as $payment) {
            $paymentTitle = '';
            $txid         = '';

            try {
                $Payment      = $Payments->getPaymentType($payment['payment']);
                $paymentTitle = $Payment->getTitle();
            } catch (QUI\Exception $Exception) {
            }

            if (isset($payment['txid'])) {
                $txid = $payment['txid'];
            }

            $result[] = [
                'date'    => $Locale->formatDate($payment['date']),
                'amount'  => $Currency->format($payment['amount']),
                'payment' => $paymentTitle,
                'txid'    => $txid
            ];
        }

        return $result;
    },
    ['hash'],
    'Permission::checkAdminUser'
);

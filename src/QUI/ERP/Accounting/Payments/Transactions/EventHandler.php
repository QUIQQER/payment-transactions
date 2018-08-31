<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Transactions\EventHandler
 */

namespace QUI\ERP\Accounting\Payments\Transactions;

use QUI;
use QUI\ERP\Accounting\Invoice\InvoiceTemporary;

/**
 * Class EventHandler
 *
 * @package QUI\ERP\Accounting\Payments\Transactions
 */
class EventHandler
{
    /**
     * @param QUI\ERP\Accounting\Invoice\InvoiceTemporary $Invoice - CreditNote
     */
    public static function onQuiqqerInvoiceTemporaryInvoicePostBegin(InvoiceTemporary $Invoice)
    {
        $refund = $Invoice->getData('refund');

        if (!$refund) {
            return;
        }

        if (!is_array($refund)) {
            return;
        }

        try {
            $Transaction = Handler::getInstance()->get($refund['txid']);
            $Transaction->refund($refund['refund'], $refund['message']);

            $Invoice->addHistory(
                QUI::getLocale()->get('quiqqer/payment-transactions', 'invoice.history.refund', [
                    'amount'   => $refund['refund'],
                    'currency' => $Invoice->getCurrency()->getSign(),
                    'txid'     => ''
                ])
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);

            try {
                $Invoice->addHistory(
                    QUI::getLocale()->get('quiqqer/payment-transactions', 'invoice.history.refund.exception', [
                        'message' => $Exception->getMessage()
                    ])
                );
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeDebugException($Exception);
            }
        }
    }
}

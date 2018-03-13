<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Transactions\Factory
 */

namespace QUI\ERP\Accounting\Payments\Transactions;

use QUI;
use QUI\ERP\Currency\Currency;

/**
 * Class Factory
 *
 * @package QUI\ERP\Accounting\Payments\Transactions
 */
class Factory
{
    /**
     * @return string
     */
    public static function table()
    {
        return QUI::getDBTableName('payment_transactions');
    }

    /**
     * @param int|float $amount
     * @param Currency $Currency - currency
     * @param string|bool $hash - invoice / order hash
     * @param string $payment - name of the Payment
     * @param array $data - variable, optional data
     * @param null $User - user which execute the transaction, or from who the transaction comes from
     * @param bool $date - transaction date
     *
     * @return Transaction
     *
     * @throws QUI\ERP\Accounting\Payments\Transactions\Exception
     */
    public static function createPaymentTransaction(
        $amount,
        Currency $Currency,
        $hash = false,
        $payment = '',
        array $data = [],
        $User = null,
        $date = false
    ) {
        $txId = QUI\Utils\Uuid::get();

        if (empty($hash)) {
            $hash = '';
        }

        if (!QUI::getUsers()->isUser($User)) {
            $User = QUI::getUserBySession();
        }

        // date
        if (!is_numeric($date)) {
            $date = time();
        }

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $date = date('Y-m-d H:i:s', $date);
        $uuid = $User->getId();

        if (empty($uuid)) {
            $uuid = QUI::getUsers()->getSystemUser()->getId();
        }

        QUI::getDataBase()->insert(self::table(), [
            'txid'     => $txId,
            'hash'     => $hash,
            'date'     => $date,
            'uid'      => $uuid,
            'amount'   => $amount,
            'currency' => json_encode($Currency->toArray()),
            'data'     => json_encode($data),
            'payment'  => $payment
        ]);

        $Transaction = Handler::getInstance()->get($txId);

        try {
            QUI::getEvents()->fireEvent('transactionCreate', [$Transaction]);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }

        return $Transaction;
    }
}

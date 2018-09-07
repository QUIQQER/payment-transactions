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
     * Create a new payment transaction
     *
     * @param int|float $amount
     * @param Currency $Currency - currency
     * @param string|bool $hash - invoice / order hash
     * @param string $payment - name of the Payment
     * @param array $data - variable, optional data
     * @param null $User - user which execute the transaction, or from who the transaction comes from
     * @param bool|int|string $date - transaction date, 0000-00-00 || 0000-00-00 00:00:00 || Unix Timestamp
     * @param string|bool $globalProcessId - for the global process hash, if empty, the hash will be used
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
        $date = false,
        $globalProcessId = false
    ) {
        $txId = QUI\Utils\Uuid::get();

        if (empty($hash)) {
            $hash = '';
        }

        if (empty($globalProcessId)) {
            $globalProcessId = $hash;
        }

        if (!QUI::getUsers()->isUser($User)) {
            $User = QUI::getUserBySession();
        }

        // date
        if (QUI\Utils\Security\Orthos::checkMySqlDateSyntax($date) ||
            QUI\Utils\Security\Orthos::checkMySqlDatetimeSyntax($date)) {
            $date = strtotime($date);
        }

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
            'txid'              => $txId,
            'hash'              => $hash,
            'date'              => $date,
            'uid'               => $uuid,
            'amount'            => $amount,
            'currency'          => json_encode($Currency->toArray()),
            'data'              => json_encode($data),
            'payment'           => $payment,
            'global_process_id' => $globalProcessId
        ]);

        $Transaction = Handler::getInstance()->get($txId);

        try {
            QUI::getEvents()->fireEvent('transactionCreate', [$Transaction]);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception->getMessage());
            QUI\System\Log::writeRecursive($Exception->getTraceAsString());
        }

        return $Transaction;
    }

    /**
     * Create a refund transaction
     * A refund transaction use the negative value to the transaction list.
     *
     * @param int|float $amount
     * @param Currency $Currency - currency
     * @param string|bool $hash - invoice / order hash
     * @param string $payment - name of the Payment
     * @param array $data - variable, optional data
     * @param null $User - user which execute the transaction, or from who the transaction comes from
     * @param bool|int|string $date - transaction date, 0000-00-00 || 0000-00-00 00:00:00 || Unix Timestamp
     * @param string|bool $globalProcessId - for the global process hash
     *
     * @return Transaction
     *
     * @throws Exception
     */
    public static function createPaymentRefundTransaction(
        $amount,
        Currency $Currency,
        $hash = false,
        $payment = '',
        array $data = [],
        $User = null,
        $date = false,
        $globalProcessId = false
    ) {
        $amount = $amount * -1; // for the internal system the amount must be the opposite

        return self::createPaymentTransaction(
            $amount,
            $Currency,
            $hash,
            $payment,
            $data,
            $User,
            $date,
            $globalProcessId
        );
    }
}

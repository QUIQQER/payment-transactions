<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Transactions\Handler
 */

namespace QUI\ERP\Accounting\Payments\Transactions;

use QUI;

/**
 * Class Handler
 *
 * @package QUI\ERP\Accounting\Payments\Transactions
 */
class Handler extends QUI\Utils\Singleton
{
    /**
     * This is the default status of an transaction
     */
    const STATUS_DEFAULT = 0;

    /**
     * Transaction is successfully transmitted and completed
     */
    const STATUS_COMPLETE = 1;

    /**
     * Pending status
     * can happen if the transaction has not been completed yet.
     * payment provider still needs to confirm
     */
    const STATUS_PENDING = 2;

    /**
     * Some error occurred
     */
    const STATUS_ERROR = 3;

    /**
     * @var array
     */
    protected $tx = [];

    /**
     * Return a specific Transaction
     *
     * @param string $txId - transaction ID
     * @return Transaction
     *
     * @throws Exception
     */
    public function get($txId)
    {
        if (!isset($this->tx[$txId])) {
            $this->tx[$txId] = new Transaction($txId);
        }

        return $this->tx[$txId];
    }

    /**
     * Return the data from a specific Transaction
     *
     * @param string $txId - transaction ID
     * @return array
     *
     * @throws Exception
     */
    public function getTxData($txId)
    {
        try {
            $result = QUI::getDataBase()->fetch([
                'from'  => Factory::table(),
                'where' => [
                    'txid' => $txId
                ],
                'limit' => 1
            ]);
        } catch (QUI\Database\Exception $Exception) {
            throw new Exception('Transaction not found');
        }

        if (!isset($result[0])) {
            throw new Exception('Transaction not found');
        }

        return $result[0];
    }

    /**
     * Return all transactions from a specific hash
     *
     * @param string $hash
     * @return Transaction[]
     */
    public function getTransactionsByHash($hash)
    {
        try {
            $result = QUI::getDataBase()->fetch([
                'select' => 'txid',
                'from'   => Factory::table(),
                'where'  => [
                    'hash' => $hash
                ]
            ]);
        } catch (QUI\Database\Exception $Exception) {
            return [];
        }

        $transactions = [];

        foreach ($result as $entry) {
            try {
                $transactions[] = $this->get($entry['txid']);
            } catch (Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        return $transactions;
    }

    /**
     * Return all transactions from a specific process
     *
     * @param string $processId
     * @return Transaction[]
     */
    public function getTransactionsByProcessId($processId)
    {
        try {
            $result = QUI::getDataBase()->fetch([
                'select' => 'txid',
                'from'   => Factory::table(),
                'where'  => [
                    'global_process_id' => $processId
                ]
            ]);
        } catch (QUI\Database\Exception $Exception) {
            return [];
        }

        $transactions = [];

        foreach ($result as $entry) {
            try {
                $transactions[] = $this->get($entry['txid']);
            } catch (Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        return $transactions;
    }
}

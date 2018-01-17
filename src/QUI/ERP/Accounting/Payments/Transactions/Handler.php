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
     * @var array
     */
    protected $tx = array();

    /**
     * Return a specific Transaction
     *
     * @param string $txId - transaction ID
     * @return mixed
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
     * @throws Exception
     */
    public function getTxData($txId)
    {
        $result = QUI::getDataBase()->fetch(array(
            'from'  => Factory::table(),
            'where' => array(
                'txid' => $txId
            ),
            'limit' => 1
        ));

        if (!isset($result[0])) {
            throw new Exception('Transaction not found');
        }

        return $result[0];
    }

    /**
     * Retun all transactions from a specific hash
     *
     * @param string $hash
     * @return array
     */
    public function getTransactionsByHash($hash)
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => 'txid',
            'from'   => Factory::table(),
            'where'  => array(
                'hash' => $hash
            )
        ));

        $transactions = array();

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

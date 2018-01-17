<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Transactions\Transaction
 */

namespace QUI\ERP\Accounting\Payments\Transactions;

use QUI;

/**
 * Class Transaction
 * @package QUI\ERP\Accounting\Payments\Transactions
 */
class Transaction extends QUI\QDOM
{
    /**
     * Transaction constructor.
     *
     * @param string $txId - transaction ID
     * @throws Exception
     */
    public function __construct($txId)
    {
        $data = Handler::getInstance()->getTxData($txId);
        $this->setAttributes($data);
    }

    /**
     * Return the order / invoice hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->getAttribute('hash');
    }

    /**
     * @return string
     */
    public function getTxId()
    {
        return $this->getAttribute('txid');
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return floatval($this->getAttribute('amount'));
    }

    /**
     * Return the transaction date
     * - Y-m-d H:i:s
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getAttribute('date');
    }

    /**
     * Return the payment if the transaction has one
     *
     * @return QUI\ERP\Accounting\Payments\Api\AbstractPayment|null
     */
    public function getPayment()
    {
        try {
            return QUI\ERP\Accounting\Payments\Payments::getInstance()->getPaymentType(
                $this->getAttribute('payment')
            );
        } catch (QUI\ERP\Accounting\Payments\Exception $Exception) {
        }

        return null;
    }
}

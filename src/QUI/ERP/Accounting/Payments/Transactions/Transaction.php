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
     * @var null|array
     */
    protected $data = [];

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

        $data = $this->getAttribute('data');

        if ($data) {
            $this->data = json_decode($data, true);
        }

        if (!is_array($this->data)) {
            $this->data = [];
        }
    }

    /**
     * Return the order / invoice hash
     * (global_process_id)
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
     * Return the amount of the transaction in formatted style
     *
     * @return string
     */
    public function getAmountFormatted()
    {
        return $this->getCurrency()->format($this->getAmount());
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

    /**
     * @return QUI\ERP\Currency\Currency
     */
    public function getCurrency()
    {
        $currency = $this->getAttribute('currency');

        try {
            if ($currency) {
                $currency = json_decode($currency, true);

                if (isset($currency['code'])) {
                    return QUI\ERP\Currency\Handler::getCurrency($currency['code']);
                }
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        return QUI\ERP\Defaults::getCurrency();
    }

    /**
     * Return the transaction as text (for presentation)
     *
     * @param null $Locale
     * @return array|string
     */
    public function parseToText($Locale = null)
    {
        if ($Locale === null) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/payment-transactions', 'transaction.to.text', [
            'amount' => $this->getAmountFormatted(),
            'txid'   => $this->getTxId()
        ]);
    }

    /**
     * Execute a refund of this transaction
     *
     * @param float|integer $amount
     * @param string $message
     *
     * @throws Exception
     */
    public function refund($amount, $message = '')
    {
        /* @var $Payment QUI\ERP\Accounting\Payments\Api\AbstractPayment */
        $Payment = $this->getPayment();

        if ($Payment->refundSupport()) {
            throw new Exception([
                'quiqqer/payment-transactions',
                'exception.payment.has.no.refund',
                ['payment' => $Payment->getTitle()]
            ]);
        }

        if (!empty($message)) {
            $message = QUI\Utils\Security\Orthos::clear($message);
        }


        // refund check
        $amount = $this->cleanupAmount($amount);

        if ($amount === null) {
            throw new Exception([
                'quiqqer/payment-transactions',
                'exception.amount.is.null'
            ]);
        }

        $refunded       = $this->getData('refund');
        $refundedAmount = $this->getData('refundAmount');

        if (!$refundedAmount) {
            $refundedAmount = 0;
        }

        QUI\System\Log::writeRecursive($amount);
        QUI\System\Log::writeRecursive($refunded);

        if ($refunded) {
            $originalAmount = $this->getAmount();
            $refundedAmount = floatval($refundedAmount) + floatval($amount);

            if ($originalAmount < $refundedAmount) {
                throw new Exception([
                    'quiqqer/payment-transactions',
                    'exception.refund.to.high'
                ]);
            }
        }

        // execute the refund
        $Payment->refund($this, $amount, $message);
    }

    /**
     * Cleans the amount value
     *
     * @param string|int|float $value
     * @return float|mixed|null
     */
    protected function cleanupAmount($value)
    {
        if (trim($value) === '') {
            return null;
        }

        if (is_float($value)) {
            return round($value, 8);
        }

        $localeCode = QUI::getLocale()->getLocalesByLang(
            QUI::getLocale()->getCurrent()
        );

        $Formatter = new \NumberFormatter($localeCode[0], \NumberFormatter::DECIMAL);

        return $Formatter->parse($value);
    }

    //region data

    /**
     * Set a data entry
     *
     * @param string $key
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Save the data field to the database
     *
     * This method cant change anything related to the transaction data
     * it will save only the extra data
     */
    public function updateData()
    {
        QUI::getDataBase()->update(Factory::table(), [
            'data' => json_encode($this->data)
        ], [
            'txid' => $this->getTxId()
        ]);
    }

    /**
     * Will return a specific extra data entry from the transaction
     *
     * @param string $key
     * @return mixed|null - null =  not found
     */
    public function getData($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    //endregion data
}

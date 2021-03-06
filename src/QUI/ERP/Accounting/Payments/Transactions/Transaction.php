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
            if (!\is_array($data)) {
                $this->data = \json_decode(QUI\Security\Encryption::decrypt($data), true);
            } else {
                $this->data = $data;
            }

            // workaround for old data
            if (!\is_array($this->data)) {
                $this->data = \json_decode($data, true);
            }

            $this->setAttribute('data', $this->data);
        }

        if (!\is_array($this->data)) {
            $this->data = [];
        }

        $this->setAttribute('status', (int)$this->getAttribute('status'));
    }

    /**
     * Return the order / invoice hash to which the transaction applies
     *
     * @return string
     */
    public function getHash()
    {
        return $this->getAttribute('hash');
    }

    /**
     * Return the global process id to which the transaction applies
     * The global process is
     *
     * @return string
     */
    public function getGlobalProcessId()
    {
        return $this->getAttribute('global_process_id');
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
        return \floatval($this->getAttribute('amount'));
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
                $currency = \json_decode($currency, true);

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
     * @param string|bool $hash
     *
     * @throws Exception
     * @throws QUI\Database\Exception
     */
    public function refund($amount, $message = '', $hash = false)
    {
        /* @var $Payment QUI\ERP\Accounting\Payments\Api\AbstractPayment */
        $Payment = $this->getPayment();

        if (!$Payment->refundSupport()) {
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

        $originalAmount = $this->getAmount();

        if (empty($originalAmount) || empty($amount)) {
            throw new Exception([
                'quiqqer/payment-transactions',
                'exception.refund.not_allowed'
            ]);
        }

        if ($refunded) {
            $refundedAmount = \floatval($refundedAmount) + \floatval($amount);

            if ($originalAmount < $refundedAmount) {
                throw new Exception([
                    'quiqqer/payment-transactions',
                    'exception.refund.to.high'
                ]);
            }
        } else {
            if ($originalAmount < \floatval($amount)) {
                throw new Exception([
                    'quiqqer/payment-transactions',
                    'exception.refund.to.high'
                ]);
            }
        }

        // execute the refund
        $Payment->refund($this, $amount, $message, $hash);

        if ($this->getAttribute('status') === Handler::STATUS_DEFAULT) {
            $this->complete();
        }
    }

    /**
     * Cleans the amount value
     *
     * @param string|int|float $value
     * @return float|mixed|null
     */
    protected function cleanupAmount($value)
    {
        if (\trim($value) === '') {
            return null;
        }

        if (\is_float($value)) {
            return \round($value, 8);
        }

        $localeCode = QUI::getLocale()->getLocalesByLang(
            QUI::getLocale()->getCurrent()
        );

        $Formatter = new \NumberFormatter($localeCode[0], \NumberFormatter::DECIMAL);

        return $Formatter->parse($value);
    }

    //region status

    /**
     * Change the transaction status
     *
     * @param int $status - STATUS constants -> Handler::STATUS_*
     *
     * @throws QUI\Database\Exception
     */
    public function changeStatus($status)
    {
        $status = (int)$status;

        switch ($status) {
            case Handler::STATUS_COMPLETE:
                $this->complete();
                break;

            case Handler::STATUS_ERROR:
                $this->error();
                break;

            case Handler::STATUS_PENDING:
                $this->pending();
                break;
        }
    }

    /**
     * Status of the transaction
     * - Handler::STATUS_*
     *
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getAttribute('status');
    }

    /**
     * Change the transaction status to complete
     *
     * @throws QUI\Database\Exception
     */
    public function complete()
    {
        $old = $this->getAttribute('status');

        QUI::getDataBase()->update(Factory::table(), [
            'status' => Handler::STATUS_COMPLETE
        ], [
            'txid' => $this->getTxId()
        ]);

        $this->setAttribute('status', Handler::STATUS_COMPLETE);


        if ($old !== Handler::STATUS_COMPLETE) {
            try {
                QUI::getEvents()->fireEvent('transactionStatusChange', [$this]);
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }
    }

    /**
     * Change the transaction status to pending
     *
     * @throws QUI\Database\Exception
     */
    public function pending()
    {
        $old = $this->getAttribute('status');

        QUI::getDataBase()->update(Factory::table(), [
            'status' => Handler::STATUS_PENDING
        ], [
            'txid' => $this->getTxId()
        ]);

        $this->setAttribute('status', Handler::STATUS_PENDING);


        if ($old !== Handler::STATUS_PENDING) {
            try {
                QUI::getEvents()->fireEvent('transactionStatusChange', [$this]);
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }
    }

    /**
     * Change the transaction status to error
     *
     * @throws QUI\Database\Exception
     */
    public function error()
    {
        $old = $this->getAttribute('status');

        QUI::getDataBase()->update(Factory::table(), [
            'status' => Handler::STATUS_ERROR
        ], [
            'txid' => $this->getTxId()
        ]);

        $this->setAttribute('status', Handler::STATUS_ERROR);


        if ($old !== Handler::STATUS_ERROR) {
            try {
                QUI::getEvents()->fireEvent('transactionStatusChange', [$this]);
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }
    }

    /**
     * Return if the transaction is pending or not
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() === Handler::STATUS_PENDING;
    }

    /**
     * Return if the transaction is complete or not
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->getStatus() === Handler::STATUS_COMPLETE;
    }

    //endregion status

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
        $this->setAttribute('data', $this->data);
    }

    /**
     * Save the data field to the database
     *
     * This method cant change anything related to the transaction data
     * it will save only the extra data
     *
     * @throws QUI\Database\Exception
     */
    public function updateData()
    {
        try {
            $data = QUI\Security\Encryption::encrypt(\json_encode($this->data));
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            $data = \json_encode($this->data);
        }

        QUI::getDataBase()->update(Factory::table(), [
            'data' => $data
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

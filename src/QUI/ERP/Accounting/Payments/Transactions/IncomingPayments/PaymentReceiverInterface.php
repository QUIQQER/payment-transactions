<?php

namespace QUI\ERP\Accounting\Payments\Transactions\IncomingPayments;

use QUI\ERP\Address;
use QUI\ERP\Accounting\Payments\Types\PaymentInterface;
use QUI\ERP\Currency\Currency;
use QUI\Locale;

/**
 * Interface PaymentReceiverInterface
 *
 * Main interface for entities that can receive payments
 */
interface PaymentReceiverInterface
{
    /**
     * Get entity type descriptor
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * Get entity type title
     *
     * @param Locale $Locale (optional) - If omitted use \QUI::getLocale()
     * @return string
     */
    public static function getTypeTitle(Locale $Locale = null): string;

    /**
     * PaymentReceiverInterface constructor.
     * @param string|int $id - Payment receiver entity ID
     */
    public function __construct($id);

    /**
     * Get payment address of of the debtor (e.g. customer)
     *
     * @param string|int $id - Payment entity ID
     * @return Address|false
     */
    public function getDebtorAddress();

    /**
     * Get full document no
     *
     * @return string
     */
    public function getDocumentNo(): string;

    /**
     * Get the unique recipient no. of the debtor (e.g. customer no.)
     *
     * @param string|int $id - Payment entity ID
     * @return string
     */
    public function getDebtorNo(): string;

    /**
     * Get date of the document
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime;

    /**
     * Get entity due date (if applicable)
     *
     * @return \DateTime|false
     */
    public function getDueDate();

    /**
     * @return Currency
     */
    public function getCurrency(): Currency;

    /**
     * Get the total amount of the document
     *
     * @return float
     */
    public function getAmountTotal(): float;

    /**
     * Get the total amount still open of the document
     *
     * @return float
     */
    public function getAmountOpen(): float;

    /**
     * Get the total amount already paid of the document
     *
     * @return float
     */
    public function getAmountPaid(): float;

    /**
     * Get payment method
     *
     * @return PaymentInterface|false
     */
    public function getPaymentMethod();

    /**
     * Get the current payment status of the ERP object
     *
     * @return int - One of \QUI\ERP\Constants::PAYMENT_STATUS_*
     */
    public function getPaymentStatus();
}

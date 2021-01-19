<?php

use QUI\ERP\Accounting\Payments\Transactions\IncomingPayments\Handler;
use QUI\Utils\Security\Orthos;
use QUI\ERP\Accounting\Payments\Transactions\Exception;

/**
 * Get data for new payment transaction
 *
 * @param string|int $entityId - Payment receiver entity ID
 * @param string $entityType - Payment receiver entity type
 * @return array
 *
 * @throws Exception
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_IncomingPayments_getData',
    function ($entityId, $entityType) {
        $entityType = Orthos::clear($entityType);
        $entityId   = Orthos::clear($entityId);

        $Provider = Handler::getPaymentReceiver($entityType, $entityId);

        if (empty($Provider)) {
            QUI\System\Log::addError(
                'No paymentReceiver provider found for entity type: '.$entityType
            );

            throw new Exception([
                'quiqqer/payment-transactions',
                'exception.ajax.backend.IncomingPayments.getData.no_provider'
            ]);
        }

        $Locale   = QUI::getLocale();
        $Currency = $Provider->getCurrency();

        $data = [
            'debtorNo'          => $Provider->getDebtorNo(),
            'addressSalutation' => '',
            'addressName'       => '',
            'addressStreet'     => '',
            'addressCity'       => '',
            'addressCountry'    => '',
            'documentType'      => $Provider::getTypeTitle($Locale),
            'documentNo'        => $Provider->getDocumentNo(),
            'date'              => $Locale->formatDate($Provider->getDate()->getTimestamp()),
            'dueDate'           => $Provider->getDueDate() ?
                $Locale->formatDate($Provider->getDueDate()->getTimestamp()) : '',
            'amountTotal'       => $Currency->format($Provider->getAmountTotal(), $Locale),
            'amountPaid'        => $Currency->format($Provider->getAmountPaid(), $Locale),
            'amountOpen'        => $Currency->format($Provider->getAmountOpen(), $Locale),
            'amountOpenRaw'     => $Provider->getAmountOpen(),
            'paymentId'         => $Provider->getPaymentMethod() ? $Provider->getPaymentMethod()->getId() : false
        ];

        // Address
        $Address = $Provider->getDebtorAddress();

        if ($Address) {
            $data['addressSalutation'] = $Address->getAttribute('salutation') ?: '';
            $data['addressName']       = $Address->getName() ?: '';
            $data['addressStreet']     = $Address->getAttribute('street_no') ?: '';
            $data['addressCity']       = $Address->getAttribute('city') ?: '';
            $data['addressCountry']    = $Address->getCountry() ? $Address->getCountry()->getName($Locale) : '';
        }

        return $data;
    },
    ['entityId', 'entityType'],
    'Permission::checkAdminUser'
);

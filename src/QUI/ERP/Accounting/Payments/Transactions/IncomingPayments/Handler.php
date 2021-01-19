<?php

namespace QUI\ERP\Accounting\Payments\Transactions\IncomingPayments;

use QUI;

/**
 * Class Handler
 *
 * Handles incoming payment transaction provider
 */
class Handler
{
    /**
     * Get ERP payment receiver by entity type
     *
     * @param string $type
     * @param string|int $id - Payment receiver entity ID
     * @return PaymentReceiverInterface|false - PaymentReceiverInterface class or false if not found
     */
    public static function getPaymentReceiver(string $type, $id)
    {
        $ProviderInstance = false;

        /** @var PaymentReceiverInterface $Provider */
        foreach (self::getAllPaymentReceiverProviders() as $Provider) {
            if ($Provider::getType() === $type) {
                $ProviderInstance = new $Provider($id);
                break;
            }
        }

        return $ProviderInstance;
    }

    /**
     * Get all available ERP payment receiver providers
     *
     * @return string[] - Provider classes (static)
     */
    public static function getAllPaymentReceiverProviders(): array
    {
        $packages        = QUI::getPackageManager()->getInstalled();
        $providerClasses = [];

        foreach ($packages as $installedPackage) {
            try {
                $Package = QUI::getPackage($installedPackage['name']);

                if (!$Package->isQuiqqerPackage()) {
                    continue;
                }

                $packageProvider = $Package->getProvider();

                if (empty($packageProvider['paymentReceiver'])) {
                    continue;
                }

                /** @var PaymentReceiverInterface $class */
                foreach ($packageProvider['paymentReceiver'] as $class) {
                    if (!\class_exists($class)) {
                        continue;
                    }

                    $providerClasses[] = $class;
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        return $providerClasses;
    }
}

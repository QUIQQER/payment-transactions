/**
 * @module package/quiqqer/payment-transactions/bin/backend/Transactions
 * @author www.pcsg.de (Henning Leutz)
 *
 * Main Transaction Handler
 */
define('package/quiqqer/payment-transactions/bin/backend/Transactions', [

    'package/quiqqer/payment-transactions/bin/backend/classes/Transactions'
], function (Transactions) {
    "use strict";

    return new Transactions();
});

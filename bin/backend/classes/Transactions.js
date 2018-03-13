/**
 * @module package/quiqqer/payment-transactions/bin/backend/classes/Transactions
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/payment-transactions/bin/backend/classes/Transactions', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QUIDOM, QUIAjax) {
    "use strict";

    return new Class({

        Extends: QUIDOM,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/classes/Transactions',

        /**
         * Return all transactions which are related to the hash
         *
         * @param hash
         */
        getTransactionsByHash: function (hash) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_payment-transactions_ajax_backend_getTransactionsFromHash', resolve, {
                    'package': 'quiqqer/payment-transactions',
                    onError  : reject,
                    hash     : hash
                });
            });
        },


        /**
         * Return details of a specific transaction
         *
         * @param txId - ID of the transaction
         */
        getTransaction: function (txId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_payment-transactions_ajax_backend_getTransaction', resolve, {
                    'package': 'quiqqer/payment-transactions',
                    onError  : reject,
                    txId     : txId
                });
            });
        }
    });
});
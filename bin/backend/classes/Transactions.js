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
        },

        /**
         * Execute a refund
         *
         * @param {String} txid
         * @param {String} refund - amount
         * @param {String} [message] - refund message, message of for the customer
         *
         * @return {Promise}
         */
        refund: function (txid, refund, message) {
            message = message || '';

            if (!refund) {
                return Promise.reject('Refund is empty');
            }

            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_payment-transactions_ajax_backend_refund', resolve, {
                    'package': 'quiqqer/payment-transactions',
                    onError  : reject,
                    txId     : txid,
                    refund   : refund,
                    message  : message
                });
            });
        },

        /**
         * Is the transaction refundable?
         *
         * @param txId
         * @return {Promise}
         */
        hasRefund: function (txId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_payment-transactions_ajax_backend_hasRefund', resolve, {
                    'package': 'quiqqer/payment-transactions',
                    onError  : reject,
                    txId     : txId
                });
            });
        }
    });
});

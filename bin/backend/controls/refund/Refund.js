/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund', [
    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/payment-transactions/bin/backend/Transactions',
    'Locale',
    'Ajax',
    'Mustache',

    'text!package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund.html',

    'css!package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund.css'
], function (QUI, QUIControl, Transactions, QUILocale, QUIAjax, Mustache, templateRefund) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/panels/refund/Refund',

        Binds: [
            '$onInject'
        ],

        options: {
            txid      : false,
            autoRefund: true
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Create the DOMNode Element
         *
         * @return {Element}
         */
        create: function () {
            this.$Elm = this.parent();

            this.$Elm.addClass('quiqqer-payment-transactions-backend-refund');
            this.$Elm.set('html', '');

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            var self = this;

            Transactions.getTransaction(self.getAttribute('txid')).then(function (Transaction) {
                Transaction.currency = JSON.decode(Transaction.currency);
                Transaction.data     = JSON.decode(Transaction.data);

                QUIAjax.get('package_quiqqer_erp_ajax_getProcessInformation', function (process) {
                    self.getElm().set('html', Mustache.render(templateRefund, {
                        Transaction: Transaction,
                        txId       : Transaction.txid,
                        amount     : Transaction.amount,
                        currency   : Transaction.currency.sign
                    }));

                    if (typeof process.invoice === 'undefined') {
                        self.getElm().getElements('.information-invoice').destroy();
                    } else {
                        self.getElm().getElement('.information-invoice-field').set({
                            html: process.invoice.id_prefix + process.invoice.id
                        });
                    }

                    if (typeof process.order === 'undefined') {
                        self.getElm().getElements('.information-order').destroy();
                    } else {
                        self.getElm().getElement('.information-invoice-field').set({
                            html: process.order.id_prefix + process.order.id
                        });
                    }

                    self.fireEvent('load', [self]);
                }, {
                    'package': 'quiqqer/erp',
                    hash     : Transaction.hash
                });

                console.log(Transaction);

            });
        },

        /**
         * Return the values
         *
         * @return {{txid: Array|*, refund: string, message: string}}
         */
        getValues: function () {
            var refund = '', message = '';

            var Refund  = this.getElm().getElement('[name="refund"]'),
                Message = this.getElm().getElement('[name="customer-message"]');

            if (Refund) {
                refund = Refund.value;
            }

            if (Message) {
                message = Message.value;
            }

            return {
                txid   : this.getAttribute('txid').$txId,
                refund : refund,
                message: message
            };
        }
    });
});
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

            Promise.all([
                Transactions.getTransaction(self.getAttribute('txid')),
                Transactions.hasRefund(self.getAttribute('txid'))
            ]).then(function (data) {
                var Transaction = data[0],
                    hasRefund   = data[1];

                Transaction.currency = JSON.decode(Transaction.currency);

                QUIAjax.get('package_quiqqer_erp_ajax_getProcessInformation', function (process) {
                    self.getElm().set('html', Mustache.render(templateRefund, {
                        Transaction: Transaction,
                        txId       : Transaction.txid,
                        amount     : Transaction.amount,
                        currency   : Transaction.currency.sign,
                        payment    : Transaction.payment.title,

                        titleData        : QUILocale.get(lg, 'quiqqer.refund.data'),
                        titlePayment     : QUILocale.get(lg, 'quiqqer.refund.payment'),
                        titleTxId        : QUILocale.get(lg, 'quiqqer.refund.txid'),
                        titleOrigPayment : QUILocale.get(lg, 'quiqqer.refund.original.payment'),
                        titleRefundAmount: QUILocale.get(lg, 'quiqqer.refund.refundAmount'),
                        titleMessage     : QUILocale.get(lg, 'quiqqer.refund.message'),
                        titleProcessData : QUILocale.get(lg, 'quiqqer.refund.processData'),
                        titleInvoice     : QUILocale.get(lg, 'quiqqer.refund.titleInvoice'),
                        titleOrder       : QUILocale.get(lg, 'quiqqer.refund.titleOrder'),
                        titleRefunds     : QUILocale.get(lg, 'quiqqer.refund.titleRefunds')
                    }));

                    if (typeof process.invoice === 'undefined') {
                        self.getElm().getElements('.information-invoice').destroy();
                    } else {
                        var invoiceId = '';

                        if (typeof process.invoice.id_prefix !== 'undefined') {
                            invoiceId = invoiceId + process.invoice.id_prefix;
                        }

                        if (typeof process.invoice.id !== 'undefined') {
                            invoiceId = invoiceId + process.invoice.id;
                        }

                        self.getElm().getElement('.information-invoice-field').set({
                            html  : invoiceId,
                            events: {
                                click: function () {
                                    // open the invoice panel
                                    self.fireEvent('loadBegin');

                                    require(['package/quiqqer/invoice/bin/backend/utils/Panels'], function (Panels) {
                                        Panels.openInvoice(invoiceId).then(function () {
                                            self.fireEvent('loadEnd');
                                            self.fireEvent('openedPanel');
                                        });
                                    });
                                }
                            }
                        });
                    }

                    if (typeof process.order === 'undefined' ||
                        typeof process.order.temporary_invoice_id !== 'undefined') {
                        self.getElm().getElements('.information-order').destroy();
                    } else {
                        console.log(process.order);

                        self.getElm().getElement('.information-order-field').set({
                            'data-temp': 'orderId',
                            html       : process.order.id,
                            events     : {
                                click: function () {
                                    // open the order panel
                                    self.fireEvent('loadBegin');
                                }
                            }
                        });
                    }

                    var Amount = self.getElm().getElement('[name="refund"]');

                    Amount.focus();
                    Amount.value = '';                 // workaround for, put cursor to the end
                    Amount.value = Transaction.amount; // workaround for, put cursor to the end

                    if (!hasRefund) {
                        Amount.value    = '---';
                        Amount.disabled = true;

                        self.getElm().getElement('textarea').disabled = true;
                    }

                    // transactions
                    if (typeof process.transactions !== 'undefined' && process.transactions.length > 1) {
                        var Refunds = self.getElm().getElement('.refunds');
                        var List    = new Element('ul');

                        var i, len, p, currency;

                        for (i = 0, len = process.transactions.length; i < len; i++) {
                            p        = process.transactions[i];
                            currency = JSON.decode(p.currency);

                            new Element('li', {
                                html: p.txid + ' - ' + p.amount + ' ' + currency.sign
                            }).inject(List);
                        }

                        List.inject(Refunds.getElement('.information-refunds'));
                    } else {
                        self.getElm().getElement('.refunds').setStyles({
                            display: 'none'
                        });
                    }

                    self.fireEvent('load', [self]);
                }, {
                    'package': 'quiqqer/erp',
                    hash     : Transaction.hash
                });
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
                txid   : this.getAttribute('txid'),
                refund : refund,
                message: message
            };
        },

        /**
         * submit the refund
         *
         * @return {Promise}
         */
        submit: function () {
            var values = this.getValues();

            return Transactions.refund(
                values.txid,
                values.refund,
                values.message
            );
        }
    });
});
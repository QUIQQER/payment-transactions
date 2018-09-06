/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/refund/Window
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/refund/Window', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'Locale',
    'package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund',
    'package/quiqqer/payment-transactions/bin/backend/Transactions'

], function (QUI, QUIConfirm, QUILocale, Refund, Transactions) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/panels/refund/Window',

        Binds: [
            '$onOpen'
        ],

        options: {
            maxHeight: 800,
            maxWidth : 600,
            txid     : false
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onOpen: this.$onOpen
            });

            this.setAttributes({
                icon     : 'fa fa-money',
                title    : '',
                ok_button: {
                    text     : QUILocale.get(lg, 'quiqqer.refund.submit'),
                    textimage: 'fa fa-money'
                }
            });

            this.$Refund = null;
        },

        /**
         * event: on open
         */
        $onOpen: function () {
            var self = this;

            this.Loader.show();
            this.getContent().set('html', '');
            this.getButton('submit').disable();

            this.setAttribute('title', QUILocale.get(lg, 'quiqqer.refund.window.title', {
                txid: self.getAttribute('txid')
            }));

            this.refresh();

            this.$Refund = new Refund({
                txid  : this.getAttribute('txid'),
                events: {
                    onLoad: function () {
                        Transactions.hasRefund(self.getAttribute('txid')).then(function (hasRefund) {
                            if (hasRefund) {
                                self.getButton('submit').enable();
                            } else {
                                new Element('div', {
                                    'class': 'messages-message message-attention animated flash',
                                    html   : QUILocale.get(lg, 'message.transaction.not.refundable'),
                                    styles : {
                                        marginBottom: 20
                                    }
                                }).inject(self.getContent(), 'top');
                            }

                            self.Loader.hide();
                        });
                    },

                    onOpenTransactionList: function () {
                        self.getButton('submit').disable();
                    },

                    onOpenRefund: function () {
                        self.getButton('submit').enable();
                    },

                    loadBegin: function () {
                        self.Loader.show();
                    },

                    loadEnd: function () {
                        self.Loader.hide();
                    }
                }
            }).inject(this.getContent());
        },

        /**
         *
         * @return {*|Array|{txid: *, txid: *, refund: *, message: *}}
         */
        getValues: function () {
            return this.$Refund.getValues();
        },

        /**
         * Submit the window
         */
        submit: function () {
            var self = this;

            this.Loader.show();

            this.$Refund.submit().then(function () {
                self.fireEvent('submit', [self]);
                self.close();
            }).catch(function (err) {
                self.Loader.hide();

                if (typeof err.getMessage !== 'undefined') {
                    self.getContent().getElements('.message-error').destroy();

                    new Element('div', {
                        'class': 'messages-message message-error animated flash',
                        html   : err.getMessage(),
                        styles : {
                            marginBottom: 20
                        }
                    }).inject(self.getContent(), 'top');
                }
            });
        }
    });
});

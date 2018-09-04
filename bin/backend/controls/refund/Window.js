/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/refund/Window
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/refund/Window', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'Locale',
    'package/quiqqer/payment-transactions/bin/backend/controls/refund/Refund'

], function (QUI, QUIConfirm, QUILocale, Refund) {
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
                icon : 'fa fa-money',
                title: ''
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

            this.setAttribute('title', QUILocale.get(lg, 'quiqqer.refund.window.title', {
                txid: self.getAttribute('txid')
            }));

            this.refresh();

            this.$Refund = new Refund({
                txid  : this.getAttribute('txid'),
                events: {
                    onLoad: function () {
                        self.Loader.hide();
                    },

                    onOpenTransactionList: function () {
                        self.getButton('submit').disable();
                    },

                    onOpenRefund: function () {
                        self.getButton('submit').enable();
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
                self.Loader.show();
                self.close();
            }).catch(function () {
                self.Loader.hide();
            });
        }
    });
});

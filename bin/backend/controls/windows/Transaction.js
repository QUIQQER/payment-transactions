/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction', [

    'qui/QUI',
    'qui/controls/windows/Popup',
    'package/quiqqer/payment-transactions/bin/backend/Transactions',
    'Mustache',
    'Locale',

    'text!package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction.html',
    'css!package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction.css'

], function (QUI, QUIPopup, Transactions, Mustache, QUILocale, template) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIPopup,
        Type   : '',

        Binds: [
            '$onOpen'
        ],

        options: {
            maxHeight: 600,
            maxWidth : 600,
            txid     : false,
            buttons  : false
        },

        /**
         * constructor
         *
         * @param {Options} options
         */
        initialize: function (options) {
            this.parent(options);

            this.setAttributes({
                icon : 'fa fa-money',
                title: ''
            });

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * event: on open
         */
        $onOpen: function () {
            var self    = this,
                Content = this.getContent();

            Content.set('html', '');
            Content.addClass('quiqqer-payment-transactions-window-transaction');

            this.Loader.show();

            Transactions.getTransaction(this.getAttribute('txid')).then(function (data) {
                data.locale_title_general = QUILocale.get(lg, 'window.transaction.txid.title');
                data.locale_title_tx      = QUILocale.get(lg, 'window.transaction.tx.title');
                data.locale_title_payment = QUILocale.get(lg, 'window.transaction.payment.title');

                data.locale_txid   = QUILocale.get(lg, 'txid');
                data.locale_hash   = QUILocale.get(lg, 'hash');
                data.locale_date   = QUILocale.get('quiqqer/system', 'date');
                data.locale_amount = QUILocale.get(lg, 'window.transaction.amount');

                data.locale_payment_id    = QUILocale.get('quiqqer/system', 'id');
                data.locale_payment_title = QUILocale.get('quiqqer/system', 'title');

                Content.set({
                    html: Mustache.render(template, data)
                });

                self.setAttribute('title', data.txid);
                self.refresh();
                self.Loader.hide();
            }).catch(function (err) {
                console.error(err);
                self.close();
            });
        }
    });
});
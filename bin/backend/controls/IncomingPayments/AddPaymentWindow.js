/**
 * Add a payment to an ERP entity (e.g. invoice, offer...)
 *
 * @module package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPaymentWindow
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Patrick Müller)
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPaymentWindow', [

    'qui/controls/windows/Confirm',
    'package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment',
    'Locale'

], function (QUIConfirm, AddPayment, QUILocale) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment',

        Binds: [
            'submit'
        ],

        options: {
            entityId  : false,
            entityType: false
        },

        initialize: function (options) {
            this.setAttributes({
                icon     : 'fa fa-money',
                title    : QUILocale.get(lg, 'controls.AddPaymentWindow.title'),
                maxHeight: 750,
                maxWidth : 800,
                ok_button: {
                    text     : QUILocale.get(lg, 'controls.AddPaymentWindow.btn.confirm'),
                    textimage: 'fa fa-check'
                }
            });

            this.parent(options);

            this.$AddPayment = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * event: on open
         */
        $onOpen: function () {
            this.Loader.show();
            this.getContent().set('html', '');

            this.$AddPayment = new AddPayment({
                entityId  : this.getAttribute('entityId'),
                entityType: this.getAttribute('entityType'),
                events    : {
                    onLoad: function () {
                        this.Loader.hide();
                        this.$AddPayment.focus();
                    }.bind(this),

                    onSubmit: this.submit
                }
            }).inject(this.getContent());
        },

        /**
         * Submit the window
         *
         * @return {Promise}
         */
        submit: function () {
            return new Promise(function (resolve) {
                var values = this.$AddPayment.getValue();

                if (values.amount === '') {
                    return;
                }

                if (values.payment_method === '') {
                    return;
                }

                this.fireEvent('submit', [this, values]);
                resolve(values);

                this.close();
            }.bind(this));
        }
    });
});

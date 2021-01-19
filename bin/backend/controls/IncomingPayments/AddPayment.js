/**
 * Add a payment to an ERP entity (e.g. invoice, offer...)
 *
 * @module package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Patrick Müller)
 *
 * @event onLoad [self]
 * @event onSubmit [TransactionData, self]
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment', [

    'qui/controls/Control',
    'qui/controls/loader/Loader',

    'qui/utils/Form',

    'Mustache',
    'Locale',
    'Ajax',

    'package/quiqqer/payments/bin/backend/Payments',

    'text!package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment.html',
    'css!package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment.css'

], function (QUIControl, QUILoader, QUIFormUtils, Mustache, QUILocale, QUIAjax, Payments, template) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPayment',

        Binds: [
            '$onInject',
            '$getData'
        ],

        options: {
            entityId  : false,
            entityType: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Form  = null;
            this.Loader = new QUILoader();

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Create the DomNode Element
         *
         * @return {Element|null}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'quiqqer-payment-transactions-add'
            });

            this.Loader.inject(this.$Elm);

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            Promise.all([
                Payments.getPayments(),
                this.$getData()
            ]).then(function (result) {
                var title, payment;

                var payments = result[0],
                    Data     = result[1],
                    current  = QUILocale.getCurrent();

                // Build content
                this.$Elm.set('html', Mustache.render(template, Object.merge({}, Data, {
                    labelDebtorNo         : QUILocale.get(lg, 'controls.AddPayment.tpl.labelDebtorNo'),
                    labelAddressName      : QUILocale.get('quiqqer/quiqqer', 'name'),
                    labelAddressStreet    : QUILocale.get(lg, 'street'),
                    labelAddressCity      : QUILocale.get(lg, 'city'),
                    labelAddressCountry   : QUILocale.get(lg, 'country'),
                    labelAddressSalutation: QUILocale.get(lg, 'salutation'),
                    labelDocumentType     : QUILocale.get(lg, 'controls.AddPayment.tpl.labelDocumentType'),
                    labelDocumentNo       : QUILocale.get(lg, 'controls.AddPayment.tpl.labelDocumentNo'),
                    labelDate             : QUILocale.get(lg, 'controls.AddPayment.tpl.labelDate'),
                    labelDueDate          : QUILocale.get(lg, 'controls.AddPayment.tpl.labelDueDate'),
                    labelAmountTotal      : QUILocale.get(lg, 'controls.AddPayment.tpl.labelAmountTotal'),
                    labelAmountPaid       : QUILocale.get(lg, 'controls.AddPayment.tpl.labelAmountPaid'),
                    labelAmountOpen       : QUILocale.get(lg, 'controls.AddPayment.tpl.labelAmountOpen'),
                    labelPayment          : QUILocale.get(lg, 'controls.AddPayment.tpl.labelPayment'),
                    labelPaymentDate      : QUILocale.get(lg, 'controls.AddPayment.tpl.labelPaymentDate'),
                    labelTransactionAmount: QUILocale.get(lg, 'controls.AddPayment.tpl.labelTransactionAmount'),
                    headerDebtor          : QUILocale.get(lg, 'controls.AddPayment.tpl.headerDebtor'),
                    headerDocument        : QUILocale.get(lg, 'controls.AddPayment.tpl.headerDocument'),
                    headerPayment         : QUILocale.get(lg, 'controls.AddPayment.tpl.headerPayment')
                })));

                this.$Form = this.$Elm.getElement('form');

                this.$Form.addEvent('submit', function (event) {
                    event.stop();
                    this.fireEvent('submit', [this.getValue(), this]);
                }.bind(this));

                var Payments = this.getElm().getElement('[name="payment_method"]');
                var Amount   = this.getElm().getElement('[name="amount"]');

                this.getElm().getElement('[name="date"]').valueAsDate = new Date();

                for (var i = 0, len = payments.length; i < len; i++) {
                    payment = payments[i];
                    title   = payment.title;

                    if (typeOf(payment.title) === 'object' && current in payment.title) {
                        title = payment.title[current];
                    }

                    if (typeOf(payment.workingTitle) === 'object' &&
                        current in payment.workingTitle &&
                        payment.workingTitle[current] !== ''
                    ) {
                        title = payment.workingTitle[current];
                    }

                    new Element('option', {
                        html : title,
                        value: parseInt(payment.id)
                    }).inject(Payments);
                }

                if (Data.paymentId) {
                    Payments.value = Data.paymentId;
                }

                if (Data.amountOpenRaw) {
                    Amount.value = Data.amountOpenRaw;
                }

                this.fireEvent('load', [this]);
            }.bind(this));
        },

        /**
         * Return the form data
         *
         * @return {Object}
         */
        getValue: function () {
            return QUIFormUtils.getFormData(this.$Form);
        },

        /**
         * Focus the amount field
         */
        focus: function () {
            this.getElm().getElement('[name="amount"]').focus();
        },

        /**
         * Get details for transactions
         *
         * @return {Promise}
         */
        $getData: function () {
            var self = this;

            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_payment-transactions_ajax_backend_IncomingPayments_getData', resolve, {
                    'package' : 'quiqqer/payment-transactions',
                    entityId  : self.getAttribute('entityId'),
                    entityType: self.getAttribute('entityType'),
                    onError   : reject
                });
            });
        }
    });
});
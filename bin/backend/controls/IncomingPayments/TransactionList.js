/**
 * Lists all payment transactions for a specific global process hash
 *
 * @modue package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/TransactionList
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Patrick)
 *
 * @event onLoad [self]
 * @event onAddTransaction [TransactionData, self]
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/TransactionList', [

    'qui/controls/Control',
    'controls/grid/Grid',
    'Locale',
    'Ajax'

], function (QUIControl, Grid, QUILocale, QUIAjax) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/TransactionList',
        Extends: QUIControl,

        Binds: [
            '$onInject',
            'openAddPaymentDialog'
        ],

        options: {
            hash      : false,
            entityType: false,
            Panel     : false,
            disabled  : false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Resize the control
         */
        resize: function () {
            this.parent();

            if (!this.$Elm) {
                return;
            }

            this.$Grid.setHeight(this.$Elm.getSize().y);
        },

        /**
         * Refresh the data and the display
         *
         * @return {Promise}
         */
        refresh: function () {
            var self = this;

            return this.$getList().then(function (result) {
                self.$Grid.setData({
                    data: result
                });

                self.fireEvent('load', [self]);
            }).then(function () {
                var AddButton = self.$Grid.getButtons().filter(function (Button) {
                    return Button.getAttribute('name') === 'add';
                })[0];

                if (!self.getAttribute('disabled')) {
                    AddButton.enable();
                } else {
                    AddButton.disable();
                }
            });
        },

        /**
         * Creates the DomNode Element
         *
         * @return {Element}
         */
        create: function () {
            var self = this;

            this.$Elm = this.parent();

            this.$Elm.setStyles({
                height: '100%'
            });

            var Container = new Element('div', {
                styles: {
                    height: '100%'
                }
            }).inject(this.$Elm);

            this.$Grid = new Grid(Container, {
                buttons    : [{
                    name     : 'add',
                    text     : QUILocale.get(lg, 'controls.TransactionList.btn.postPayment'),
                    textimage: 'fa fa-money',
                    disabled : true,
                    events   : {
                        onClick: this.openAddPaymentDialog
                    }
                }],
                columnModel: [{
                    header   : QUILocale.get(lg, 'controls.TransactionList.date'),
                    dataIndex: 'date',
                    dataType : 'date',
                    width    : 160
                }, {
                    header   : QUILocale.get(lg, 'controls.TransactionList.amount'),
                    dataIndex: 'amount',
                    dataType : 'string',
                    className: 'journal-grid-amount',
                    width    : 160
                }, {
                    header   : QUILocale.get(lg, 'controls.TransactionList.paymentMethod'),
                    dataIndex: 'payment',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'controls.TransactionList.txid'),
                    dataIndex: 'txid',
                    dataType : 'string',
                    width    : 225
                }]
            });

            this.$Grid.addEvents({
                onDblClick: function () {
                    self.$openTransactionId(
                        self.$Grid.getSelectedData()[0].txid
                    );
                }
            });

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            this.resize();
            this.refresh();
        },

        /**
         * Opens the add payment dialog
         */
        openAddPaymentDialog: function () {
            var self = this;

            var Button = this.$Grid.getButtons().filter(function (Button) {
                return Button.getAttribute('name') === 'add';
            })[0];

            Button.setAttribute('textimage', 'fa fa-spinner fa-spin');

            require([
                'package/quiqqer/payment-transactions/bin/backend/controls/IncomingPayments/AddPaymentWindow'
            ], function (AddPaymentWindow) {
                new AddPaymentWindow({
                    entityId  : self.getAttribute('hash'),
                    entityType: self.getAttribute('entityType'),
                    events    : {
                        onSubmit: function (Win, data) {
                            self.fireEvent('addTransaction', [data, self]);
                        },
                        onClose : function () {
                            Button.setAttribute('textimage', 'fa fa-money');
                        }
                    }
                }).open();
            });
        },

        /**
         * opens a transaction window
         *
         * @param {String} txid - Transaction ID
         */
        $openTransactionId: function (txid) {
            var self = this;

            if (this.getAttribute('Panel')) {
                this.getAttribute('Panel').Loader.show();
            }

            require([
                'package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction'
            ], function (Window) {
                if (self.getAttribute('Panel')) {
                    self.getAttribute('Panel').Loader.hide();
                }

                new Window({
                    txid: txid
                }).open();
            });
        },

        /**
         * Fetch transactions
         *
         * @return {Promise}
         */
        $getList: function () {
            var self = this;

            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_payment-transactions_ajax_backend_IncomingPayments_getTransactionList', resolve, {
                    'package': 'quiqqer/payment-transactions',
                    hash     : self.getAttribute('hash'),
                    onError  : reject
                });
            });
        }
    });
});
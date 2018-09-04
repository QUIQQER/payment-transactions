/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/Panel
 *
 * List all transactions in a grid
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/Panel', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Button',
    'controls/grid/Grid',
    'Ajax',
    'Locale',

    'css!package/quiqqer/payment-transactions/bin/backend/controls/Panel.css'

], function (QUI, QUIPanel, QUIButton, Grid, QUIAjax, QUILocale) {
    "use strict";

    var lg = 'quiqqer/payment-transactions';

    return new Class({

        Extends: QUIPanel,
        Type   : 'package/quiqqer/payment-transactions/bin/backend/controls/Panel',

        Binds: [
            'refresh',
            'openAdd',
            'openRemove',
            '$onCreate',
            '$onInject',
            'openRefund'
        ],

        options: {
            title: QUILocale.get(lg, 'menu.erp.payment-transactions.title'),
            icon : 'fa fa-money'
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid         = null;
            this.$ButtonRefund = null;

            this.addEvents({
                onCreate: this.$onCreate,
                onInject: this.$onInject,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {
            var self = this;

            // Buttons
            this.addButton({
                name     : 'add',
                text     : QUILocale.get('quiqqer/quiqqer', 'add'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.openAdd
                }
            });

            this.addButton({
                type: 'separator'
            });

            this.addButton({
                name     : 'remove',
                text     : 'Korrektur', // #locale
                textimage: 'fa fa-trash-o',
                disabled : true,
                events   : {
                    onClick: this.openRemove
                }
            });

            var Actions = new QUIButton({
                name      : 'actions',
                text      : QUILocale.get(lg, 'btn.actions'),
                menuCorner: 'topRight',
                styles    : {
                    'float': 'right'
                }
            });

            Actions.appendChild({
                name    : 'refund',
                icon    : 'fa fa-money',
                text    : QUILocale.get(lg, 'btn.refund'),
                disabled: true,
                events  : {
                    onClick: function () {
                        self.openRefund(
                            self.$Grid.getSelectedData()[0].txid
                        );
                    }
                }
            });

            this.addButton(Actions);

            this.$ButtonRefund = Actions.getChildren('refund')[0];

            // Grid
            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                pagination : true,
                columnModel: [{
                    header   : QUILocale.get('quiqqer/system', 'date'),
                    dataIndex: 'date',
                    dataType : 'string',
                    width    : 140
                }, {
                    header   : QUILocale.get(lg, 'txid'),
                    dataIndex: 'txid',
                    dataType : 'string',
                    width    : 260,
                    className: 'monospace'
                }, {
                    header   : QUILocale.get(lg, 'grid.amount'),
                    dataIndex: 'amount',
                    dataType : 'numeric',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'grid.currency'),
                    dataIndex: 'currency_code',
                    dataType : 'string',
                    width    : 60
                }, {
                    header   : QUILocale.get('quiqqer/erp', 'global_process_id'),
                    dataIndex: 'hash',
                    dataType : 'string',
                    width    : 260,
                    className: 'monospace'
                }, {
                    header   : QUILocale.get('quiqqer/system', 'user_id'),
                    dataIndex: 'uid',
                    dataType : 'numeric',
                    width    : 100
                }, {
                    header   : QUILocale.get('quiqqer/system', 'username'),
                    dataIndex: 'username',
                    dataType : 'string',
                    width    : 100
                }, {
                    header   : QUILocale.get('quiqqer/system', 'name'),
                    dataIndex: 'user_name',
                    dataType : 'string',
                    width    : 140
                }, {
                    header   : QUILocale.get(lg, 'grid.payment'),
                    dataIndex: 'payment',
                    dataType : 'string',
                    width    : 300,
                    className: 'monospace'
                }, {
                    dataIndex: 'hash',
                    dataType : 'string',
                    hidden   : true
                }],
                onrefresh  : this.refresh
            });

            this.$Grid.addEvents({
                onClick   : function () {
                    self.$ButtonRefund.enable();
                },
                onDblClick: function () {
                    self.openTransaction(
                        self.$Grid.getSelectedData()[0].txid
                    );
                }
            });
        },

        /**
         * Refresh
         */
        $onInject: function () {
            this.refresh();
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            if (!this.$Grid) {
                return;
            }

            var Body = this.getContent();

            if (!Body) {
                return;
            }

            var size = Body.getSize();

            this.$Grid.setHeight(size.y - 40);
            this.$Grid.setWidth(size.x - 40);
        },

        /**
         * refresh the data
         *
         * @return {Promise|*}
         */
        refresh: function () {
            var self = this;

            self.$ButtonRefund.disable();

            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_payment-transactions_ajax_backend_list', function (result) {
                    self.$Grid.setData(result.grid);
                    resolve();
                }, {
                    'package': 'quiqqer/payment-transactions',
                    params   : JSON.encode({
                        perPage: self.$Grid.options.perPage,
                        page   : self.$Grid.options.page
                    }),
                    onError  : reject
                });
            });
        },

        /**
         * Open the add dialog
         *
         * @param {String} txid
         */
        openTransaction: function (txid) {
            require([
                'package/quiqqer/payment-transactions/bin/backend/controls/windows/Transaction'
            ], function (Transaction) {
                new Transaction({
                    txid: txid
                }).open();
            });
        },

        /**
         * Open the add dialog
         */
        openAdd: function () {

        },

        /**
         * Open the remove dialog
         */
        openRemove: function () {

        },

        /**
         * Open the refund dialog
         *
         * @param {String} txid
         */
        openRefund: function (txid) {
            require([
                'package/quiqqer/payment-transactions/bin/backend/controls/refund/Window'
            ], function (RefundWindow) {
                new RefundWindow({
                    txid: txid
                }).open();
            });
        }
    });
});

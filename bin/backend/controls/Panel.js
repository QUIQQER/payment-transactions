/**
 * @module package/quiqqer/payment-transactions/bin/backend/controls/Panel
 *
 * List all transactions in a grid
 */
define('package/quiqqer/payment-transactions/bin/backend/controls/Panel', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'controls/grid/Grid',
    'Ajax',
    'Locale'

], function (QUI, QUIPanel, Grid, QUIAjax, QUILocale) {
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
            '$onInject'
        ],

        options: {
            title: QUILocale.get(lg, 'menu.erp.payment-transactions.title'),
            icon : 'fa fa-money'
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

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
                text     : 'Korrektur',
                textimage: 'fa fa-trash-o',
                disabled : true,
                events   : {
                    onClick: this.openRemove
                }
            });

            // Grid
            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                pagination : true,
                columnModel: [{
                    header   : 'Date',
                    dataIndex: 'date',
                    dataType : 'string',
                    width    : 140
                }, {
                    header   : 'TX-ID',
                    dataIndex: 'txid',
                    dataType : 'string',
                    width    : 260
                }, {
                    header   : 'Amount',
                    dataIndex: 'amount',
                    dataType : 'numeric',
                    width    : 100
                }, {
                    header   : 'Currency',
                    dataIndex: 'currency_code',
                    dataType : 'string',
                    width    : 60
                }, {
                    header   : 'Hash',
                    dataIndex: 'hash',
                    dataType : 'string',
                    width    : 260
                }, {
                    header   : 'User',
                    dataIndex: 'uid',
                    dataType : 'numeric',
                    width    : 100
                }, {
                    header   : 'Username',
                    dataIndex: 'username',
                    dataType : 'string',
                    width    : 100
                }, {
                    header   : 'Name',
                    dataIndex: 'user_name',
                    dataType : 'string',
                    width    : 140
                }, {
                    header   : 'Payment',
                    dataIndex: 'payment',
                    dataType : 'string',
                    width    : 100
                }],
                onrefresh  : this.refresh
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
         */
        openAdd: function () {

        },

        /**
         * Open the remove dialog
         */
        openRemove: function () {

        }
    });
});

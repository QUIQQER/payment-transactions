<?php

/**
 * This file contains package_quiqqer_payment-transactions_ajax_backend_list
 */

use QUI\ERP\Accounting\Payments\Transactions\Search;

/**
 * Returns transaction list for a grid
 *
 * @param string $params - JSON query params
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_payment-transactions_ajax_backend_list',
    function ($params) {
        $Search = Search::getInstance();
        $Grid   = new QUI\Utils\Grid();

        // query params
        $query = $Grid->parseDBParams(json_decode($params, true));

        if (isset($query['limit'])) {
            $limit = \explode(',', $query['limit']);

            $Search->limit($limit[0], $limit[1]);
        }

        return $Search->searchForGrid();
    },
    ['params'],
    'Permission::checkAdminUser'
);

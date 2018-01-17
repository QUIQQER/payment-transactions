<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Transactions\Factory
 */

namespace QUI\ERP\Accounting\Payments\Transactions;

use QUI;

/**
 * Class Search
 *
 * @package QUI\ERP\Accounting\Payments\Transactions
 */
class Search extends QUI\Utils\Singleton
{
    /**
     * @var array
     */
    protected $filter = array();

    /**
     * @var array
     */
    protected $limit = array(0, 20);

    /**
     * @var string
     */
    protected $order = 'date DESC';

    /**
     * @var array
     */
    protected $allowedFilters = array(
        'from',
        'to'
    );

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * Set a filter
     *
     * @param string $filter
     * @param string|array $value
     * @todo implement
     */
    public function setFilter($filter, $value)
    {
        $keys = array_flip($this->allowedFilters);

        if (!isset($keys[$filter])) {
            return;
        }

        if (!is_array($value)) {
            $value = array($value);
        }

    }

    /**
     * Clear all filters
     */
    public function clearFilter()
    {
        $this->filter = array();
    }

    /**
     * Set the limit
     *
     * @param string $from
     * @param string $to
     */
    public function limit($from, $to)
    {
        $this->limit = array((int)$from, (int)$to);
    }

    /**
     * Set the order
     *
     * @param $order
     */
    public function order($order)
    {
        switch ($order) {
            case 'txid':
            case 'txid ASC':
            case 'txid DESC':

            case 'amount':
            case 'amount ASC':
            case 'amount DESC':

            case 'date':
            case 'date ASC':
            case 'date DESC':
                $this->order = $order;
                break;
        }
    }

    /**
     * Execute the search and return the order list
     *
     * @return array
     *
     * @throws QUI\Exception
     */
    public function search()
    {
        return $this->executeQueryParams($this->getQuery());
    }

    /**
     * Execute the search and return the order list for a grid control
     *
     * @return array
     * @throws QUI\Exception
     */
    public function searchForGrid()
    {
        $this->cache = array();

        // select display orders
        $orders = $this->executeQueryParams($this->getQuery());

        // count
        $count = $this->executeQueryParams($this->getQueryCount());
        $count = (int)$count[0]['count'];


        // total - calculation is without limit and paid_status
        $oldFiler = $this->filter;
        $oldLimit = $this->limit;

        $this->limit  = false;
        $this->filter = array_filter($this->filter, function ($filter) {
            return $filter['filter'] != 'paid_status';
        });

        $this->filter = $oldFiler;
        $this->limit  = $oldLimit;

        // result
        $result = $this->parseListForGrid($orders);
        $Grid   = new QUI\Utils\Grid();

        return array(
            'grid'  => $Grid->parseResult($result, $count),
            'total' => $count
        );
    }

    /**
     * @param bool $count - Use count select, or not
     * @return array
     */
    protected function getQuery($count = false)
    {
        $table = Factory::table();
        $order = $this->order;

        // limit
        $limit = '';

        if ($this->limit && isset($this->limit[0]) && isset($this->limit[1])) {
            $start = $this->limit[0];
            $end   = $this->limit[1];
            $limit = " LIMIT {$start},{$end}";
        }

        if (empty($this->filter)) {
            if ($count) {
                return array(
                    'query' => " SELECT COUNT(txid) AS count FROM {$table}",
                    'binds' => array()
                );
            }

            return array(
                'query' => "
                    SELECT *
                    FROM {$table}
                    ORDER BY {$order}
                    {$limit}
                ",
                'binds' => array()
            );
        }

        $where = array();
        $binds = array();
        $fc    = 0;

        foreach ($this->filter as $filter) {
            $bind = ':filter'.$fc;

            switch ($filter['filter']) {
                case 'from':
                    $where[] = 'date >= '.$bind;
                    break;

                case 'to':
                    $where[] = 'date <= '.$bind;
                    break;

                default:
                    continue;
            }

            $binds[$bind] = array(
                'value' => $filter['value'],
                'type'  => \PDO::PARAM_STR
            );

            $fc++;
        }

        $whereQuery = 'WHERE '.implode(' AND ', $where);


        if ($count) {
            return array(
                "query" => "
                    SELECT COUNT(txid) AS count
                    FROM {$table}
                    {$whereQuery}
                ",
                'binds' => $binds
            );
        }

        return array(
            "query" => "
                SELECT id
                FROM {$table}
                {$whereQuery}
                ORDER BY {$order}
                {$limit}
            ",
            'binds' => $binds
        );
    }

    /**
     * @return array
     */
    protected function getQueryCount()
    {
        return $this->getQuery(true);
    }

    /**
     * @param array $queryData
     * @return array
     * @throws QUI\Exception
     */
    protected function executeQueryParams($queryData = array())
    {
        $PDO   = QUI::getDataBase()->getPDO();
        $binds = $queryData['binds'];
        $query = $queryData['query'];

        $Statement = $PDO->prepare($query);

        foreach ($binds as $var => $bind) {
            $Statement->bindValue($var, $bind['value'], $bind['type']);
        }

        try {
            $Statement->execute();

            return $Statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception);
            QUI\System\Log::writeRecursive($query);
            QUI\System\Log::writeRecursive($binds);
            throw new QUI\Exception('Something went wrong');
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseListForGrid($data)
    {
        $Users = QUI::getUsers();

        foreach ($data as $key => $entry) {
            $data[$key]['amount']   = QUI\ERP\Money\Price::validatePrice($entry['amount']);
            $data[$key]['currency'] = json_decode($entry['currency'], true);
            $data[$key]['data']     = json_decode($entry['data'], true);
            $data[$key]['uid']      = (int)$entry['uid'];

            // user
            try {
                $User = $Users->get($data[$key]['uid']);

                $data[$key]['username']  = $User->getUsername();
                $data[$key]['user_name'] = $User->getName();
            } catch (QUI\Exception $Exception) {
                $data[$key]['username']  = '---';
                $data[$key]['user_name'] = '---';
            }

            // currency
            try {
                $Currency = QUI\ERP\Currency\Handler::getCurrency(
                    $data[$key]['currency']['code']
                );

                $data[$key]['currency_code'] = $Currency->getCode();
            } catch (QUI\Exception $Exception) {
                $data[$key]['currency_code'] = '---';
            }
        }

        return $data;
    }
}

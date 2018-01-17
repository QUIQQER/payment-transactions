<?php

define('SYSTEM_INTERN', true);

require_once 'bootstrap.php';

QUI::getDataBase()->table()->truncate('orders');
QUI::getDataBase()->table()->truncate('orders_process');
QUI::getDataBase()->table()->truncate('invoice_temporary');
QUI::getDataBase()->table()->truncate('invoice');
QUI::getDataBase()->table()->truncate('payment_transactions');

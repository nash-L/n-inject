<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/12
 * Time: 1:55
 */

use NashInject\Type\DateType;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

function searchData($query, DateType $startDate, DateType $endDate)
{
    return [
        'keyword' => $query,
        'create_at' => [
            $startDate->getStartDateTime()->toTimestamp()->getData(),
            $endDate->getEndDateTime()->toTimestamp()->getData()
        ],
        'create_time' => [
            $startDate->getStartDateTime()->getData(),
            $endDate->getEndDateTime()->getData()
        ]
    ];
}

$inject = new \NashInject\Injector();

$where = $inject->execute('searchData', ['query' => 'Hello', 'startDate' => '2018-12-22', 'endDate' => '2018-12-22']);

var_dump($where);

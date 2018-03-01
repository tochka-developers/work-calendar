<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tochka\Calendar\WorkCalendar;

/**
 * Тестируем класс WorkCalendar
 *
 * @author ivanov
 */
class WorkCalendarTest extends TestCase
{
    public function isWorkdayDataProvider()
    {
        return [
            ['2018-01-01', false],
            ['2018-01-08', false],
            ['2018-01-09', true],
            ['2018-02-22', true],
            ['2018-02-23', false],
            ['2018-02-24', false],
            ['2018-03-07', true],
            ['2018-03-08', false],
            ['2018-03-09', false],
            ['2018-03-16', true],
            ['2018-04-28', true],
            ['2018-04-29', false],
            ['2018-04-30', false],
            ['2018-05-08', true],
            ['2018-05-09', false],
            ['2018-05-10', true],
            ['2018-06-09', true],
            ['2018-06-10', false],
            ['2018-06-12', false],
            ['2018-06-13', true],
            ['2018-08-06', true],
            ['2018-08-07', true],
            ['2018-08-08', true],
            ['2018-08-09', true],
            ['2018-08-10', true],
            ['2018-08-11', false],
            ['2018-08-12', false],
            ['2018-12-28', true],
            ['2018-12-29', true],
            ['2018-12-30', false],
            ['2018-12-31', false],
        ];
    }

    /**
     * @dataProvider isWorkdayDataProvider
     */
    public function testIsWorkday($dateString, $expected)
    {
        list($year, $month, $day) = explode('-', $dateString);
        $date = WorkCalendar::create($year, $month, $day);
        $this->assertEquals($expected, $date->isWorkday());
    }

    public function addWorkdayDataProvider()
    {
        return [
            ['2017-12-29', '2018-01-09'],
            ['2017-12-31', '2018-01-09'],
            ['2018-01-05', '2018-01-09'],
            ['2018-01-08', '2018-01-09'],
            ['2018-04-28', '2018-05-03'],
            ['2018-05-08', '2018-05-10'],
            ['2018-05-09', '2018-05-10'],
            ['2018-05-15', '2018-05-16'],
        ];
    }

    /**
     * @dataProvider addWorkdayDataProvider
     */
    public function testAddWorkday($initialDateString, $expected)
    {
        list($year, $month, $day) = explode('-', $initialDateString);
        $date = WorkCalendar::create($year, $month, $day);
        $date->addWorkday();
        $this->assertEquals($expected, $date->format('Y-m-d'));
    }

    public function subWorkdayDataProvider()
    {
        return [
            ['2018-01-09', '2017-12-29'],
            ['2018-01-08', '2017-12-29'],
            ['2018-01-01', '2017-12-29'],
            ['2017-12-30', '2017-12-29'],
            ['2018-02-26', '2018-02-22'],
            ['2018-05-10', '2018-05-08'],
            ['2018-06-13', '2018-06-09'],
        ];
    }

    /**
     * @dataProvider subWorkdayDataProvider
     */
    public function testSubWorkday($initialDateString, $expected)
    {
        list($year, $month, $day) = explode('-', $initialDateString);
        $date = WorkCalendar::create($year, $month, $day);
        $date->subWorkday();
        $this->assertEquals($expected, $date->format('Y-m-d'));
    }
}
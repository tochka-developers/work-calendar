<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tochka\Calendar\WorkCalendar;

/**
 * Тестируем класс WorkCalendar
 *
 * @author Ivanov Sergey<ivanov@tochka.com>
 */
class WorkCalendarTest extends TestCase
{
    public function isWorkdayDataProvider()
    {
        return [
            ['2016-02-29', true],
            ['2016-12-30', true],
            ['2016-12-31', false],
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

            // проверяем генерацию года, на который нет точных данных
            ['1970-01-01', false],
            ['1970-01-02', false],
            ['1970-01-03', false],
            ['1970-01-04', false],
            ['1970-01-05', false],
            ['1970-01-06', false],
            ['1970-01-07', false],
            ['1970-01-08', false],
            ['1970-01-09', true],
            ['1970-01-10', false],
            ['1970-02-22', false],
            ['1970-02-23', false],
            ['1970-02-24', true],
            ['1970-03-06', true],
            ['1970-03-07', false],
            ['1970-03-08', false],
            ['1970-03-09', false],
            ['1970-03-10', true],
            ['1970-04-17', true],
            ['1970-04-18', false],
            ['1970-04-19', false],
            ['1970-04-20', true],
            ['1970-04-30', true],
            ['1970-05-01', false],
            ['1970-05-02', false],
            ['1970-05-03', false],
            ['1970-05-04', true],
            ['1970-05-08', true],
            ['1970-05-09', false],
            ['1970-05-10', false],
            ['1970-05-11', false],
            ['1970-05-12', true],
            ['1970-06-11', true],
            ['1970-06-12', false],
            ['1970-06-13', false],
            ['1970-11-03', true],
            ['1970-11-04', false],
            ['1970-11-05', true],
    
            ['2020-01-02', false],
            ['2020-01-14', true],
        ];
    }

    /**
     * @dataProvider isWorkdayDataProvider
     */
    public function testIsWorkday($dateString, $expected)
    {
        [$year, $month, $day] = explode('-', $dateString);
        $date = WorkCalendar::createFromDate($year, $month, $day);
        
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
            ['2018-12-29', '2019-01-09'],
        ];
    }

    /**
     * @dataProvider addWorkdayDataProvider
     */
    public function testAddWorkday($initialDateString, $expected)
    {
        [$year, $month, $day] = explode('-', $initialDateString);
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
        [$year, $month, $day] = explode('-', $initialDateString);
        $date = WorkCalendar::create($year, $month, $day);
        $date->subWorkday();
        $this->assertEquals($expected, $date->format('Y-m-d'));
    }

    public function addWorkdaysDataProvider()
    {
        return [
            ['2018-01-01', 3, '2018-01-11'],
            ['2018-01-01', 5, '2018-01-15'],
            ['2018-01-01', 10, '2018-01-22'],
            ['2018-04-27', 5, '2018-05-08'],
            ['2018-06-13', 1, '2018-06-14'],
            ['2018-11-10', 7, '2018-11-20'],
        ];
    }

    /**
     * @dataProvider addWorkdaysDataProvider
     */
    public function testAddWorkdays($initialDateString, $addDays, $expected)
    {
        [$year, $month, $day] = explode('-', $initialDateString);
        $date = WorkCalendar::create($year, $month, $day);
        $date->addWorkdays($addDays);
        $this->assertEquals($expected, $date->format('Y-m-d'));
    }

    public function subWorkdaysDataProvider()
    {
        return [
            ['2018-01-10', 5, '2017-12-26'],
            ['2018-02-28', 3, '2018-02-22'],
            ['2018-03-07', 10, '2018-02-20'],
            ['2018-11-20', 7, '2018-11-09'],
            ['2018-12-14', 2, '2018-12-12'],
        ];
    }

    /**
     * @dataProvider subWorkdaysDataProvider
     */
    public function testSubWorkdays($initialDateString, $addDays, $expected)
    {
        [$year, $month, $day] = explode('-', $initialDateString);
        $date = WorkCalendar::create($year, $month, $day);
        $date->subWorkdays($addDays);
        $this->assertEquals($expected, $date->format('Y-m-d'));
    }

    public function diffInWorkdaysDataProvider()
    {
        return [
            ['2017-12-25', '2018-02-01', 22],
            ['2018-04-25', '2018-05-05', 5],
            ['2018-05-05', '2018-04-25', -5],
            ['2018-06-01', '2018-06-10', 6],
            ['2018-06-21', '2018-06-21', 0],
            ['2018-06-21', '2018-06-22', 1],
            ['2018-06-21', '2018-06-20', -1],
            ['2018-06-09', '2018-06-13', 1],
        ];
    }

    /**
     * @dataProvider diffInWorkdaysDataProvider
     */
    public function testDiffInWorkdays($firstDateString, $secondDateString, $workdaysDiffCount)
    {
        [$year, $month, $day] = explode('-', $firstDateString);
        $firstDate = WorkCalendar::create($year, $month, $day);

        [$year, $month, $day] = explode('-', $secondDateString);
        $secondDate = WorkCalendar::create($year, $month, $day);

        $actualWorkdaysDiffCount = $firstDate->diffInWorkdays($secondDate);

        $this->assertEquals($workdaysDiffCount, $actualWorkdaysDiffCount);
        $this->assertEquals($firstDateString, $firstDate->format('Y-m-d'));
        $this->assertEquals($secondDateString, $secondDate->format('Y-m-d'));
    }
}
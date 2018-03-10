<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tochka\Calendar\RussianYearMaskProvider;

/**
 * Тестируем класс RussianYearMaskProviderTest
 *
 * @author Ivanov Sergey<ivanov@tochka.com>
 */
class RussianYearMaskProviderTest extends TestCase
{
    public function getYearMaskOkDataProvider()
    {
        return [
            [2013, 365],
            [2014, 365],
            [2015, 365],
            [2016, 366],
            [2017, 365],
            [2018, 365],
        ];
    }

    /**
     * @dataProvider getYearMaskOkDataProvider
     */
    public function testGetYearMaskOk($year, $daysCount)
    {
        $dataProvider = new RussianYearMaskProvider;
        $mask = $dataProvider->getYearMask($year);

        $this->assertEquals(count($mask), $daysCount);

        $expectedMask = file_get_contents(RussianYearMaskProvider::RES_DIR . $year . '.json');
        $expectedMask = json_decode($expectedMask, true);

        $this->assertEquals($expectedMask, $mask);
    }

    /**
     * @expectedException Exception
     */
    public function testGetYearMaskException()
    {
        $dataProvider = new RussianYearMaskProvider;
        $mask = $dataProvider->getYearMask(1970);
    }
}
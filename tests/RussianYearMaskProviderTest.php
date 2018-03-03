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
            [2013],
            [2014],
            [2015],
            [2016],
            [2017],
            [2018],
        ];
    }

    /**
     * @dataProvider getYearMaskOkDataProvider
     */
    public function testGetYearMaskOk($year)
    {
        $dataProvider = new RussianYearMaskProvider;
        $mask = $dataProvider->getYearMask($year);

        $this->assertEquals(count($mask), 365);

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
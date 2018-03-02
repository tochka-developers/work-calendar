<?php

namespace Tochka\Calendar;

use Carbon\Carbon;
use Exception;

/**
 * Надстройка над Carbon, которая позволяет
 * легко оперировать рабочими днями по ТК РФ
 *
 * @author Ivanov Sergey<ivanov@tochka.com>
 */
class WorkCalendar extends Carbon
{
    /**
     * Директория, в которую кешируются
     * маски рабочих дней по годам
     */
    public static $RES_DIR = __DIR__ . '/../resources/';

    /**
     * Массив с масками по годам
     */
    private static $yearMasks = [];

    /**
     * @param int $year Год в формате ГГГГ
     * @throws Exception
     */
    public static function getYearMask(int $year)
    {
        if (!isset(self::$yearMasks[$year])) {
            self::$yearMasks[$year] = self::getYearMaskFromResources($year);
        }

        return self::$yearMasks[$year];
    }

    /**
     * @param int $year Год в формате ГГГГ
     * @throws Exception
     */
    protected static function getYearMaskFromResources(int $year)
    {
        $fileName = static::$RES_DIR . $year;
        if (file_exists($fileName)) {
            $content = @ file_get_contents($fileName);
            if ($content === false) {
                throw new Exception('Failed to read file: ');
            }

            return json_decode($content, true);
        } else {
            $workdaysYearMask = self::generateYearMask($year);
            file_put_contents($fileName, json_encode($workdaysYearMask));

            return $workdaysYearMask;
        }
    }

    /**
     * Получение массива-маски с рабочими днями в году.
     * Ключами массива являются номер дня в году(начиная с 0),
     * значениями - 1(рабочий) и 0(выходной)
     *
     * @param int $year Год в формате ГГГГ
     * @return array
     * @throws Exception
     */
    protected static function generateYearMask(int $year)
    {
        $workdaysYearMask = array_fill(0, 365, 0);

        // для начала берем xml с
        // праздничными и сокращенными днями в РФ
        $url = "http://xmlcalendar.ru/data/ru/{$year}/calendar.xml";
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new Exception('Could not load data from URL: ' . $url);
        }

        try {
            $xmlCalendar = @ new \SimpleXmlElement($content);
        } catch (Exception $ex) {
            throw $ex;
        }

        $holidays    = [];
        $cutWorkdays = [];
        foreach ($xmlCalendar->days->day as $dayXml) {
            $recType = (string) $dayXml['t'];
            if ($recType == '1') {
                $holidays[] = (string) $dayXml['d'];
            } elseif ($recType == '2') {
                $cutWorkdays[] = (string) $dayXml['d'];
            }
        }

        $currentDate = new \DateTime($year.'-01-01 00:00');
        $daysCount = $currentDate->format('L') === '1' ? 365 : 364;
        // заполняем маску года днями отдыха
        for ($i = 0; $i < $daysCount; $i++) {
            $isHoliday = false;
            $format = $currentDate->format('m.d');

            if (in_array($format, $holidays)) {
                // если день в праздничном массиве - выходной
                $isHoliday = true;
            } elseif (in_array($format, $cutWorkdays)) {
                // если день в укороченном рабочем дне - не выходной
                // ничего не делаем
            } elseif (in_array($currentDate->format('w'), ['0', '6'])) {
                // если день недели суббота или воскресенье - выходной
                $isHoliday = true;
            }
            
            $workdaysYearMask[$i] = $isHoliday ? 0 : 1;

            $currentDate->modify('+1 day');
        }

        return $workdaysYearMask;
    }

    /**
     * Добавить рабочий день к текущей дате
     */
    public function addWorkday()
    {
        $this->addWorkdays(1);
    }

    /**
     * Добавить несколько рабочих дней к текущей дате
     *
     * @param int $count Количество добавляемых рабочих дней
     */
    public function addWorkdays(int $count)
    {
        $count = abs($count);
        while ($count > 0) {
            $this->addDay();

            if ($this->isWorkday()) {
                $count--;
            }
        }
    }

    /**
     * Вычесть рабочий день от текущей даты
     */
    public function subWorkday()
    {
        $this->subWorkdays(1);
    }

    /**
     * Вычесть несколько рабочих дней с текущей даты
     *
     * @param int $count Количество вычитаемых рабочих дней
     */
    public function subWorkdays(int $count)
    {
        $count = abs($count);
        while ($count > 0) {
            $this->subDay();

            if ($this->isWorkday()) {
                $count--;
            }
        }
    }

    /**
     * True - рабочий день, false - выходной
     *
     * @return bool
     */
    public function isWorkday(): bool
    {
        $mask = self::getYearMask($this->year);
        return (boolean)$mask[$this->dayOfYear];
    }

    /**
     * Вычисляется разница в рабочих днях между двумя датами.
     * Возвращается число рабочих дней.
     *
     * @param WorkCalendar $carbon Дата, с которой надо
     * @return int
     */
    public function diffInWorkdays(WorkCalendar $carbon): int
    {
        $workdaysDiffCount = 0;

        $daysDiffCount = $this->diffInDays($carbon, false);
        if ($daysDiffCount > 0) {
            // $carbon больше текущей даты
            $initialDate = $this->copy();
            $revert = false;
        } else {
            // $carbon меньше текущей даты
            $initialDate = $carbon;
            $revert = true;

            $daysDiffCount = abs($daysDiffCount);
        }

        for ($i = 1; $i <= $daysDiffCount; $i++) {
            $initialDate->addDay();
            if ($initialDate->isWorkday()) {
                $workdaysDiffCount++;
            }
        }

        if ($revert) {
            $workdaysDiffCount = -$workdaysDiffCount;
        }

        return $workdaysDiffCount;
    }
}
<?php

namespace Tochka\Calendar;

use Exception;
/**
 * Description of AbstractYearMaskProvider
 *
 * @author ivanov
 */
class RussianYearMaskProvider extends AbstractYearMaskProvider
{
    const RES_DIR = __DIR__ . '/../resources/ru/';
    
    protected function generateYearMask(int $year)
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
}
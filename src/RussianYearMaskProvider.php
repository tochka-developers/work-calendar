<?php

namespace Tochka\Calendar;

use DateTime;
use DateInterval;
use Exception;

/**
 * Маски рабочих дней для РФ
 *
 * @author Ivanov Sergey<ivanov@tochka.com>
 */
class RussianYearMaskProvider extends AbstractYearMaskProvider
{
    const RES_DIR = __DIR__ . '/../resources/ru/';

    /**
     * Логика работы такова - пробуем взять xml
     * с празничными и укороченными рабочими днями
     * из xmlcalendar.ru. Если не получается по любой причине
     * (будь то ошибка взаимодействия, либо отсутствие инфы на сайте)
     * - ориентируемся на ст. 112 ТК РФ
     */
    protected function generateYearMask(int $year)
    {
        try {
            [$holidays, $cutDays, $workdays] = $this->getDataFromXmlCalendar($year);
        } catch (Exception $ex) {
            $holidays = $this->getCalculatedData($year);
            $cutDays = [];
            $workdays = [];
        }

        return $this->_generateYearMask($year, $holidays, $cutDays, $workdays);
    }

    private function getCalculatedData(int $year)
    {
        $holidays = [];
        $unportableHolidays = [
            '01.01', '02.01', '03.01', '04.01',
            '05.01', '06.01', '07.01', '08.01',
        ];
        $portableHolidays = [
            '23.02', '08.03', '01.05', '09.05', '12.06', '04.11'
        ];
        $portedHolidays = [];

        $currentDate = new \DateTime($year.'-01-01 00:00');
        $daysCount   = $currentDate->format('L') === '1' ? 366 : 365;
        for ($i = 0; $i < $daysCount; $i++) {
            $dm = $currentDate->format('d.m');
            $dateIndex = $currentDate->format('z');

            if (in_array($dm, $unportableHolidays)
                || in_array($dm, $portedHolidays)) {
                $holidays[] = $dateIndex;
            }

            if (in_array($dm, $portableHolidays)) {
                $holidays[] = $dateIndex;

                $weekday = $currentDate->format('w');
                if ($weekday == /* воскресенье */'0') {
                    $cloned = clone $currentDate;
                    $cloned->add(new DateInterval('P1D'));
                    $portedHolidays[] = $cloned->format('d.m');
                }

                if ($weekday == /* суббота */'6') {
                    $cloned = clone $currentDate;
                    $cloned->add(new DateInterval('P2D'));
                    $portedHolidays[] = $cloned->format('d.m');
                }
            }

            $currentDate->modify('+1 day');
        }

        return $holidays;
    }
    
    private function getDataFromXmlCalendar(int $year)
    {
        $url = "http://xmlcalendar.ru/data/ru/{$year}/calendar.xml";
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new Exception('Could not load data from URL: ' . $url);
        }
        
        // номера дней, которые являются выходными. 
        // 1 января имеет индекс 0
        $holidays    = []; 
        $cutWorkdays = [];
        $workdays    = [];

        try {
            $xmlCalendar = @ new \SimpleXmlElement($content);
            foreach ($xmlCalendar->days->day as $dayXml) {
                $md   = (string) $dayXml['d'];
                $date = DateTime::createFromFormat('Y.m.d', $year.'.'.$md);
                $dateIndex = (int) $date->format('z');

                $recType = (string)$dayXml['t'];
                if ($recType === '1') {
                    $holidays[] = $dateIndex;
                } elseif ($recType === '2') {
                    $cutWorkdays[] = $dateIndex;
                } elseif ($recType === '3') {
                    $workdays[] = $dateIndex;
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        
        return [$holidays, $cutWorkdays, $workdays];
    }

    private function _generateYearMask($year, $holidays, $cutDays, $workdays)
    {
        $currentDate      = new \DateTime($year.'-01-01 00:00');
        $daysCount        = $currentDate->format('L') === '1' ? 366 : 365;
        $workdaysYearMask = array_fill(0, $daysCount, 0);

        // заполняем маску года днями отдыха
        for ($i = 0; $i < $daysCount; $i++) {
            $isHoliday = false;

            if (in_array($i, $holidays)) {
                // если день в массиве праздничных дней - выходной
                $isHoliday = true;
            } elseif (in_array($i, $cutDays)) {
                // если день в массиве сокращенных рабочих дней - рабочий день
                // иногда бывает так, что суббота является укороченным рабочим днем
            } elseif (in_array($i, $workdays)) {
                // если день в массиве рабочих дней - рабочий день
                // иногда бывает так, что суббота или воскресенье является рабочим днем
            } elseif (in_array($currentDate->format('w'), [/* воскресенье */'0', /* суббота */'6'])) {
                // если день недели суббота или воскресенье - выходной
                $isHoliday = true;
            }

            $workdaysYearMask[$i] = $isHoliday ? 0 : 1;

            $currentDate->modify('+1 day');
        }

        return $workdaysYearMask;
    }
}
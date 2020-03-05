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
    public const DEFAULT_MASK_PROVIDER = RussianYearMaskProvider::class;

    private static $maskProvider;

    /**
     * @param AbstractYearMaskProvider $provider
     */
    public static function setMaskProvider(AbstractYearMaskProvider $provider)
    {
        static::$maskProvider = $provider;
    }
    
    /**
     * @param int $year Год в формате ГГГГ
     *
     * @return mixed
     */
    public static function getYearMask(int $year)
    {
        if (static::$maskProvider === null) {
            $className = static::DEFAULT_MASK_PROVIDER;
            static::$maskProvider = new $className;
        }

        return static::$maskProvider->getYearMask($year);
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
    
        return (boolean)$mask[$this->dayOfYear-1];
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
            $initialDate = $carbon->copy();
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
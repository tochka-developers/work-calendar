<?php

namespace Tochka\Calendar;

use Exception;

/**
 * Класс-провайдер годовой маски рабочих дней
 *
 * @author Ivanov Sergey<ivanov@tochka.com>
 */
abstract class AbstractYearMaskProvider
{
    const RES_DIR = __DIR__ . '/../resources/';

    /** @var array Массив масок с рабочими днями по годам */
    protected $yearMasks = [];

    /**
     * @param int $year Год в формате ГГГГ
     * @throws Exception
     */
    public function getYearMask(int $year)
    {
        if (!isset($this->yearMasks[$year])) {
            $this->yearMasks[$year] = $this->getYearMaskFromResources($year);
        }

        return $this->yearMasks[$year];
    }

    /**
     * @param int $year Год в формате ГГГГ
     * @throws Exception
     */
    protected function getYearMaskFromResources(int $year)
    {
        $fileName = static::RES_DIR . $year . '.json';
        if (file_exists($fileName)) {
            $content = @ file_get_contents($fileName);
            if ($content === false) {
                throw new Exception('Failed to read file: ');
            }

            return json_decode($content, true);
        } else {
            $workdaysYearMask = $this->generateYearMask($year);
            file_put_contents($fileName, json_encode($workdaysYearMask));

            return $workdaysYearMask;
        }
    }

    /**
     * Получение массива-маски с рабочими днями в году.
     * Ключами массива являются номер дня в году(начиная с 0),
     * значениями - 1(рабочий) и 0(выходной).
     * 
     * Нулевой элемент массива - 1 января года,
     * последний элемент массива - 31 декабря года
     *
     * @param int $year Год в формате ГГГГ
     * @return array
     * @throws Exception
     */
    abstract protected function generateYearMask(int $year);
}
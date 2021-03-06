# Work Calendar
---
## Описание
Класс-хелпер позволяет удобно работать с производственным календарем. По-умолчанию, настроен производственный календарь РФ без учета региональных праздников. Расширяет функционал Carbon\Carbon.
## Установка
С использованием composer:
```sh
$ composer require tochka-developers/work-calendar
```
## Использование
Методы для удобной работы с производственным календарем:
* isWorkday(): bool - true, если день рабочий, иначе false;
* diffInWorkdays(WorkCalendar $carbon): int - разница в рабочих днях между двумя датами. Может возвращать отрицательное значение, если передаваемая дата меньше(раньше) текущей;
* addWorkday() - добавить рабочий день к текущей дате. То есть экземпляр будет хранить следующий рабочий день вместо установленного дня;
* subWorkday() - отнять рабочий день от текущей даты. То есть экземпляр будет хранить предыдущий рабочий день вместо установленного дня;
* addWorkdays(int $count) - добавить ```$count``` рабочих дней к текущей дате;
* subWorkdays(int $count) - отнять ```$count``` рабочих дней от текущей даты.
### Примеры использования
```php
$date = WorkCalendar::create('2018', '02', '22');
print_r($date->isWorkday()); // true
...
$date->addDay(); // 2018-02-23, день защитника отечества
print_r($date->isWorkday()); // false
...
$date->addWorkday();
print_r($date->format('Y-m-d') // 2018-02-26
...
$date->subWorkday();
print_r($date->format('Y-m-d') // 2018-02-22
...
$date->addWorkdays(5);
print_r($date->format('Y-m-d') // 2018-03-02
...
$date->subWorkdays(5);
print_r($date->format('Y-m-d') // 2018-02-22
```
## Собственный производственный календарь
Если есть необходимость в учете региональных праздников, либо создать производственный календарь для другой страны, то это можно сделать следующим образом:
* Унаследовать функциональность класса `Tochka\Calendar\AbstractYearMaskProvider` собственным классом и переопределить метод `generateYearMask()`. Данный метод должен возвращать маску рабочих и выходных дней в виде массива, где 1 - это рабочий день, а 0 - праздничный. Количество элементов должно быть равно количеству дней в году;
* В `Tochka\Calendar\WorkCalendar::setMaskProvider()` передать экземпляр вновь созданного класса;
* Готово, можно пользоваться WorkCalendar.
## Источники
* [Производственный календарь в XML-формате](http://xmlcalendar.ru/)
* [Производственный календарь в удобном для человека формате](http://www.consultant.ru/law/ref/calendar/proizvodstvennye/2018/)
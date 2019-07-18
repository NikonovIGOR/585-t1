<?php

/* ЗАДАЧА 1
 *
 * Дано:
 * 	- Текст из *.csv файла
 * Необходимо:
 * 	1. Распарсить текст, подготовить данные к работе (элемент = тип Объект)
 * 	2. Отсортировать данные по дате затем КБК и вывести в таблице, таким образом, что если существует несколько записей на одну дату с одним КБК, то в поле %% считать среднее, а в скобках вывести кол-во елементов.
 *
 *  Пример Табл.:
 *  | ДАТА       | КБК      | Адрес             | %%      |
 *  | 11.01.2013 | 1-01-001 | Спб, Восстания, 1 | 84% (2) |
 *
 */


$data = "
02-01-2013;1-01-001;Спб, Восстания, 1;95
05-01-2013;1-02-011;Спб, Савушкина, 106;87
01-01-2013;1-01-003;Спб, Обводный канал, 12 ;92
06-02-2013;2-05-245;Ростов-на-Дону, Стачек, 41;79
12-01-2012;5-10-002;Новосибирск, Ленина, 105;75
01-01-2013;1-01-003;Спб, Обводный канал, 12 ;98
03-01-2013;6-30-855;Сочи, Коммунистическая, 2;84
05-01-2013;2-04-015;Ростов-на-Дону, Пушкинская, 102;71
07-01-2013;6-01-010;Сочи, Приморская, 26;62
05-01-2013;1-02-011;Спб, Савушкина, 106;89
01-01-2013;1-01-003;Спб, Обводный канал, 12 ;57
";

/**
 * Помощник
 */
class Helper
{
    /**
     * Парсиг CSV
     * @param string $csv
     * @return array
     */
    public static function parsingCsv(string $csv)
    {
        $rows = explode("\n", $csv);
        $result = [];
        foreach ($rows as $i => $row) {
            if (!empty(trim($row))) {
                $result[$i] = array_map('trim', explode(';', $row));
            }
        }
        return $result;
    }

    /**
     * Сортировка по свойству
     * @param $a
     * @param $b
     * @param string $attr
     * @return int
     */
    public static function sortItems($a, $b, string $attr)
    {
        if ($a->$attr() == $b->$attr()) {
            return 0;
        }
        return ($a->$attr() < $b->$attr()) ? -1 : 1;
    }
}

class Item
{
    public $date;
    public $kbk;
    public $address;
    public $percent;

    public $avgPercent;
    public $countPercent;

    function __construct($properties = [])
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    public function kbkFormat(): int
    {
        return preg_replace("/[^0-9]/", '', $this->kbk);
    }

    public function dateFormat(): int
    {
        return strtotime($this->date);
    }

}

class Table
{
    public $rows = [];
    public $percent = [];
    private $_data;

    function __construct($data)
    {
        $this->_data = $data;
        $this->setRows();
        $this->sortRows();
        $this->recountPercent();
    }

    /**
     * Парсинг CSV текста и подготовка данных к работе
     */
    private function setRows()
    {
        foreach (Helper::parsingCsv($this->_data) as $el) {
            $item = new Item();
            list($item->date, $item->kbk, $item->address, $item->percent) = $el;
            $this->rows[] = $item;
        }
    }

    /**
     * Сортировка по дате и кбк
     */
    private function sortRows()
    {
        usort($this->rows, function ($a, $b) {
            $res = Helper::sortItems($a, $b, 'dateFormat');
            if (!$res) {
                return Helper::sortItems($a, $b, 'kbkFormat');
            }
            return $res;
        });
    }

    /**
     * Если существует несколько записей на одну дату с одним КБК,
     * то в поле %% считать среднее, а в скобках вывести кол-во элементов.
     */
    private function recountPercent()
    {
        foreach ($this->rows as $row) {
            $this->percent[$row->date][$row->kbk][] = $row->percent;
        }
        foreach ($this->rows as &$row) {
            $percents = $this->percent[$row->date][$row->kbk];
            $row->countPercent = count($percents);
            $row->avgPercent = round(array_sum($percents) / $row->countPercent);
        }
    }
}

?>

<!DOCTYPE html>

<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Тестовое 585 (Никонов Игорь)</title>
  <style>
    table td {
      padding: 5px 10px;
    }
  </style>
</head>
<body>
<table>
  <tr>
    <th>Дата</th>
    <th>КБК</th>
    <th>Адрес</th>
    <th>%%</th>
  </tr>
    <?php /** @var Item $item */ ?>
    <?php foreach ((new Table($data))->rows as $item) { ?>
      <tr>
        <td><?= $item->date ?></td>
        <td><?= $item->kbk ?></td>
        <td><?= $item->address ?></td>
        <td><?= $item->avgPercent ?> <?= $item->countPercent > 1 ? "({$item->countPercent})" : '' ?></td>
      </tr>
    <?php } ?>
</table>
</body>
</html>

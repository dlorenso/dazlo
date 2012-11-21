<?php
namespace \Daz\Db\Query;

class Example extends Daz\Db\Connection\Example
{
    public static function photoCreate($year, $month, $day, $path)
    {
        $SQL = self :: statement();
        $SQL->sql('INSERT INTO photo (year, month, day, path)');
        $SQL->sql('VALUES (?, ?, ?, ?)')->setInt($year)->setInt($month)->setInt($day)->set($path);

        // run
        return self :: queryInsert($SQL);
    }

    public static function photoDeleteAll()
    {
        $SQL = self :: statement();
        $SQL->sql('DELETE FROM photo');

        // run
        return self :: queryDelete($SQL);
    }
}
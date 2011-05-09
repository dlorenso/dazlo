<?php
class Daz_Db_Query_Example extends Daz_Db_Connection_Example {
    //----------------------------------------------------------------------
    public static function photoCreate($year, $month, $day, $path) {
        $SQL = self :: statement();
        $SQL->sql('INSERT INTO photo (year, month, day, path)');
        $SQL->sql('VALUES (?, ?, ?, ?)')->setInt($year)->setInt($month)->setInt($day)->set($path);
        $SQL->dump();

        // run
        return self :: queryInsert($SQL);
    }

    //----------------------------------------------------------------------
    public static function photoDeleteAll() {
        $SQL = self :: statement();
        $SQL->sql('DELETE FROM photo');

        // run
        return self :: queryDelete($SQL);
    }

    //----------------------------------------------------------------------
}

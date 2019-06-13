<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Table extends Model
{
    public static function getColumns($tableName) {
        return DB::table('INFORMATION_SCHEMA.COLUMNS')->select(['COLUMN_NAME', 'DATA_TYPE'])
            ->where('TABLE_NAME', $tableName)
            ->whereNotIn('COLUMN_NAME', ['id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'lang'])
            ->get();



    }
}

<?php

namespace Mostafaznv\Larupload\Database\Schema;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\Facades\DB;
use PDO;

class Blueprint
{
    /**
     * Add upload columns to the table
     *
     * @param BlueprintIlluminate $table
     * @param string $name
     * @param string $mode
     */
    public static function columns(BlueprintIlluminate $table, string $name, string $mode = 'heavy'): void
    {
        $table->string("{$name}_file_name", 255)->nullable();

        if ($mode == 'heavy') {
            $table->unsignedInteger("{$name}_file_size")->nullable();
            $table->string("{$name}_file_type", 85)->nullable();
            $table->string("{$name}_file_mime_type", 85)->nullable();
            $table->unsignedInteger("{$name}_file_width")->nullable();
            $table->unsignedInteger("{$name}_file_height")->nullable();
            $table->unsignedInteger("{$name}_file_duration")->nullable();
            $table->string("{$name}_file_dominant_color", 7)->nullable();
            $table->string("{$name}_file_format", 85)->nullable();
            $table->string("{$name}_file_cover", 85)->nullable();
        }
        else {
            $table->{self::jsonColumnType()}("{$name}_file_meta")->nullable();
        }
    }

    /**
     * Drop upload columns
     *
     * @param BlueprintIlluminate $table
     * @param $name
     * @param string $mode
     */
    public static function dropColumns(BlueprintIlluminate $table, string $name, string $mode = 'heavy'): void
    {
        $columns = static::getDefaultColumns($name, $mode);
        $table->dropColumn($columns);
    }

    /**
     * Get a list of default columns
     *
     * @param $name
     * @param string $mode
     * @return array
     */
    public static function getDefaultColumns(string $name, string $mode = 'heavy'): array
    {
        $columns = [
            "{$name}_file_name"
        ];

        if ($mode == 'heavy') {
            $coverColumns = [
                "{$name}_file_size", "{$name}_file_type", "{$name}_file_mime_type", "{$name}_file_width", "{$name}_file_height", "{$name}_file_duration", "{$name}_file_format", "{$name}_file_cover"
            ];
        }
        else {
            $coverColumns = [
                "{$name}_file_size", "{$name}_file_type", "{$name}_file_mime_type", "{$name}_file_width", "{$name}_file_height", "{$name}_file_duration", "{$name}_file_format", "{$name}_file_cover"
            ];
        }

        $columns = array_merge($columns, $coverColumns);

        return $columns;
    }

    /**
     * Get json column data type
     *
     * @return string
     */
    protected static function jsonColumnType(): string
    {
        return DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' && version_compare(DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), '5.7.8', 'ge') ? 'json' : 'text';
    }
}

<?php

namespace Mostafaznv\Larupload\Database\Schema;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\Facades\DB;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use PDO;

class Blueprint
{
    /**
     * Add upload columns to the table
     *
     * @param BlueprintIlluminate $table
     * @param string $name
     * @param LaruploadMode $mode
     */
    public static function columns(BlueprintIlluminate $table, string $name, LaruploadMode $mode = LaruploadMode::HEAVY): void
    {
        $table->string("{$name}_file_name", 255)->nullable();

        if ($mode === LaruploadMode::HEAVY) {
            $table->string("{$name}_file_id", 36)->nullable();

            if (config('larupload.store-original-file-name', false)) {
                $table->string("{$name}_file_original_name", 255)->nullable()->index();
            }

            $table->unsignedInteger("{$name}_file_size")->nullable()->index();
            $table->enum("{$name}_file_type", enum_to_names(LaruploadFileType::cases()))->nullable()->index();
            $table->string("{$name}_file_mime_type", 85)->nullable();
            $table->unsignedInteger("{$name}_file_width")->nullable();
            $table->unsignedInteger("{$name}_file_height")->nullable();
            $table->unsignedInteger("{$name}_file_duration")->nullable()->index();
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
     * @param string $name
     * @param LaruploadMode $mode
     */
    public static function dropColumns(BlueprintIlluminate $table, string $name, LaruploadMode $mode = LaruploadMode::HEAVY): void
    {
        $columns = [
            "{$name}_file_name"
        ];


        if ($mode === LaruploadMode::HEAVY) {
            $tableName = $table->getTable();
            $heavyColumns = [
                "{$name}_file_id", "{$name}_file_size", "{$name}_file_type", "{$name}_file_mime_type",
                "{$name}_file_width", "{$name}_file_height", "{$name}_file_duration",
                "{$name}_file_dominant_color", "{$name}_file_format", "{$name}_file_cover"
            ];

            if (config('larupload.store-original-file-name', false)) {
                $heavyColumns[] = "{$name}_file_original_name";

                $table->dropIndex("{$tableName}_{$name}_file_original_name_index");
            }


            $columns = array_merge($columns, $heavyColumns);

            $table->dropIndex("{$tableName}_{$name}_file_size_index");
            $table->dropIndex("{$tableName}_{$name}_file_type_index");
            $table->dropIndex("{$tableName}_{$name}_file_duration_index");
        }
        else {
            $columns[] = "{$name}_file_meta";
        }


        $table->dropColumn($columns);
    }

    /**
     * Add upload file_original_name column to the table
     *
     * @param BlueprintIlluminate $table
     * @param string $name
     * @param LaruploadMode $mode
     */
    public static function addOriginalName(BlueprintIlluminate $table, string $name): void
    {
        $table->string("{$name}_file_original_name", 85)
            ->nullable()
            ->index()
            ->after("{$name}_file_id");
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

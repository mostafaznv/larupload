<?php

if (!function_exists('enum_to_names')) {
    /**
     * Returns an array of enum names
     *
     * @param UnitEnum[] $enums
     * @return array
     */
    function enum_to_names(array $enums): array
    {
        return array_column($enums, 'name');
    }
}

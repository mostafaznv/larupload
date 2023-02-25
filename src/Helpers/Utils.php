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

if (!function_exists('disk_driver_is_local')) {
    /**
     * Check if given driver is local
     *
     * @param string $disk
     * @return bool
     */
    function disk_driver_is_local(string $disk): bool
    {
        return config("filesystems.disks.$disk.driver") == \Mostafaznv\Larupload\Larupload::LOCAL_DRIVER;
    }
}

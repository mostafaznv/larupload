# Migration

#### From 2.3.4 to 3.0.0

Starting from version 3.0.0, support for PHP 8.1 and 8.2 has been dropped, along with support for Laravel 10 and 11.

Storing original file names in the database is now the default behavior and can no longer be disabled. If you are upgrading to version 3.0.0, you must add the original file name column to all tables that use Larupload columns before performing the upgrade. You can use the [macro](../advanced-usage/migrations/add-original-file-name-to-existing-tables.md) function available in ^2.2.\* to apply this change safely.

In legacy versions, the `LARUPLOAD_NULL` constant was used to delete files. As of version 3.0.0, this is no longer supported. You should now use the [detach](../delete.md) method or assign [false](../delete.md) to your attachments.

Finally, the behavior of the FFMpeg queue has been updated. Previously, an `HttpResponseException` was thrown when the queue limit was exceeded. From version 3.0.0 onward, the package throws `FFMpegQueueMaxNumExceededException` instead.





#### From 2.1.0 to 2.2.0

Starting from version 2.2.0, we added functionality to the package to store the original file name in the database.

Since the old `name` column was used to store the manipulated file name (hashed, unique id, time, etc.), we needed a new column to store the original one.&#x20;

Adding a new column and working with it could result in a breaking change, so we decided to make it optional. In the next major release, we will make it the default behavior of the package.

To enable this feature:

1. Enable the `store-original-file-name` property in the [`config/larupload.php`](../advanced-usage/configuration/store-original-file-name.md) file. (You may need to add it to your config file from [here](https://github.com/mostafaznv/larupload/blob/b3392af87d902a133a962daf223a97a93c566482/config/config.php#L217-L227))
2. Add the `{$name}_file_original_name` column to your existing tables. (for new tables, this column will be added by default).




# Migration

#### From 2.1.0 to 2.2.0

Starting from version 2.2.0, we added functionality to the package to store the original file name in the database.

Since the old `name` column was used to store the manipulated file name (hashed, unique id, time, etc.), we needed a new column to store the original one.&#x20;

Adding a new column and working with it could result in a breaking change, so we decided to make it optional. In the next major release, we will make it the default behavior of the package.

To enable this feature:

1. Enable the `store-original-file-name` property in the [`config/larupload.php`](../advanced-usage/configuration/store-original-file-name.md) file.
2. Add the `{$name}_file_original_name` column to your existing tables. (for new tables, this column will be added by default).




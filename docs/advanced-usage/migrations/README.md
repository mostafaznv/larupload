# Migrations

To simplify the process of creating the columns required by Larupload, we have provided a database `macro` that allows you to easily add the necessary columns to the desired table in your migration.

There are two modes available for creating columns in the table: `HEAVY` and `LIGHT`.&#x20;

The main difference between these two modes is in the number of table columns and the way the data is stored.

In `HEAVY` mode, a separate column is created for each field, and each data item is stored in its own column. This mode is useful when you need to use special queries on the table or use it to sort your data.

On the other hand, in `LIGHT` mode, only the filename is stored in its own column, while other file information is stored in a `JSON`/`String` column named `meta`. This mode is useful when you want to record or display your data.


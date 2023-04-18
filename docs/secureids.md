# SecureIds

Larupload's SecurIds feature provides an added layer of security and privacy to your uploaded files by using a different ID format in the file upload path instead of the real ID of model records. This feature is especially useful for situations where you don't want to expose the real IDs of your records, making them less discoverable and more secure.

The SecurIds feature supports several different ID formats, including:

* ULID
* UUID
* HASHID (requires the [hashids](https://github.com/vinkla/hashids) package to be installed)
* NONE (uses real IDs and is the default setting)

This flexibility allows you to choose the format that best suits your needs, and easily switch between formats if necessary.




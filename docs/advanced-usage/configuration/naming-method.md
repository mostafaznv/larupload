---
description: 'Default: HASH_FILE'
---

# Naming Method

With this feature, you can specify the naming method for files as follows:

* **SLUG**: The name of the uploaded file is converted into a slug. To prevent the file from caching in different clients, a random number is always added to the end of the filename.
* **HASH\_FILE**: Using the MD5 algorithm, the hash of the uploaded file is used as the filename.
* **TIME**: The upload time is used as the uploaded file name.




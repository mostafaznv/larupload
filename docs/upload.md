# Upload

There are three ways to upload files using Larupload:

* Upload by mutator
* Upload by the `attach` function
* Upload using the `create` method of the model



{% tabs %}
{% tab title="Mutator" %}
This is the easiest way to upload a file. You can upload a file by assigning the file to a property of your model.&#x20;

It is advising that when uploading a file using Larupload, the corresponding property must be defined in the `attachments` method of the model.

```php
$upload = new Upload;
$upload->file = $request->file('file');
$upload->save();
```
{% endtab %}

{% tab title="Attach (IDE Friendly)" %}
With the `attach` function, you can upload both the file and its cover image (if applicable).

Here are the arguments that the `attach` function accepts:

* The first argument is the file that you want to upload (_required_).
* The second argument is the cover image for the file (_optional_).

```php
$file = $request->file('file');
$cover = $request->file('cover');
   
$upload->file->attach($file, $cover);
// or (recommended)
$upload->attachment('file')->attach($file, $cover);

$upload->save();
```

{% hint style="info" %}
If you provide a cover file when calling the `attach` function, the package will prioritize using your uploaded file as the cover instead of automatically generating one.
{% endhint %}
{% endtab %}

{% tab title="Create" %}
In this method, you can create and upload a file in one line of code. All you need to do is to pass the file and any required information to the `create` method of your model.

For example, if you have an `Upload` model that has an attachment named `file`, you can create and upload a file by writing the following code:\


```php
$upload = Upload::create([
    'file' => $request->file('file')
]);
```
{% endtab %}
{% endtabs %}




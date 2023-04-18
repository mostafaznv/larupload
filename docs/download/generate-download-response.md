# Generate Download Response

The `download` method in Larupload is a convenient way to serve files from your web server. This method generates a HTTP download response for a particular attachment and style, making it easy for users to download the file to their local machine.

To use the `download` method, you simply call it on an attachment instance with the desired style, and it will return a HTTP response object that you can return from your controller action. The response object includes the file contents, along with headers to specify the filename and content type of the file.



{% tabs %}
{% tab title="Download Original File" %}
```php
$user->attachment('avatar')->download();
```
{% endtab %}

{% tab title="Download Particular Style" %}
```php
$user->attachment('avatar')->download('thumbnail');
```
{% endtab %}
{% endtabs %}

{% hint style="info" %}
It's not recommended to use the `download` function to serve large files directly through your web server, as this can slow down your application and consume server resources. Instead, it's better to use a technique called X-Sendfile, which allows you to offload the file serving to your web server or a specialized file server.

I have developed a package that you can use to serve large files efficiently through your web-server. You can find the package here:&#x20;

[https://github.com/mostafaznv/php-x-sendfile](https://github.com/mostafaznv/php-x-sendfile)
{% endhint %}




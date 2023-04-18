# Download

Larupload provides a convenient feature to generate download links for all styles of an attachment. This feature is particularly useful when you need to provide a way for users to download the files that you have uploaded. Once you have uploaded a file, you can use the `url()` method to generate a download link for that file. This method takes an optional style name as its argument and returns a URL that can be used to download the specified style of the attachment.

In addition to generating download links, Larupload also provides a built-in functions to respond with an HTTP download response. You can use the `download()` method to create a download response for an attachment. This method takes an optional style name as its argument and returns a Symfony HttpFoundation `StreamedResponse` object that can be used to stream the attachment to the client. This makes it easy to provide a way for users to download files directly from your web-server.

{% hint style="info" %}
It's not recommended to use the `download` function to serve large files directly through your web server, as this can slow down your application and consume server resources. Instead, it's better to use a technique called X-Sendfile, which allows you to offload the file serving to your web server, making the process more efficient and faster.

I have developed a package that you can use to serve large files efficiently through your web-server. You can find the package here:&#x20;

[https://github.com/mostafaznv/php-x-sendfile](https://github.com/mostafaznv/php-x-sendfile)
{% endhint %}

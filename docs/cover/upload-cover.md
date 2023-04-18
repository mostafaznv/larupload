# Upload Cover

In Larupload, covers are associated with the original files and must be uploaded using the `attach()` function. When uploading a file, you can also include a cover as the second argument. If a cover is provided, it will be assigned to the uploaded file and the [automatic cover creation](#user-content-fn-1)[^1] by the package will be prevented.

<pre class="language-php"><code class="lang-php">$file = $request->file('file');
$cover = $request->file('cover');

<a data-footnote-ref href="#user-content-fn-2">$upload->attachment('file')->attach($file, $cover);</a>
$upload->save();
</code></pre>





[^1]: it's only available for image and videos

[^2]: ```php
    # or
    $upload->file->attach($file, $cover);
    ```

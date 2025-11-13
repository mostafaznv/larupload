# Customization

Larupload's standalone uploader provides the flexibility to customize the upload process by allowing users to configure various aspects of the uploader.&#x20;

Some of the customization options available in the standalone uploader are:

1. **Adding custom styles**: Users can define their own styles to be used for the uploaded files. They can define multiple styles with different dimensions and formats to suit their needs.
2. **Changing disk**: By default, the standalone uploader uses the pre-defined disk from `config/larupload.php` to store the uploaded files. However, users can choose to use a different disk by specifying the disk name in the configuration.
3. **Changing naming-method**: Users can customize the naming method for the uploaded files by defining their own naming convention.&#x20;
4. ...

Overall, these customization options make the standalone uploader highly flexible and adaptable to different use cases. Users can easily tailor the uploader to their specific needs, ensuring that their uploaded files are handled exactly as they want.

{% code title="Basic Example" %}
```php
$file = $request->file('file');
$cover = $request->file('cover');

$upload = Larupload::init('path')
            ->disk('s3')
            ->namingMethod(LaruploadNamingMethod::HASH_FILE)
            ->image('thumbnail', 1000, 750, LaruploadMediaStyle::CROP)
            ->video('thumbnail', 1000, 750, LaruploadMediaStyle::CROP)
            ->audio('wav', new Wav())
            ->stream(
                name: '480p',
                width: 640,
                height: 480,
                format: (new X264)
                    ->setKiloBitrate(3000)
                    ->setAudioKiloBitrate(64)
                )
            )
            ->upload($file, $cover);
```
{% endcode %}

# Cover

Larupload allows you to upload cover files for your attachments. A cover is a visual representation of an attachment, which can be useful for displaying a thumbnail or preview image, and can be used to represent the attachment in various contexts, such as on a web page or in a mobile app.

With Larupload, you can assign a cover to an attachment by using the `attach()` method and passing the cover file as the second argument. If you don't specify a cover file, Larupload will automatically generate one based on the original file (image/video).

Additionally, you can also update the cover for an attachment at any time by using the `cover()->update()` method and passing the new cover file as the argument. Similarly, you can delete the cover for an attachment using the `cover()->detach()` method.




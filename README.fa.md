![larupload-banner](https://user-images.githubusercontent.com/7619687/53000850-837af180-343e-11e9-90a1-c30ff435b0b1.png)


# ادامه مطلب
**لارآپلود** به شما کمک میکند که همه فایل های خود را به راحتی آپلود کنید. علاوه بر آپلود فایل، لارآپلود امکانات جالبی را برای آپلود ویدیو، صدا و عکس دارد.
در لارآپلود سعی کردیم که از [فایل سیستم](https://laravel.com/docs/filesystem) لاراول استفاده کنیم.
به لطف استفاده از فایل سیستم لاراول، به راحتی امکان سوییچ کردن به «هر» درایور دلخواهی (همچون اس اف تی پی، لوکال، اس۳ و ...) وجود دارد.


## برخی از امکانات لارآپلود
- استفاده از درایورهای مختلف
- امکان کراپ/ریساز کردن عکس و فیلم
- ایجاد چندین سایز مختلف از فیلم و عکس
- ایجاد ویدیوهای استریم شده از سورس اصلی ویدیو
- استخراج عرض و ارتفاع از عکس
- استخراج عرض، ارتفاع و مدت زمان ویدیو
- استخراج مدت زمان صدا
- استخراج رنگ غالب عکس و ویدیو
- ایجاد اتوماتیک عکس کاور برای فایل های ویدیویی
- امکان آپلود کاور برای هر فایل دلخواه
- فانکشن اختصاصی برای ایجاد ستون های دیتابیس هنگام اجرای مایگریشن
- گرفتن آدرس فایل آپلود شده به صورت تکی یا به صورت مجموعه «استایل های تعریف شده»
- نام گذاری فایل ها به چند روش مختلف 
- پشتیبانی از زبان فارسی و عربی برای نامگذاری فایل ها
- اعتبارسنجی فایل های ورودی به وسیله فرمت فایل و نوع فایل
- امکان تعیین تنظیمات به صورت عمومی و یا به صورت خصوصی برای هر مدل
- دارای ۲ حالت برای ذخیره سازی. حالت کامل و حالت سبک 
- قابلیت انجام پردازش های مربوط به «اف اف ام پگ» در پس زمینه


## پیش نیازها
- لاراول ۵.۵ یا بالاتر
- کتابخانه GD
- FFMPEG 


## مراحل نصب

1. ##### نصب کردن پکیج به وسیله کامپوزر
    ```
    composer require mostafaznv/larupload
    ```

2. ##### انتشار فایل تنظیمات و جداول
    ```
    php artisan vendor:publish --provider="Mostafaznv\Larupload\LaruploadServiceProvider"
    ```

3. ##### ایجاد جدول ها:
    ```shell
    php artisan migrate
    ```

4. ##### تمام



## روش استفاده

1. ##### اضافه کردن ستون های مربوطه به جدول دلخواه
    ```php
    <?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class Uploads extends Migration
    {
        public function up()
        {
            Schema::create('uploads', function (Blueprint $table) {
                $table->increments('id');
                $table->upload('file'); // or $table->upload('file', 'heavy');
                $table->timestamps();
            });
        }
    
        public function down()
        {
            Schema::dropIfExists('uploads');
        }
    }
    ```

2. ##### اضافه کردن تریت لارآپلود به مدل
    ```php
    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use Mostafaznv\Larupload\Traits\Larupload;
    
    class Upload extends Model
    {
        use Larupload;
    
        public function __construct(array $attributes = [])
        {
            parent::__construct($attributes);
    
            $this->hasUploadFile('file');
        }
    }
    ```

3. ##### آپلود فایل
    ```php
    $upload = new Upload;
    $upload->file = $request->file;
    $upload->save();
    ```


## پیش قرارداد های استفاده از پکیج
> - تمامی فایل ها در قالب استایل اوریجینال آپلود میشوند. شما می توانید سایر استایل های خود را اضافه کنید اما فایل اصلی در قالب اوریجینال آپلود میشود.
> - اطلاعات اضافه مثل اسم، سایز، فرمت و ... در دیتابیس ذخیره میشوند، مگر اینکه امکان ذخیره نشدنشان در فایل کانفیگ وجود داشته باشد.


## فهرست مطالبی که در ادامه آموزش داده میشوند
- ایجاد ستون در جدول به کمک مایگریشن
  - ایجاد ستون برای حالت کامل
  - ایجاد ستون برای حالت سبک
- روش های آپلود فایل
  - آپلود به وسیله Accessor
  - آپلود به وسیله تابع
  - پاک کردن فایل موجود و همچین مقدار موجود در دیتابیس
- تولید لینک دانلود
- دریافت اطلاعات اضافه (متا)
- شخصی سازی
    - شخصی سازی به وسیله فایل کانفیگ
    - شخصی سازی به وسیله سازنده مدل
- صف بندی پردازش ویدیو
  - گوش دادن به رویداد
  - ریلیشنشیپ ها
- ترفندهای اضافه
  - ست کردن صفت
  - گرفتن صفت

## ایجاد ستون در جدول به کمک مایگریشن
برای ایجاد سهولت در ساختن ستون های مورد نیاز لارآپلود، با کمک گرفتن از قابلیت ماکرو امکانی را فراهم کردیم که به راحتی بتوانید ستونهای مورد نیاز خود را در جدول مورد نظر ایجاد کنید:

1. ### ایجاد ستون برای حالت کامل
    ```php
    Schema::create('uploads', function (Blueprint $table) {
        $table->increments('id');
        $table->upload('file');
        $table->timestamps();
    });
    ```

2. ### ایجاد ستون برای حالت سبک
    ```php
    Schema::create('uploads', function (Blueprint $table) {
        $table->increments('id');
        $table->upload('file', 'light');
        $table->timestamps();
    });
    ```

تفاوت بین حالت های کامل و سبک در تعداد ستون های ایجاد شده در جدول و نحوه ذخیره سازی آنهاست.
در حالت سنگین برای تمامی فیلدها ستون مجزا ساخته میشود و تمامی داده ها در ستون های مربوط به خود ذخیره میشوند. این حالت برای زمانی مفید است که قصد دارید روی داده های جدول کوئری های خاصی بزنید و یا در مرتب سازی داده های خود از آنها استفاده کنید.
اما در حالت سبک فقط نام فایل است که در ستون مربوط به خود ذخیره میشود و سایر اطلاعات مربوط به فایل در یک ستون جیسون/متن تحت عنوان متا ذخیره میشود و به جهت ثبت و نمایش مفید است


## روش های آپلود فایل
برای آپلود کردن یک فایل میتوانید از روش های مختلفی استفاده کنید:

1. ### آپلود به وسیله Accessor
    ```php
    $upload->file = $request->file;
    ```

2. ### آپلود به وسیله تابع

    به کمک تابع setUploadedFile میتوانید فایل و در صورت نیاز کاور فایل را آپلود کنید.

    **توضیح آرگومان های ورودی تابع**
    - اول: نام ستون مربوط به فایل در جدول - فیلد ضروری
    - دوم: فایل اصلی آپلود - فیلد ضروری
    - سوم: فایل کاور (اختیاری).
    
    > در صورتی که فایل کاور را ارسال کنید، اولویت ایجاد کاور با فایل ارسال شده شماست و از ایجاد اتوماتیک کاور توسط پکیج جلوگیری میکند.

    ```php
    $upload->setUploadedFile('file', $request->file, $request->cover);
    ```

3. ### پاک کردن فایل موجود و همچین مقدار موجود در دیتابیس

    در صورتی که بخواهید فایل موجود را پاک کنید، میتوانید از روش زیر استفاده کنید

    ```php
    $upload->file = LARUPLOAD_NULL;
    ```


## تولید لینک دانلود
برای دسترسی به لینک فایل آپلود شده میتوانید از روش های زیر استفاده کنید

1. ### دریافت لینک تمام استایل ها
    کد:
    ```php
    dd($upload->file);
    ```
    
    خروجی:
    ```php
    array:3 [▼
      "original" => "http://larupload.site/uploads/uploads/1/file/original/image.png"
      "cover" => " http://larupload.site/uploads/uploads/1/file/cover/image.jpg"
      "thumbnail" => " http://larupload.site/uploads/uploads/1/file/thumbnail/image.png"
    ]
    ```

2. ### دریافت لینک برای یک استایل خاص
    ```php
    echo $upload->file['thumbnail'];
    // or
    echo $upload->url('file', 'thumbnail');
    ```
    > در صورتی که آرگومان دوم را ارسال نکنید، به صورت اتوماتیک لینک مربوط به فایل اصلی (اوریجینال) باز گردانده میشود
    
    > به جای تابع url میتوانید از laruploadUrl هم استفاده کنید


## دریافت اطلاعات اضافه (متا)
آرگومان اول نام فایل و آرگومان دوم نام متای مورد نظر.

1. ### دریافت تمام اطلاعات اضافه مربوط به فایل

    کد:
    ```php
    dd($upload->meta('file'));
    ```
    
    خروجی:
    ```php
    array:9 [▼
      "name"           => "64d65a4e98029c37e7fd510c6e0a34d6.png"
      "size"           => 93366
      "type"           => "image"
      "mime_type"      => "image/png"
      "cover"          => "64d65a4e98029c37e7fd510c6e0a34d6.jpg"
      "width"          => 2560
      "format"         => "png"
      "height"         => 1600
      "duration"       => null
      "dominant_color" => "#c5ae0a"
    ]
    ```
    
    > **تایپ**: نوع فایل به صورت قابل خواندن برای انسان را باز میگرداند: <br>`image`, `video`, `audio`, `pdf`, `compressed`, `file`

2. ### دریافت متا به وسیله نام آن

    کد:
    ```php
    echo $upload->meta('file', 'dominant_color');
    ```
    خروجی:
    ```php
    #c5ae0a
    ```


## شخصی سازی
در لارآپلود تلاش زیادی برای فراهم آوردن امکان شخصی سازی هرچه بیشتر عملکرد پکیج شده است.

به همین منظور میتوانید به ۲ روش عملکرد پکیج را شخصی سازی کنید.

1. به کمک فایل کانفیگ
2. به کمک مدل


### شخصی سازی به وسیله فایل کانفیگ
##### Storage

به کمک این قابلیت میتوانید تعیین کنید که فایل شما به کمک چه درایوری آپلود شود

درایور هایی که تا کنون پشتیبانی میشوند:

- لوکال
- پابلیک
- اس اف تی پی (فقط اس اف تی پی پشتیبانی میشود و برنامه ای برای پشتیبانی اف تی پی نداریم)
- اس۳ (تست نشده و نیاز به تست دارد. اما به نظر میرسد که مشکل خاصی برای اجرا نداشته باشد)



##### Mode

لارآپلود به ۲ روش اطلاعات مربوط به فایل آپلود شده را ذخیره میکند:

Heavy و Light

- حالت کامل تمام اطلاعات و جزئیات فایل را در ستون های خاص خودش ذخیره میکند.
- حالت سبک اطلاعات اضافه را به صورت جیسون در یک ستون به نام متا ذخیره میکند

> توجه کنید که انتخاب هر کدام از این حالات باید با نوع جدول ایجاد شده همخوانی داشته باشد.


#### Naming Method
با استفاده از این قابلیت میتوانید روش نامگذاری فایل ها را به صورت زیر مشخص کنید

- slug
نام فایل آپلود شده به اسلاگ تبدیل میشود. برای جلوگیری از کش شدن فایل در کلاینت های مختلف، همواره یک عدد رندوم به آخر نام فایل اضافه میشود.

- md5 hash_file 
به کمک الگوریتم ام دی۵، هش فایل آپلود شده به عنوان نام فایل انتخاب میشود.

- time
زمان آپلود به عنوان نام فایل آپلود شده انتخاب میشود.


#### Lang
از این قابلیت برای نامگذاری فایل ها به هنگام استفاده از الگوی slug استفاده میشود.  در صورت خالی گذاشتن این بخش، از زبان اپلیکیشن (موجود در فایل config/app.php) استفاده میکنیم.


#### Image Processing Library
میتوانید مشخص کنید که لارآپلود به وسیله کدام کتابخانه پردازش تصویر، عملیات کراپ و ریسایز را انجام دهد.
گزینه های قابل تنظیم:

- Imagine\Gd\Imagine
- Imagine\Imagick\Imagine
- Imagine\Gmagick\Imagine


#### Styles
از این بخش برای تعریف کردن استایل های مختلف بر روی ویدیو و عکس ها استفاده میشود. به کمک این قابلیت میتوانید به هر تعداد دلخواهی کپی های مختلف از نسخه اصلی فایل ایجاد کنید و آنها را کراپ یا ریسایز کنید
نمونه استایل:
```php
'styles' => [
    'thumbnail' => [
        'height' => 500,
        'width'  => 500,
        'mode'   => 'crop',
        'type'   => ['image', 'video'], 
    ],
    
    'medium' => [
        'height' => 1000,
        'width'  => 1000,
        'mode'   => 'auto',
        'type'   => ['image']
    ]
      
    'stream' => [
        '480p' => [
            'width'   => 640,
            'height'  => 480,
            'bitrate' => [
                'audio' => '64k',
                'video' => '300000'
            ]
        ],

        '720p' => [
            'width'   => 1280,
            'height'  => 720,
            'bitrate' => [
                'audio' => '64k',
                'video' => '400000'
            ]
        ],

        '1080p' => [
            'width'   => 1920,
            'height'  => 1080,
            'bitrate' => [
                'audio' => '64k',
                'video' => '500000'
            ]
        ]
    ]
]
```
توضیحات آیتم های استایل:
- height

ارتفاع عکس یا ویدیو. مقدار ارتفاع باید به صورت عددی باشد

- width

عرض عکس یا ویدیو. مقدار عرض باید به صورت عددی باشد

- mode

لارآپلود به کمک این فیلد تصمیم میگیرد که با ویدیو یا عکس آپلود شده چگونه برخورد کند. مقادیر قابل قبول برای این بخش:

landscape, portrait, crop, exact, auto

- type

به کمک این فیلد میتوانید مشخص کنید که استایل تعریف شده قابل استفاده برای چه نوع فایلهایی است.

مقادیر قابل قبول:

Image 

video 

هردو

- stream

    - **stream**: اگرا میخواهید از ویدیوهای خود فایل استریم بسازید باید و حتما از این مقدار استفاده کنید. فعلا قابلیت استریم کردن فقط برای استایل `استریم` وجود دارد
    - **key**: نام کیفیت ویدیو را به وسیله این قسمت مشخص میکنید. قویا پیشنهاد میشود که نامگذاری ها به صورت رشته باشد
    - **width**: عرض ویدیو. عرض باید یک مقدار عددی باشد 
    - **height**: طول ویدیو. طول باید یک مقدار عددی باشد
        - **bitrate.audio**: بیت ریت صدا. بیت ریت باید یک مقدار قابل قبول برای دستورات اف‌اف‌ام‌پگ باشد
        - **bitrate.video**: بیت ریت ویدیو. این مقدار باید یک مقدار عددی باشد.
        
    > توجه: استریم فقط برای ویدیو ها کار میکند و میتواند از ویدیو فایل عکس کاور ایجاد کند.
        
    > توجه: تمام کیفیت های تبدیل شده بعد از تبدیل شدن به تی‌اس حذف میشوند. از این رو امکان استفاده از ورژن ام‌پی‌۴ کیفیت ها وجود ندارد. ام‌پی۴ فقط برای فایل اصلی (اوریجینال) موجود است


#### Generate Cover
لارآپلود به شما این امکان را میدهد که به صورت اتوماتیک از عکس و ویدیوی آپلود شده تصویر کاور استخراج کنید. به کمک این فیلد می توانید این قابلیت را فعال یا غیر فعال کنید.

#### Cover Style
به کمک تنظیمات این بخش میتوانید خواص کاور ایجاد شده را مدیریت کنید.
```php
'cover_style' => [
    'height' => 500,
    'width'  => 500,
    'mode'   => 'crop'
]
```


#### Dominant Color
به کمک این قابلیت می توانید رنگ غالب عکس یا ویدیو را استخراج کنید.

> توجه کنید که اگر قابلیت ایجاد کاور را غیرفعال کنید، استخراج رنگ از ویدیو به صورت اتوماتیک غیر فعال میشود.


#### Keep Old Files
با فعال کردن این قابلیت از حذف شدن فایل های قدیمی به هنگام آپدیت شدن رکورد دیتابیس جلوگیری کنید.


#### Preserve Files Flag
با فعال کردن این قابلیت از حذف شدن فایل های قدیمی به هنگام حذف شدن رکورد دیتابیس جلوگیری کنید.


#### Upload Path
آدرس مکانی است که می خواهید فایل های شما در آن آپلود شود. این آدرس به صورت relative از root درایور فایل سیستم شما استفاده میکند


#### Allowed Mime Types
به کمک این فیلد و با استفاده از نوع فایل میتوانید فایل های ورودی را اعتبار سنجی کنید

مثال:

video/mp4

#### Allowed Mimes
به کمک این فیلد و با استفاده از فرمت فایل میتوانید فایل های ورودی را اعتبار سنجی کنید

مثال: mp4


#### FFMPEG
در صورتی که این بخش را خالی نگهدارید، لارآپلود تلاش میکند که به کمک environment سیستم عامل مسیر FFMPEG را پیدا کند. اما شما میتوانید مسیر FFMPEG را به صورت دستی و از این طریق مشخص کنید

مثال: mp4

```php
'ffmpeg' => [
    'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
    'ffprobe.binaries' => '/usr/local/bin/ffprobe'
],
```

- #### FFMPEG Timeout
    به وسیله این قابلیت میتوانید ماکزیمم زمان اجرای دستورات اف‌اف‌ام‌پگ را تنظیم کنید
    
    مثال:
    ```php
    'ffmpeg-timeout' => 500
    ```
    
- #### FFMPEG Queue
    بعضی اوقات پردازش ویدیوها به قدری سنگین است که شما نیازمندید که آنها را در پس زمینه انجام دهید.
    
    مثال:
    ```php
    'ffmpeg-queue' => false
    ```
    
- #### Number of max available FFMPEG Queues
    تنظیم کردن تعداد پردازشهای همزمان در پس زمینه
    > اگر شما از حد مجاز تعیین شده عبور کنید، پکیج به شما پیفام خطا نشان میدهد.
    
    Example:
    ```php
    'ffmpeg-max-queue-num' => 1
    ```

## شخصی سازی به سازنده مدل
علاوه بر استفاده از فایل کانفیگ که وظیفه تنظیم عمومی تنظیمات لارآپلود را بر عهده دارد، می توانید به کمک سازنده مدل هر مدل را به صورت اختصاصی نیز شخصی سازی کنید.
 
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Upload extends Model
{
    use Larupload;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('file', [
            'naming_method'  => 'slug',
            'keep_old_files' => false,
            'styles'         => [
                'small' => [
                    'width'  => 800,
                    'height' => 800,
                    'mode'   => 'crop',
                    'type'   => ['image', 'video']
                ],

                'thumbnail' => [
                    'width'  => 250,
                    'height' => 250,
                    'mode'   => 'auto',
                    'type'   => ['video']
                ]
            ],
        ]);
    }
}
```

## صف بندی اف اف ام پگ
> توجه: اگر شما در حال خواندن این بخش هستید، ما فرض میکنیم که با  [صف در لاراول](https://laravel.com/docs/queues) آشنا هستید. اگر چنین نیست، لطفا ابتدا داکیومنت لاراول را مطالعه کنید.

شما میتوانید این قابلیت را از طریق تنظیمات فعال کنید.
در این صورت به هنگام اپلود ویدیوها، لارآپلود تنها نسخه اصلی را آپلود کرده و انجام پردازش روی استایل های مختلف (مثل کراپ، ریزایز و استریم) را در پس زمینه انجام خواهد داد.


#### به همراه صف بندی پردازش های ویدیو، ۲ قابلیت جدید رای شما ایجاد کرده ایم:
- فراخوانی یک رویداد به جهت اطلاعرسانی پایان پردازش
- ۲ ریلیشنشیب برای نشاند دادن وضعیت فعلی پردازش و همچین تاریخچه تمام پردازش ها

#### FFMpeg Queue Finished Event
 بعد از اتمام پردازش، به کمک یک رویداد به شما اطلاع میدهیم که پردازش مورد نظر تمام شده است. به وسیله قدم های زیر میتوانید رویداد مورد نظر را دریافت کنید
 
1. ##### ایجاد listener
    ```php
    php artisan make:event LaruploadFFMpegQueueFinished 
    ```

2. ##### ثبت لیسنر
    باید لیسنر را در کلاس EventServiceProvider معرفی کنید
    ```php
    protected $listen = [
        ...

        'Mostafaznv\Larupload\Events\LaruploadFFMpegQueueFinished' => [
            'App\Listeners\LaruploadFFMpegQueueNotification',
        ],
    ];
    ```

2. ##### دریافت رویداد
    در لیسنر ایجاد شده به کمک کد زیر میتوان رویداد مورد نظر را دریافت کرد.
    ```php
    class LaruploadFFMpegQueueNotification
    {
        public function handle(LaruploadFFMpegQueueFinished $event)
        {
            info("larupload queue finished. id: $event->id, model: $event->model, statusId: $event->statusId");
        }
    }
    ```

#### ریلیشنشیپ ها
در تمامی مدل هایی که از لارآپلود استفاده میکنند، ریلیشنشیپ های زیر قابل دسترس هستند.

- **laruploadQueue**: نماش وضعیت آخرین پردازش انجام شده
- **laruploadQueues**: نمایش تاریخچه تمامی پردازش ها

```php
use App/Upload;

Upload::where('id', 21)->with('laruploadQueue', 'laruploadQueues')->first();
```

## ترفندهای اضافه

### Set Attribute
گاهی اوقات می خواهید که اطلاعات اضافه تری را به دیتابیس خود وارد کنید. باید توجه کنید که حتما باید تابع راه انداز لارآپلود را هم فراخوانی کنید.
 
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Upload extends Model
{
    use Larupload;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('file');
    }
    
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            if ($value) {
                $attachedFile = $this->attachedFiles[$key];
                $attachedFile->setUploadedFile($value);
    
                $this->attributes['your_own_attribute'] = 'value';
            }
    
            return;
        }
    
        parent::setAttribute($key, $value);
    }
}
``` 

### Get Attribute
در صورتی که بخواهید رکورد را به آرایه تبدیل کنید یا اینکه مقدار رکورد را در یک ای‌پی‌آی بازگردانید، میتوانید از روش های زیر استفاده کنید

##### برگرداندن تمام آدرس ها برای تمام فایل های موجود در مدل

کد:
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Contact extends Model
{
    use Larupload;

    protected $table = 'contacts';
    protected $appends = ['media'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('image');
        $this->hasUploadFile('profile_cover');
    }

    public function getMediaAttribute()
    {
        return $this->getFiles();
    }
}

``` 

نتیجه:
```php
"media" => array:1 [▼
    "image" => array:3 [▼
        "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
        "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
        "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    ],
    "profile_cover" => array:3 [▼
        "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
        "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
        "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    ]
]
```

##### برگرداندن تمام آدرس ها فقط برای یکی از فایل های موجود در مدل

کد:
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Contact extends Model
{
    use Larupload;

    protected $table = 'contacts';
    protected $appends = ['image'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('image');
        $this->hasUploadFile('profile_cover');
    }

    public function getImageAttribute()
    {
        return $this->getFiles('image');
    }
}

``` 

نتیجه:
```php
"image" => array:3 [▼
    "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
    "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
]
```


## توسعه دهنگان
- مصطفی زینی وند -  [@mostafaznv](https://github.com/mostafaznv)
- فائزه قربان نژاد - [@Ghorbannezhad](https://github.com/Ghorbannezhad)
- تیم سمسون اپز [@SamssonApps](https://github.com/SamssonApps)


## لیست تغییرات

به [لیست تغییرات](CHANGELOG.md) مراجعه کنید تا از لیست تفییرات هر نسخه با خبر شوید.

## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2018 Mostafaznv, Some rights reserved.

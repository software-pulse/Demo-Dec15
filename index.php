<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Web Banner</title>
    <style>
        .banner-container {
            text-align: center;
        }

        .banner-image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <h1>PHP Web App</h1>

    <div class="banner-container">
        <?php
        // Replace 'path/to/your/banner.jpg' with the actual path or URL of your banner image.
        $bannerImagePath = 'https://www.ranosys.com/adobe/dynamicmedia/deliver/dm-aid--4351ab77-bdff-401f-9b3e-fe7ba15b5244/phpbanner-1920.png?quality=85&width=1600&preferwebp=true';
        ?>

        <img class="banner-image" src="<?php echo $bannerImagePath; ?>" alt="Web Banner">
    </div>

</body>
</html>

<?php
echo "Hello, SWD!";
echo "&nbsp;This is for PHP Test.";
echo "&nbsp;Thank you!";
echo "&nbsp;We will see you again...";
?>

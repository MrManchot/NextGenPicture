<?php

// Meta description +  Github
// README : sudo apt install imagemagick jpegoptim pngquant webp
// Packagist

?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>NextGenPicture</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>xmp { padding:20px; background:#eee; }</style>
</head>

<body>

  <?php

  ini_set('display_errors', 1);
  require '../src/NextGenPicture.php';

  # Minimal Configuration
  // echo (new NextGenPicture())->load('sample.jpg')->display();

  # All Options
  $ngp = new NextGenPicture([
    'relative_path' => '../cache/',
    'cache_dir' => __DIR__ . '/../cache/',
    'force_generate' => false,
    'compatibility' => true,
    'quality' => 90,
    'max_pixel_ratio' => 3
  ]);

  $html = $ngp->load('sample.jpg')
    ->setMaxDisplaySize([800, 600])
    ->setResponsive([1024 => 375])
    ->setAlt('Alt of the picture')
    ->setId('picture-id')
    ->setClass(['picture-first-class', 'picture-second-class'])
    ->setAttributes(['data-first' => 'toto', 'data-second' => 'tata'])
    ->display();

  echo $html . '<xmp>' . $html . '</xmp>';

  ?>

  <footer>
    Photo by <a href="https://curioso.photography/">Curioso Photography</a> from <a href="https://www.pexels.com">Pexels</a>
  </footer>
</body>

</html>
# NextGenPicture

Generate your images in [WebP](https://developers.google.com/speed/webp) format in different sizes plus the appropriate HTML tag.

Manage: media requests, device pixel ratio and 100% compatibility.

## Installation

For your server (command for Ubuntu) :

```bash
sudo apt install imagemagick webp
```
Optional :

```bash
sudo apt install jpegoptim pngquant 
```

Install NextGenPicture with composer :

```bash
composer require cmouleyre/next-gen-picture
```

## Usage

```php
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
```php

Result :

```html
<picture>
  <source type="image/webp" media="(max-width: 1024px)" srcset="../cache/a6a5ac1817569518be673efdc2ac46f0_375.webp 1x, ../cache/a6a5ac1817569518be673efdc2ac46f0_750.webp 2x, ../cache/a6a5ac1817569518be673efdc2ac46f0_1125.webp 3x">
  <source type="image/webp" srcset="../cache/a6a5ac1817569518be673efdc2ac46f0_800.webp">
  <source media="(max-width: 1024px)" srcset="../cache/a6a5ac1817569518be673efdc2ac46f0_375.jpg 1x, ../cache/a6a5ac1817569518be673efdc2ac46f0_750.jpg 2x, ../cache/a6a5ac1817569518be673efdc2ac46f0_1125.jpg 3x">
  <source srcset="../cache/a6a5ac1817569518be673efdc2ac46f0_800.jpg">
  <img src="../cache/a6a5ac1817569518be673efdc2ac46f0_800.jpg" class="picture-first-class picture-second-class" id="picture-id" alt="Alt of the picture" data-first="toto" data-second="tata" >
</picture>
```html

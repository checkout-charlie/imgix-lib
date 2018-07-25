[![Build Status](https://travis-ci.org/sparwelt/imgix-lib.svg?branch=master)](https://travis-ci.org/sparwelt/imgix-lib)
[![Coverage Status](https://coveralls.io/repos/github/sparwelt/imgix-lib/badge.svg?branch=master)](https://coveralls.io/github/sparwelt/imgix-lib?branch=master)

Imgix Library
===================
## Installation
```bash
composer require sparwelt/imgix-lib
```

## Usage
### Basic usage
```php
// initialization
$cdnConfiguration = ['my_cdn' => ['my.imgix.net']];
$imgix = ImgixServiceFactory::createFromConfiguration($cdnConfiguration);

// url generation
echo $imgix->generateUrl('/dir/test.png', ['w' => 100, 'h' => 200]);
// "https://my.imgix.net/dir/test.png?w=100&h=200"

// image generation
echo $imgix->generateImage('/dir/test.png', ['src => ['w' => 100, 'h' => 200]]);
// <img src="https://my.imgix.net/dir/test.png?w=100&h=200">

// html conversion
echo $imgix->convertHtml('<li><img src="/test.png"><\li><li><img src="/test2.png">', ['src => ['w' => 100, 'h' => 200]]);
// '<li><img src="https://my.imgix.net/test.png"><\li><li><img src="https://my.imgix.net/test2.png">'
```

### Responsive usage
TBC

# Imager Pretransform plugin for Craft CMS 3.x

Pretransform any Assets on save, with Imager

![Screenshot](resources/icon.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require superbig/craft-imagerpretransform

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Imager Pretransform.

## Imager Pretransform Overview

If you have suffered from memory or execution time issues when creating many transforms on demand, you should look into pre-generating transforms per image.

This plugin, combined with a long cache duration time, will make sure transforms will only be generated once, and one at a time.

When users upload an Asset, a task will be created, which in turn will use Imager to pre-generate the transform(s).

## Configuring Imager Pretransform

You can either have a set of transforms per Asset source handle:

```php
<?php
return [
    'transforms' => [
        // Images source, with handle images
        'images' => [
            [
                'width' => 1400,
            ],
            [
                'width'       => 600,
                'jpegQuality' => 65
            ],
            [
                'width'       => 380,
                'height'      => 380,
                'mode'        => 'crop',
                'position'    => 'center-center',
                'jpegQuality' => 65
            ],

            'defaults' => [

            ],

            'configOverrides' => [
                'resizeFilter'         => 'catrom',
                'instanceReuseEnabled' => true,
            ]
        ],

        'anotherSourceHandle' => [
            [
                'height' => 600
            ]
        ]
    ]
];
```

Or just a set of transforms that will be applied to all Assets on upload/save:

```php
<?php
return [
    'transforms' => [
        [
            'width' => 1400,
        ],
        [
            'width'       => 600,
            'jpegQuality' => 65
        ],
        [
            'width'       => 380,
            'height'      => 380,
            'mode'        => 'crop',
            'position'    => 'center-center',
            'jpegQuality' => 65
        ],

        'defaults' => [],
        'configOverrides' => []
    ]
];
```

You should also set Imager's cache duration to a long time, say 1 year:

In imager.php:

```php
'cacheDuration' => 31536000, // 1 year
```

If any of your transform settings depends on a value from the specific Asset, you can pass a function instead of a string.

The function will be passed the AssetFileModel.

As an example, this is how you would use a Focal Point field:

```php
<?php
return [
    'transforms' => [
        [
            'width'       => 400,
            'height'      => 400,
            'mode'        => 'croponly',
            'position'    => function ($asset) {
                return $asset->focalPointField;
            },
        ],
    ]
];
```

## Using Imager Pretransform

There is 3 ways to pretransform images:
- Automatically on upload
- Manually through element action
- Console command that takes either folder id or volume handle as argument


Brought to you by [Superbig](https://superbig.co)

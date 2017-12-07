# SEO Editor for SilverStripe 3

**For SilverStripe 4, please use [axllent/silverstripe-meta-editor](https://github.com/axllent/silverstripe-meta-editor)**

SEO Editor interface to allow easy editing of page Titles and Meta Descriptions for pages within a ModelAdmin interface.

Edit inline, or download a CSV export and import changes.

This module is based on the original [littlegiant/silverstripe-seo-editor](https://github.com/littlegiant/silverstripe-seo-editor),
however adapted to suit different requirements. This module focuses on the standard `Title` and `MetaDescription` fields, and
takes into consideration the current `MenuTitle` field (as not to change the menu title when you change the page title).

![SilverStripe SEO Editor](https://raw.github.com/axllent/silverstripe-seo-editor/master/images/preview.jpg)


## Installation

Installation via composer

```bash
$ composer require axllent/silverstripe-seo-editor
```

## Configuration

You can optionally specify pagetypes to be ignored in the SEO editor. Simply create a yml file in the following format (the example below is the default):

```
SEOEditorAdmin:
  ignore_page_types:
    - ErrorPage
    - RedirectorPage
    - VirtualPage
  meta_title_min_length: 20
  meta_title_max_length: 70
  meta_description_min_length: 100
  meta_description_max_length: 200
```

## Requirements

SilverStripe CMS ^3.1


## License

SilverStripe SEO is released under the MIT license


### Code guidelines

This project follows the standards defined in:

* [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
* [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

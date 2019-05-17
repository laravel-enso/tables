# Tables

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d54378598375451382b4d5d248d5a8dd)](https://www.codacy.com/app/laravel-enso/tables?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/tables&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://github.styleci.io/repos/111688250/shield?branch=master)](https://github.styleci.io/repos/111688250)
[![License](https://poser.pugx.org/laravel-enso/tables/license)](https://packagist.org/packages/laravel-enso/tables)
[![Total Downloads](https://poser.pugx.org/laravel-enso/tables/downloads)](https://packagist.org/packages/laravel-enso/tables)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/tables/version)](https://packagist.org/packages/laravel-enso/tables)

Data Table package with server-side processing, unlimited exporting and VueJS components. 
Quickly build any complex table based on a JSON template.

This package can work independently of the [Enso](https://github.com/laravel-enso/Enso) ecosystem.

The front end assets that utilize this api are present in the [tables](https://github.com/enso-ui/tables) package.

For live examples and demos, you may visit [laravel-enso.com](https://www.laravel-enso.com)

[![Watch the demo](https://laravel-enso.github.io/tables/screenshots/bulma_001_thumb.png)](https://laravel-enso.github.io/tables/videos/bulma_demo_01.mp4)

<sup>click on the photo to view a short demo in compatible browsers</sup>

[![Themed screenshot](https://laravel-enso.github.io/tables/screenshots/bulma_002_thumb.png)](https://laravel-enso.github.io/tables/videos/bulma_demo_02.mp4)

<sup>click on the photo to view an **export** demo in compatible browsers</sup>

## Installation

Comes pre-installed in Enso. 

To install outside of Enso:

1. install the package `composer require laravel-enso/tables` 

2. if needed, publish and customize the config: `php artisan vendor:publish --tag=tables-config`

3. install the api implementation for the front end, [tables](https://github.com/enso-ui/tables). Be sure to check
out front end docs [here](https://docs.laravel-enso.com/frontend/tables.html).

## Features

- efficient server side data loading
- multi-column searching
- multi-column sorting with the option to set per column default sorting
- configurable pagination
- user customizable column visibility
- configurable action buttons
- beautiful tag rendering for boolean flags
- can display and format numbers as money values, and the formatting can be customized via the template
- full customization via the use of scoped slots for your columns
- smart resizing & auto-hide based on screen width. Data is still accessible under an optional child row
- tooltips for columns/rows
- front-end translations for labels and even data
- configurable, on-the-fly view modes: compact, striped, bordered, hover
- configurable column alignment from the template left / center / right
- preferences/state save for each table in the browser's localStorage
- server-side Excel exporting of the table data, using your current sorting and filtering choices, with email notification and optional push notifications.
    The export supports a practically unlimited dataset and features real time progress reporting in the interface
- reloading of data on demand
- smart management of huge datasets, with configurable limit
- possibility to define actions that apply to the entire, filtered, dataset
- Enso Enum computation
- Laravel accessors for the main query model
- the configuration template for each table has been designed to be as light and straightforward as possible without losing 
out on features
- caching support for speeding up the retrieval of data
- thorough validation of the JSON template with developer friendly messages, in order to avoid misconfiguration issues
- Eloquent query friendly with the possibility to easily display nested models attribute values
- can be used independently of the Enso ecosystem

### Configuration & Usage

Be sure to check out the full documentation for this package available at [docs.laravel-enso.com](https://docs.laravel-enso.com/backend/tables.html)

### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.

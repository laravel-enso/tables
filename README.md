# Tables

[![License](https://poser.pugx.org/laravel-enso/tables/license)](LICENSE)
[![Stable](https://poser.pugx.org/laravel-enso/tables/version)](https://packagist.org/packages/laravel-enso/tables)
[![Downloads](https://poser.pugx.org/laravel-enso/tables/downloads)](https://packagist.org/packages/laravel-enso/tables)
[![PHP](https://img.shields.io/badge/php-8.2%2B-777bb4.svg)](composer.json)
[![Issues](https://img.shields.io/github/issues/laravel-enso/tables.svg)](https://github.com/laravel-enso/tables/issues)
[![Merge Requests](https://img.shields.io/github/issues-pr/laravel-enso/tables.svg)](https://github.com/laravel-enso/tables/pulls)

## Description

Tables is the backend engine behind Enso's server-side data tables.

The package reads a JSON table template, validates it, builds the frontend bootstrap payload, turns incoming column and meta state into a normalized request object, applies search and filter pipelines to an Eloquent query, computes row payloads, totals, pagination metadata, and can queue large spreadsheet exports with progress notifications.

It is one of the core Enso infrastructure packages and is designed to be reused by any backend module that exposes list views.

## Installation

Install the package:

```bash
composer require laravel-enso/tables
```

Publish the optional assets when you want local overrides:

```bash
php artisan vendor:publish --tag=tables-config
php artisan vendor:publish --tag=tables-mail
```

The package also ships stubs for new table builders, actions, and JSON templates:

```bash
php artisan vendor:publish --provider="LaravelEnso\Tables\AppServiceProvider"
```

When template caching is enabled, clear cached templates on deploy:

```bash
php artisan enso:tables:clear
```

## Features

- JSON template DSL for columns, buttons, controls, filters, structure, and styling.
- Dedicated controller traits for init, table data, and Excel export endpoints.
- Search, filter, interval, sort, and pagination normalization through a request/config pipeline.
- Column computors for enums, numbers, dates, datetimes, translations, resources, model methods, and cents.
- Aggregated totals, averages, and custom/raw totals at table level.
- Queue-based spreadsheet exports with chunked fetching, multi-sheet splitting, and mail/database/broadcast notifications.
- Template caching and optional row-count caching for large datasets.
- Extension points for dynamic templates and batch row actions.
- PHPUnit helpers for datatable endpoint testing.

## Usage

### 1. Implement a table builder

Each table builder implements `LaravelEnso\Tables\Contracts\Table`:

```php
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\Contracts\Table;

class Users implements Table
{
    public function __construct(private TableRequest $request)
    {
    }

    public function query(): Builder
    {
        return User::query()->select(['id', 'email', 'is_active']);
    }

    public function templatePath(): string
    {
        return __DIR__.'/../Templates/users.json';
    }
}
```

### 2. Expose the three backend endpoints

The package controller traits are intentionally small:

- `Init` loads the builder and returns the validated template payload
- `Data` applies request state and returns rows plus meta
- `Excel` prepares the queued export flow and emits `ExportStarted`

```php
use Illuminate\Routing\Controller;
use LaravelEnso\Tables\Traits\Init;
use LaravelEnso\Tables\Traits\Data;
use LaravelEnso\Tables\Traits\Excel;

class InitTable extends Controller
{
    use Init;

    protected string $tableClass = Users::class;
}

class TableData extends Controller
{
    use Data;

    protected string $tableClass = Users::class;
}

class ExportExcel extends Controller
{
    use Excel;

    protected string $tableClass = Users::class;
}
```

### 3. Define the JSON template

At minimum, a table template needs:

- `routePrefix`
- `buttons`
- `columns`

Example:

```json
{
    "routePrefix": "administration.users",
    "buttons": ["create", "excel"],
    "controls": ["columns", "length", "reload", "reset", "style"],
    "appends": ["full_name"],
    "filters": [
        {
            "label": "Active",
            "data": "users.is_active",
            "value": null,
            "type": "boolean"
        }
    ],
    "columns": [
        {
            "label": "Email",
            "name": "email",
            "data": "users.email",
            "meta": ["searchable", "sortable"]
        },
        {
            "label": "Balance",
            "name": "balance",
            "data": "users.balance",
            "number": {
                "precision": 2,
                "symbol": "RON ",
                "template": "%s%v"
            },
            "meta": ["filterable", "total"]
        }
    ]
}
```

### Template structure

Top-level attributes accepted by the validator include:

- `appends`, `auth`, `buttons`, `controls`
- `comparisonOperator`, `countCache`, `crtNo`
- `dataRouteSuffix`, `debounce`, `dtRowId`
- `filters`, `flatten`, `fullInfoRecordLimit`
- `lengthMenu`, `method`, `model`, `name`
- `preview`, `responsive`, `searchMode`, `searchModes`
- `selectable`, `strip`, `templateCache`
- `defaultSort`, `defaultSortDirection`, `totalLabel`

Column attributes:

- mandatory: `data`, `label`, `name`
- optional: `align`, `class`, `dateFormat`, `enum`, `meta`, `number`, `tooltip`, `resource`

Column meta flags:

- `average`, `boolean`, `clickable`, `cents`, `customTotal`
- `date`, `datetime`, `filterable`, `icon`, `method`
- `notExportable`, `nullLast`, `searchable`, `rawTotal`
- `rogue`, `slot`, `sortable`, `sort:ASC`, `sort:DESC`
- `total`, `translatable`, `notVisible`

Button structure:

- mandatory: `type`, `icon`
- types: `global`, `row`, `dropdown`
- actions: `ajax`, `export`, `href`, `router`
- optional attributes such as `routeSuffix`, `fullRoute`, `method`, `event`, `postEvent`, `confirmation`, `selection`, `tooltip`, and `slot`

Filters:

- mandatory: `label`, `data`, `value`, `type`
- optional: `slot`, `multiple`, `route`, `translated`, `params`, `pivotParams`, `custom`, `selectLabel`

Defaults also come from `config/tables.php`:

- cache behavior
- default buttons and controls
- style mapping
- export queue / sheet limits
- search modes and comparison operators

### Request and query pipeline

Incoming frontend state is normalized through:

1. `ProvidesRequest` and `FilterAggregator`
2. `TemplateLoader` and `Template`
3. `Config`, which merges request meta onto template columns
4. `Data\Builders\Data`, `Meta`, and `Total`

The data pipeline applies:

- global search
- per-column filters
- numeric and date intervals
- default and custom sorting
- pagination limits
- model and array computors
- row actions and row style metadata

### Computors and formatting

The package supports two families of computors:

- model computors such as `method` and `resource`
- array computors such as `enum`, `number`, `date`, `datetime`, `cents`, and `translator`

That lets you:

- render enum labels from legacy Enso enums or native PHP enums
- format numbers with symbols and precision
- format dates using the configured global or per-column format
- call model methods for derived values
- wrap values in API resources before returning them

### Export flow

The export endpoint uses `Prepare`, then queues either `Jobs\Excel` or `Jobs\EnsoExcel`.

During export the package:

- rebuilds the filtered query
- streams rows in chunks through `Fetcher`
- writes one or more sheets with OpenSpout
- stores the file under `storage/app/{export.folder}`
- notifies the user with `ExportStarted`, `ExportDone`, or `ExportError`

### Caching and extension points

- `TemplateLoader` caches cacheable template fragments by template path, plus `cachePrefix()` when the table implements `DynamicTemplate`
- `TableCache` can invalidate cached counts on model create/delete
- custom batch jobs can extend `LaravelEnso\Tables\Services\Action`

### Tests

The package ships focused unit coverage for:

- template builders and validators
- search, filter, interval, meta, and export builders
- template cache loading
- `TableCache` invalidation behavior

Useful local targets:

```bash
php artisan test --compact vendor/laravel-enso/tables/tests/units/Services/TemplateLoaderTest.php
php artisan test --compact vendor/laravel-enso/tables/tests/units/Traits/TableCacheTest.php
```

## Depends On

Required Enso packages:

- [`laravel-enso/enums`](https://docs.laravel-enso.com/backend/enums.html) [↗](https://github.com/laravel-enso/enums)
- [`laravel-enso/filters`](https://docs.laravel-enso.com/backend/filters.html) [↗](https://github.com/laravel-enso/filters)
- [`laravel-enso/helpers`](https://docs.laravel-enso.com/backend/helpers.html) [↗](https://github.com/laravel-enso/helpers)

Companion frontend package:

- [`@enso-ui/tables`](https://docs.laravel-enso.com/frontend/tables.html) [↗](https://github.com/enso-ui/tables)

## Contributions

are welcome. Pull requests are great, but issues are good too.

Thank you to all the people who already contributed to Enso!

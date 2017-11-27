<!--h-->
# Vue Data Table
<!--/h-->

DataTable package with server-side processing and VueJS components. Build fast any complex table based on a JSON template.

### Details
Supports:
- server side data loading, with multi-argument
- multi-column searching
- multi-column sorting
- configurable pagination
- customizable column visibility
- configurable action buttons
- beautiful tag rendering for boolean flags
- custom rendering of data for columns
- auto-hide based on screen width. Data is still accessible under an optional child row
- front-end translations
- configurable, on-the-fly view modes: compact, striped, bordered, hover, left - center - right data alignment
- preferences/state save for each table in the browser's localStorage
- server-side Excel exporting of the table data, using your current sorting and filtering choices, with email delivery and optional push notifications
- reloading of data on demand
- Enso Enum computation
- Laravel accessors for the main query model
- thorough validation of the JSON template, in order to avoid miss-configuration issues

### Coming very soon

- fully independent of the Enso ecosystem

#### and sooner than later

- editable with input, date-picker, select, checkbox

#### and later or never

- column reordering

### Installation

... soon

### Use

... soon

### Publishes
- `php artisan vendor:publish --tag=vuedatatable-config` - the component configuration
- `php artisan vendor:publish --tag=vuedatatable-assets` - all the VueJS components and assets

### Notes

The [Laravel Enso Core](https://github.com/laravel-enso/Core) package comes with this package included.

We've tried to make it as light as possible and use the minimum amount of external libraries and dependencies.
Therefore, the package depends just on:
 - [Spout](https://github.com/box/spout) for fast & efficient xlsx exports 
 - [toastr](https://github.com/CodeSeven/toastr) for beautiful notifications
 - [element-resize-detector](https://github.com/wnr/element-resize-detector) for making the table responsive
 - [lodash](https://github.com/lodash/lodash) for debouncing, using a selective import


<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->
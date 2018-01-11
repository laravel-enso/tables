<!--h-->
# Vue Data Table
<!--/h-->

Data Table package with server-side processing and VueJS components. Build fast any complex table based on a JSON template.

[![Screenshot](https://laravel-enso.github.io/vuedatatable/screenshots/bulma_002_thumb.png)](https://laravel-enso.github.io/vuedatatable/screenshots/bulma_002.png)

[![Watch the demo](https://laravel-enso.github.io/vuedatatable/screenshots/bulma_001_thumb.png)](https://laravel-enso.github.io/vuedatatable/videos/bulma_demo_01.webm)

<sup>click on the photo to view a short demo in compatible browsers</sup>


### Features
- efficient server side data loading
- multi-column searching
- multi-column sorting
- configurable pagination
- user customizable column visibility
- configurable action buttons
- beautiful tag rendering for boolean flags
- custom data rendering support
- auto-hide based on screen width. Data is still accessible under an optional child row
- front-end translations for labels and even data
- configurable, on-the-fly view modes: compact, striped, bordered, hover and left / center / right data alignment
- preferences/state save for each table in the browser's localStorage
- server-side Excel exporting of the table data, using your current sorting and filtering choices, with email delivery and optional push notifications
- reloading of data on demand
- Enso Enum computation
- Laravel accessors for the main query model
- the configuration template for each table has been designed to be as light and straightforward as possible without losing 
out on features
- thorough validation of the JSON template with developer friendly messages, in order to avoid misconfiguration issues

### Coming very soon

- fully independent of the Enso ecosystem

#### and sooner than later

- PDF export alongside the XLSX report 
- editable table cells, with input, date-picker, select, checkbox support

#### and later or never

- column reordering

### Installation

... soon

### Use
The Vue Data Table component works by pulling its configuration through an initialization request. 
After loading its configuration through that first request, it makes another request for pulling in its data, 
based on its configuration.

If UI changes occur that require the reload of the data (such as when searching, sorting, etc) or the loading of 
additional data, such as when changing to the next page of results, another request for data is made.

This means that the configuration is not re-read as long as the component is not re-drawn.   

For the data editor functionality (WIP), separate requests will be used.

Note: In order to make the above requests, named routes are required.

#### Configuration
The table comes with with a publishable configuration file which you may update in order to fit your 
project requirements. The various options are explained below.

```php
return [
    
    'validations' => 'local',
    'labels' => [
        'crtNo'   => '#',
        'actions' => 'Actions',
    ],
    'lengthMenu' => [
        10, 15, 20, 25, 30,
    ],
    'buttons' => [
        'global' => [
            'create' => [
                'icon'        => 'fa fa-plus',
                'class'       => 'is-success',
                'routeSuffix' => 'create',
                'event'       => 'create',
                'action'      => 'router',
                'label'       => 'Create',
            ],
            'excel' => [
                'icon'        => 'fa fa-file-excel-o',
                'class'       => 'is-info',
                'routeSuffix' => 'exportExcel',
                'event'       => 'export-excel',
                'action'      => 'export',
                'label'       => 'Excel',
            ],
        ],
        'row' => [
            'show' => [
                'icon'        => 'fa fa-eye',
                'class'       => 'is-success',
                'routeSuffix' => 'show',
                'event'       => 'show',
                'action'      => 'router',
            ],
            'edit' => [
                'icon'        => 'fa fa-pencil',
                'class'       => 'is-warning',
                'routeSuffix' => 'edit',
                'event'       => 'edit',
                'action'      => 'router',
            ],
            'destroy' => [
                'icon'         => 'fa fa-trash',
                'class'        => 'is-danger',
                'routeSuffix'  => 'destroy',
                'event'        => 'destroy',
                'action'       => 'ajax',
                'method'       => 'DELETE',
                'message'      => 'The selected record is about to be deleted. Are you sure?',
                'confirmation' => true,
            ],
            'download' => [
                'icon'        => 'fa fa-cloud-download',
                'class'       => 'is-primary',
                'routeSuffix' => 'download',
                'event'       => 'download',
                'action'      => 'href',
            ],
        ],
    ],
    'style' => [
        'default' => [
            'striped', 'hover', 'bordered', 'center',
        ],
        'mapping' => [
            'left'     => 'has-text-left',
            'center'   => 'has-text-centered',
            'right'    => 'has-text-right',
            'compact'  => 'is-narrow',
            'striped'  => 'is-striped',
            'bordered' => 'is-bordered',
            'hover'    => 'is-hoverable',
        ],
    ],
    'boolean' => [
        false => '<span class="tag is-table-tag is-danger"><span class="icon is-small"><i class="fa fa-times"></i></span></span>',
        true  => '<span class="tag is-table-tag is-success"><span class="icon is-small"><i class="fa fa-check"></i></span></span>',
    ],
    'export' => [
        'path'             => 'exports',
        'limit'            => 20000,
        'maxExecutionTime' => 100,
        'notifications'    => ['broadcast', 'database'],
    ],
];
```

- `validations`, string, values may be `always`/`local`, default `local`. When parsing the template, the given options are validated 
because we want to avoid misconfiguration leading to unexpected results. It makes sense to run the validator just 
during development, however, if you want to also run it in production, you may configure that here.
- `labels`, array of options for the header names of the implicit columns. Note that these labels are also translated 
if a translation function is given to the VueJS component, through the `i18n` parameter. Options:   
    - `crtNo` is the current line number, default `#`
    - `actions`, is the last table column that contains the row's buttons, default `Actions`
- `lengthMenu`, array of numbers, default `[10, 15, 20, 25, 30]` representing the pagination options for the table. 
For each table's JSON template, the `lengthMenu` parameter is also available, and, if given, it will have higher priority over the 
global configuration. This allows for some tables to have a different pagination than the default.
- `buttons`, array of button configurations, with 2 types:
    - `global`, these buttons are the buttons that are rendered above the search input, global for the whole table, 
    which do not depend on the data of a particular row. Defaults:
       - `create`, button for creating a new resource
       - `excel`, button for exporting the contents of the table. Note: The export process takes into account your current 
       sorting and filtering.
    - `row`, these are the buttons rendered in the `action` column, and defaults include: 
    `show`, `edit`, `destroy`, `download`
- `style`, array of style configurations, with 2 sections:
    - `default`, array of classes, default is `['striped', 'hover', 'bordered', 'center']`, that are applied by default 
    for all tables. Note that you should set only one alignment specific class in the default.
    - `mapping`, array of configurations for the styles. While designed for/with Bulma, you may specify here custom 
    classes in order to personalize your tables   
- `boolean`, array of configuration - HTML code mappings, that give you the option of customizing the rendering of 
boolean type columns
- `export`, array configuration options for exporting the contents of a file. Note: The export process takes 
into account your current sorting and filtering. Available options:
    - `path`, string, folder where the temporary export file is saved, default `exports`. This folder is expected 
    to reside in `storage/app`
    - `limit`, number, the maximum limit of results that are exported, default 20000. You may want to tweak this depending on 
    the time the export takes, the size of the file, etc. 
    - `maxExecutionTime`, number, max number of seconds for the php script to run, before it times out. You may need
     to adjust this depending on how big your reports are. 
    - `notifications`, array of notification options, default `['broadcast', 'database']`. Note that 
    email notifications are always used for sending the actual export file, so you should take into account email attachment size and 
    mail server timeout / other limitations when choosing values for the export.  

#### Template
```JSON
{
    "routePrefix": "route.prefix",
    "readSuffix": "read.suffix",
    "writeSuffix": "write.suffix",
    "name": "Table Name",
    "icon": "fa fa-list-alt",
    "crtNo": true,
    "lengthMenu": [10, 15, 20, 25, 30],
    "appends": ["customAttribute"],
    "buttons": [
        "show", "create", "edit", "destroy", "download", "exportExcel",
        {
            "type": "row",
            "icon": "fa fa-bell",
            "class": "has-text-purple",
            "routeSuffix": "custom",
            "event": "custom-event",
            "action": "router",
            "fullRoute": "optional.full.route",
            "label": "Button Label",
            "confirmation": false,
            "message": "Are you sure?",
            "method": "GET/PUT/PATCH/POST/DELETE",
            "params": {
                "first": "foo",
                "second": "bar"
            }
        }
    ],
    "columns": [
        {
            "label": "Name",
            "data": "table.column",
            "name": "columnAlias",
            "meta": ["searchable", "sortable", "translation", "boolean", "editable", "total", "render"],
            "enum": "EnumClass"
        }
    ]
}
```

Options:
- `routePrefix`, required, string, the route segment, used for both read and write
- `readSuffix`, required, string, the route endpoint, that gets concatenated to the `routePrefix`
- `writeSuffix`, optional, string, the route endpoint, that gets concatenated to the `routePrefix`. 
This is only needed when using the editor. 
- `name`, optional, string, the title used for the table.
- `icon`, optional, string, expects Font Awesome icon classes
- `crtNo`, optional, boolean, flag for showing the current line number. Note that if it's missing the responsive 
functionality will be limited 
- `lengthMenu`, optional, array, list of options for the table pagination. If missing, the default values in the 
global configuration are used. If given, the template values have higher precedence
- `appends` - optional, array, list of appended attributes that need to be added to the query results. 
Note that the appended attributes are available from the main query model
- `buttons`, optional, array, list of buttons that need to be rendered. See below for more in-depth information.
- `columns`, required, array, list of column configurations. See below for more in-depth information.

##### Buttons
There are several type of buttons, depending on how you classify them.

By configuration:
- `simple`, declared as a string, representing one of the string buttons from the config. 
Example: `"show", "create", "edit", "destroy", "download", "exportExcel"`
- `complex`, declared as an object, with several attributes.

By position:
- `row`, buttons that are rendered on each row, in the actions column. 
Example: `"show", "edit", "destroy", "download"`, or custom buttons with the `type:"row"` configuration.
- `global`, buttons that are rendered above the search input, at the top of the table VueJS component.
Example: `"create", "exportExcel"`, or custom buttons with the `type:"global"` configuration.

By action:
- `router`, buttons that trigger an action/navigation through the VueJS router, `"action": "router"`
- `href`, buttons that trigger an action/navigation through a plain HTML link
- `export`, buttons that trigger an export
- `ajax`, buttons that trigger an ajax request.

The configuration options for buttons are, as follows:
- `type`: required, string, available options are `row` / `global`
- `icon`: required, string, expects font awesome icon classes
- `class`: required, string, expects CSS styling classes
- `routeSuffix`: optional, string, if given, gets appended to the `routePrefix` param
- `event`: optional, string, the name of an event that is emitted on click, which allows for custom in-page handling, 
outside of the table
- `action`: optional, string, available options are `router` / `href` / `export` / `ajax`. 
Depending on the chosen options other parameters could be required.
- `fullRoute`: optional, string, if given, is used independently from the `routePrefix` param
- `label`: optional, string, should be given only for global buttons
- `confirmation`: optional, boolean, flag for showing a confirmation modal before processing the action, such as deletion
- `message`: optional, string, used in conjunction with `confirmation`, when you want to customize the modal's message
- `method`: optional, string, should be given if you have `action` set as `ajax`, 
available options are: `"GET"` / `"PUT`" / `"PATCH`" / `"POST`" / `"DELETE`"
- `params`: optional, object, used if action = `router`, object is added to route params object

##### Columns
The columns configuration attribute is required, and expects an array of configuration objects. 
Each configuration object may have the following attributes:
- `label`, required, string, the column name used in the table header. This will be translated if a translation function 
is available. 
- `data`, required, string, the table + column that data gets pulled from, in the query. For example 'users.email'
- `name`, required, string, the alias for that column's data, given in the select query
- `enum`, optional, string, the class name of the enumeration used to transform/map the values of that column/attribute. 
For example, you may use this mechanism to show labels instead of integer values, for an attribute that holds 
the type for a model/table.  
- `meta`, optional, array of string options, for further transformations:
    - `searchable`, optional, string, marks this column as searchable. If not searchable, a column is not used when 
    using the table search functionality 
    - `sortable`, optional, string, marks this column as sortable. If not sortable, the controls to sort are 
    not available for sorting
    - `translation`, optional, string, marks this column's values as translatable. 
    The `i18n` parameter translation function should be given to the VueJS component 
    - `boolean`, optional, string, marks this column as boolean, which means it will be rendered as such
    - `editable`, optional, string, marks this column as editable - WIP
    - `total`, optional, string, if flagged, calculates a total for this column 
    - `render`, optional, string, flags this column for custom rendering, allowing for unlimited customization 
    of the format of the data in this column

#### The VueJS Component
The VueTable component takes the following parameters:
- `id`, required, string, identification for this table, is used to store the preferences in the browser's local storage
- `path`, required, string, the URI for the table initialization request
- `filters`, optional, object, reactive options that, if available, is sent with the getTableData request and 
is used to filter results
- `params`, optional, object, reactive parameters, that, if available, is sent with the getTableData request and 
is be used to filter results
- `intervals`, optional, object, reactive parameters, that, if available is used for interval filtering of the results
- `customRender`, optional, function, that can be used as a custom render function for a single column or 
a render dispatcher for when needing to custom render multiple columns for the same table
- `i18n`, optional, function, that is used for translating labels, headers, and table data. 
The default value (function) for this parameter simply returns it's argument as the translated value. 

#### The query

In your controller, the query must look like this:

```php
public function query()
{
    return Owner::select(\DB::raw('id as "dtRowId", name, description, is_active, created_at'));
}
```

Keep in mind that the here we're returning a QueryBuilder not a collection of results.

#### Further Examples
You may see the vue data table in action, with the code for the Owners page, right here:
- [data controller](https://github.com/laravel-enso/Core/blob/master/src/app/Http/Controllers/Owner/OwnerTableController.php)
- [table template](https://github.com/laravel-enso/Core/blob/master/src/app/Tables/owners.json)
- [front-end vue page](https://github.com/laravel-enso/Core/blob/master/src/resources/assets/js/pages/administration/owners/Index.vue)
- [live result](http://enso.dev/administration/owners/) (if you're not already logged in, use `admin@laravel-enso.com` and `password`)

Feel free to look around at the various packages in the [laravel-enso](https://github.com/laravel-enso) repository, to find more examples.

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
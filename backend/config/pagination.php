<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pagination View
    |--------------------------------------------------------------------------
    |
    | This option controls the default view used by the paginator, and may be
    | set to a custom view if you wish to use a different rendering style.
    | The default "tailwind" view and "bootstrap" view are both included.
    |
    | Supported: "tailwind", "bootstrap"
    |
    */

    'default' => 'tailwind',

    /*
    |--------------------------------------------------------------------------
    | Pagination Themes
    |--------------------------------------------------------------------------
    |
    | When using the "tailwind" or "bootstrap" theme, you may configure a
    | page that strips the pagination HTML markup. This can be useful
    | if you require a different pagination markup than the defaults.
    |
    */

    'themes' => [
        'tailwind' => 'pagination::tailwind',
        'bootstrap' => 'pagination::bootstrap-5',
    ],

];

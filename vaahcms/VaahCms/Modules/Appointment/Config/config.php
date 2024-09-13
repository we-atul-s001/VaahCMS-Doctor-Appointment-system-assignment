<?php

return [
    "name"=> "Appointment",
    "title"=> "Doctor Appointment",
    "slug"=> "appointment",
    "thumbnail"=> "https://img.site/p/300/160",
    "is_dev" => env('MODULE_APPOINTMENT_ENV')?true:false,
    "excerpt"=> "Doctor Appointment System",
    "description"=> "Doctor Appointment System",
    "download_link"=> "",
    "author_name"=> "Atul Pratap Singh",
    "author_website"=> "https://vaah.dev",
    "version"=> "0.0.1",
    "is_migratable"=> true,
    "is_sample_data_available"=> true,
    "db_table_prefix"=> "vh_appointment_",
    "providers"=> [
        "\\VaahCms\\Modules\\Appointment\\Providers\\AppointmentServiceProvider"
    ],
    "aside-menu-order"=> null
];

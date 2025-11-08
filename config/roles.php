<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Role Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default role that will be assigned
    | to new users during registration. For event management systems,
    | 'attendee' is the industry standard default role.
    |
    | Best Practice for Event Management Systems:
    | - 'attendee' - Standard default (users can browse and register for events)
    | - 'organizer' - Only for verified event organizers (manual assignment)
    | - 'admin' - Only for system administrators (manual assignment)
    |
    */

    'default_role' => env('DEFAULT_USER_ROLE', 'attendee'),

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchy
    |--------------------------------------------------------------------------
    |
    | Define role hierarchy for permission inheritance.
    | Higher roles inherit permissions from lower roles.
    |
    */

    'hierarchy' => [
        'admin' => ['organizer', 'attendee', 'vendor', 'sponsor'],
        'organizer' => ['attendee'],
        'vendor' => ['attendee'],
        'sponsor' => ['attendee'],
        'attendee' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Display Names
    |--------------------------------------------------------------------------
    |
    | Human-readable display names for roles.
    |
    */

    'display_names' => [
        'admin' => 'Administrator',
        'organizer' => 'Event Organizer',
        'attendee' => 'Attendee',
        'vendor' => 'Vendor',
        'sponsor' => 'Sponsor',
    ],

];


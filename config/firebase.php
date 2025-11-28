<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging (FCM) push notifications
    | Using FCM V1 API with service account authentication
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', 'depannema-288ba'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials Path
    |--------------------------------------------------------------------------
    |
    | Path to the JSON file containing service account credentials
    | This file should be downloaded from Google Cloud Console
    | Store it in storage/app/ directory (outside public access)
    |
    */
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | FCM API Version
    |--------------------------------------------------------------------------
    |
    | The FCM API version to use
    |
    */
    'api_version' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'sound' => 'default',
        'badge' => 1,
        'priority' => 'high',
    ],
];


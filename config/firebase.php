<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase / FCM (HTTP v1)
    |--------------------------------------------------------------------------
    |
    | Path to the service account JSON (download from Firebase Console >
    | Project settings > Service accounts). Required for push delivery.
    | Alternatively set FIREBASE_CREDENTIALS_JSON to the raw JSON string.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    'credentials' => env('FIREBASE_CREDENTIALS_PATH'),

    'credentials_json' => env('FIREBASE_CREDENTIALS_JSON'),

    'private_key' => env('FIREBASE_PRIVATE_KEY'),

    'client_email' => env('FIREBASE_CLIENT_EMAIL'),

];

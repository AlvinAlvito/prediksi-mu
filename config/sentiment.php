<?php

return [
    'default_links' => [
        'https://youtu.be/o7hzKvhtVRg?si=Z7cnC92GVFK1GQ2C',
        'https://youtu.be/4XggGCID4mo?si=Ms3NZF3Cbh8g_4cO',
    ],
    'max_comments_per_video' => env('YOUTUBE_MAX_COMMENTS', 0),
    'evaluation_test_ratio' => env('SENTIMENT_EVAL_TEST_RATIO', 0.2),
    'reuse_cached_dataset' => env('REUSE_CACHED_DATASET', true),
    'max_execution_time' => env('ANALYSIS_MAX_EXECUTION_TIME', 0),
    'youtube_request_timeout' => env('YOUTUBE_REQUEST_TIMEOUT', 60),
];

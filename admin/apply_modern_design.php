<?php
// Batch script to apply modern table design to remaining modules

$modules = [
    'archived_sop.php',
    'provider_rates.php', 
    'provider_schedules.php',
    'schedule_routes.php',
    'set_tariffs.php',
    'view_sop.php'
];

$modernHeader = '
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">{TITLE}</h3>
            <p>{DESCRIPTION}</p>
        </div>
    </div>';

$modernTableStart = '
    <!-- Modern {MODULE} Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>';

$modernTableEnd = '
                </thead>';

$modernTableClose = '
            </tbody>
            </table>
        </div>
    </div>';

// Module-specific configurations
$moduleConfig = [
    'archived_sop.php' => [
        'title' => 'Archived SOPs',
        'description' => 'View and manage archived Standard Operating Procedures',
        'columns' => [
            'ID' => 'hashtag',
            'Title' => 'file-alt',
            'Category' => 'tag',
            'Created By' => 'user',
            'Date Created' => 'calendar',
            'Status' => 'toggle-on',
            'Actions' => 'tools'
        ]
    ],
    'provider_rates.php' => [
        'title' => 'Provider Rates',
        'description' => 'Manage freight rates for service providers',
        'columns' => [
            'Rate ID' => 'hashtag',
            'Mode' => 'shipping-fast',
            'Distance' => 'route',
            'Weight/Volume' => 'weight',
            'Rate' => 'dollar-sign',
            'Status' => 'toggle-on',
            'Actions' => 'tools'
        ]
    ],
    'provider_schedules.php' => [
        'title' => 'Provider Schedules',
        'description' => 'View and manage provider schedules',
        'columns' => [
            'Schedule ID' => 'hashtag',
            'Route' => 'route',
            'Date' => 'calendar',
            'Time' => 'clock',
            'Status' => 'toggle-on',
            'Actions' => 'tools'
        ]
    ]
];

echo "Modern design configuration ready for modules:\n";
foreach ($modules as $module) {
    echo "- $module\n";
}
echo "\nUse this configuration to update each module manually.\n";
?>

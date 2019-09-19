<?php
require_once(__DIR__ . '/../init/init.php');

use App\Models\TaiwanGovernorDocument\DownloadList;

$download_list = require_once(__DIR__ . '/../Configs/download_list.php');

foreach ($download_list as $record) {
    $file_path = $record['file_path'];
    $record_count = $record['count'];

    for ($count = 0; $count < $record_count; $count += 10) {
        DownloadList::create([
            'file_path' => $file_path,
            'start_index' => $count,
            'count' => 10,
        ]);
    }
}

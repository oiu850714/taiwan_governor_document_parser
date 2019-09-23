<?php
namespace App\Models\TaiwanGovernorDocument;

use Illuminate\Database\Eloquent\Model;

class DownloadList extends Model
{
    protected $table = 'download_lists';
    protected $connection = 'default';
    public $timestamps = false;

    const STATUS_NONACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_FINISHED = 2;

    protected $fillable = [
        'file_path',
        'start_index',
        'count',
        'status',
        'finished_at',
    ];
}

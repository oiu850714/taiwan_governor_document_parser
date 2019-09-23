<?php
namespace App\Models\TaiwanGovernorDocument;

use Illuminate\Database\Eloquent\Model;

class DownloadList extends Model
{
    protected $table = 'download_lists';
    protected $connection = 'default';
    public $timestamps = false;

    protected $fillable = [
        'file_path',
        'start_index',
        'count',
        'status',
        'finished_at',
    ];
}

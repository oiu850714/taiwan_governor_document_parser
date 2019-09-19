<?php
namespace App\Models\TaiwanGovernorDocument;

use Illuminate\Database\Eloquent\Model;

class DownloadList extends Model
{
    protected $table = 'download_lists';
    protected $connection = 'default';

    protected $fillable = [
        'name',
        'url',
        'file_path',
        'start_index',
        'count',
    ];
}

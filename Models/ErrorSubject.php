<?php
namespace App\Models\TaiwanGovernorDocument;

use Illuminate\Database\Eloquent\Model;

class ErrorSubject extends Model
{
    protected $table = 'error_subjects';
    protected $connection = 'default';
    public $timestamps = false;

    protected $fillable = [
        'subject', // 件名
        'subject_number', // 典藏號
        'book', // 隸屬冊名
        'file_path', // 檔案階層
        'error_message', // 錯誤訊息
    ];
}

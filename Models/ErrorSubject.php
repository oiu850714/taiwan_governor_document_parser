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
        'start_index', // 方便查詢是從第幾筆開始查的
        'error_message', // 錯誤訊息
    ];
}

<?php
include __DIR__ . '/init/init.php';

use DiDom\Document;
use Libraries\ApiClient;
use Libraries\Utils;
use GuzzleHttp\Exception\RequestException;
use App\Models\TaiwanGovernorDocument\DownloadList;
use App\Models\TaiwanGovernorDocument\ErrorSubject;

if (!$download_record = DownloadList::first()) {
    return;
}

$archive_url = Utils::getArchiveUrl($download_record);


$client = new ApiClient();
$body = $client->getArchiveUrlHtmlBody($archive_url);

$document = new Document($body);
// 搜尋結果都在 .result_header 內
$result_headers = $document->find('.result_header');

foreach($result_headers as $result_header) {

    // 件名
    $result_title = $result_header->find('.result_title')[0];
    $result_link = $result_title->find('.result_link')[0];
    $acc_link = $result_link->find('.acc_link')[0];
    $a_real_title =$acc_link->find('a')[0];
    // 這個變數存件名
    $subject = trim($a_real_title->text());


    $result_info = $result_header->find('.result_info')[0];
    $result_fields = $result_info->find('.result_field');

    // 典藏號
    $subject_number = trim($result_fields[0]->find('.field_value')[0]->text());
    // 隸屬測名
    $subject_book = trim($result_fields[1]->find('.field_value')[0]->text());
    // 檔案階層
    $subject_filepath = trim($result_fields[2]->find('.field_value')[0]->text());
    // 本件日期
    $subject_date_range = trim($result_fields[3]->find('.field_value')[0]->text());
    // 密等/解密紀錄
    $subject_permission_level = trim($result_fields[4]->find('.field_value')[0]->text());
    // acc_key
    $subject_download_acc_key = trim($result_fields[5]->find('.field_value')[0]->find('.option')[0]->getAttribute('acckey'));

    $initial_path = '/mnt/c/Users/oiu85/Desktop/database';
    $save_path = "$initial_path/$subject_filepath/$subject_book/";
    if (!file_exists($save_path)) {
        mkdir($save_path, 0777, true);
    }

    try {
        // 第一步: 換 reource key
        $resource_key = $client->getResourceKey($subject_download_acc_key);

        // 第二步: 換 download key
        $download_key = $client->getDownloadKey($resource_key);

        // 第三部: 下載
        $body = $client->getArchive($download_key);
        file_put_contents("$save_path/$subject_number-$subject.zip", $body);
    } catch (\Exception $e) {
        ErrorSubject::create([
            'subject' => $subject,
            'subject_number' => $subject_number,
            'book' => $subject_book,
            'file_path' => $subject_filepath,
            'error_message' => $e->getMessage(),
        ]);
    }
}

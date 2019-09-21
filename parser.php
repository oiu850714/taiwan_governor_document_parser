<?php
include __DIR__ . '/init/init.php';

use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use App\Models\TaiwanGovernorDocument\DownloadList;

try {
    $download_record = DownloadList::first();

    $url = getArchiveUrl($download_record);


    $client = new Client([
        'cookies' => true, // client 要紀錄 cookie，不然總督府網站不給你爬會要你 302
        'base_uri' => 'http://archives.th.gov.tw',
        'connect_timeout' => 30,
    ]);

    $response = $client->request('GET', $url); // 觸發 302 設定 Cookie 並拿搜尋頁面餵給 DiDom
    // 這個網頁沒有帶 cookie 就會 302
    // DiDom 看起來好像沒有可以帶 Cookie 的選項，所以 workaround 把 guzzle result 餵給他
    $body = (string) $response->getBody();

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
        echo
            "=============== 每筆紀錄 =================\n" .
            "件名: $subject\n" .
            "典藏號: $subject_number\n" .
            "隸屬測名: $subject_book\n" .
            "搜尋的檔案階層: $subject_filepath\n" .
            "本件日期: $subject_date_range\n" .
            "密等/解密紀錄: $subject_permission_level\n" .
            "acc_key: $subject_download_acc_key\n" .
            "=============== end =======================\n"
        ;

        $initial_path = '/mnt/c/Users/oiu85/Desktop/database';
        $save_path = "$initial_path/$subject_filepath/$subject_book/";
        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true);
        }

        // 第一步: 換 reource key
        $response = $client->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/initial/$subject_download_acc_key",
            ],
        ]);

        $body_array = json_decode($response->getBody(), true);
        $resource_key = $body_array['data']['resouse']; // 注意 key 是 resouse, 他們 API 拼錯了
        echo "第一步: resource_key: $resource_key\n";


        // 這兩段是要觸發服務內部自己 built 出一個可下載的檔案 LOL
        // 感覺需要戳頁面，這個 key 才有辦法換到 download key...
        $response = $client->request('GET', "/index.php?act=Display/image/$resource_key");

        // 跟據 canvas 頁面的 JS 判斷，應該要打下面的 API 才會 build..
        $response = $client->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/built/$resource_key",
            ],
        ]);

        // 第二步: 換 download key
        $response = $client->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/package/$resource_key",
            ],
        ]);

        $body_array = json_decode($response->getBody(), true);
        $download_key = $body_array['data'];
        if (empty($download_key)) {
            echo "這一件目前無法下載!\n";
            continue;
        }
        echo "第二步: download_key: $download_key\n";

        // 第三部: 下載
        $response = $client->request('GET', "/index.php?act=Display/download/$download_key");
        $body = (string) $response->getBody();
        file_put_contents("$save_path/$subject_number-$subject.zip", $body);

        echo "第三步: guzzle body size: " . strlen($body) . "\n";
    }
} catch (\RequestException $e) {
}

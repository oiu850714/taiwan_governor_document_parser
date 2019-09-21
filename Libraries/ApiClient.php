<?php
namespace Libraries;

use GuzzleHttp\Client;

class ApiClient
{
    const BASE_URI = 'http://archives.th.gov.tw';
    protected $client;

    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client([
            'cookies' => true,
            'base_uri' => self::BASE_URI,
            'connect_timeout' => 30,
        ]);

        return $this->client;
    }


    public function getArchiveUrlHtmlBody($archive_url)
    {
        $response = $this->getClient()->request('GET', $archive_url); // 觸發 302 設定 Cookie 並拿搜尋頁面餵給 DiDom
        // 這個網頁沒有帶 cookie 就會 302
        // DiDom 看起來好像沒有可以帶 Cookie 的選項，所以 workaround 把 guzzle result 餵給他
        $body = (string) $response->getBody();

        return $body;
    }

    public function getResourceKey($acc_key)
    {
        $response = $this->getClient()->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/initial/$acc_key",
            ],
        ]);

        $body_array = json_decode($response->getBody(), true);
        $resource_key = $body_array['data']['resouse']; // 注意 key 是 resouse, 他們 API 拼錯了

        if (empty($resource_key) or !is_string($resource_key)) {
            throw new \Exception('resource key 換取 失敗');
        }

        return $resource_key;
    }

    public function getDownloadKey($resource_key)
    {
        // 這兩段是要觸發服務內部自己 built 出一個可下載的檔案 LOL

        // 跟據 canvas 頁面的 JS 判斷，應該要打下面的 API 才會 build
        $response = $this->getClient()->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/built/$resource_key",
            ],
        ]);

        // 第二步: 換 download key
        $response = $this->getClient()->request('POST', '/index.php', [
            'form_params' => [
                'act' => "Display/package/$resource_key",
            ],
        ]);

        $body_array = json_decode($response->getBody(), true);
        $download_key = $body_array['data'];

        if (empty($download_key) or !is_string($download_key)) {
            throw new \Exception('download key 換取 失敗');
        }

        return $download_key;
    }

    public function getArchive($download_key)
    {
        // 第三部: 下載
        $response = $this->getClient()->request('GET', "/index.php?act=Display/download/$download_key");
        $body = (string) $response->getBody();

        if (($body_size = mb_strlen($body, 'UTF-8')) < 10000) {
           throw new \Exception("檔案 body size $body_size 過小可能有問題");
        }

        return $body;
    }
}

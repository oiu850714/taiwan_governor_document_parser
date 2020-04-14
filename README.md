# Crawler for 「國史館臺灣文獻館文獻檔案查詢系統」
* 設計成給定一個 [檔案查詢 list](https://github.com/oiu850714/taiwan_governor_document_parser/blob/master/Configs/download_list.php) 即可將查詢結果全部爬下來
* HTTP client 有存 session cookie，crawler 基本上就是模擬前台的驗證流程，最後取得下載連結

## TODO
* 包 Dockerfile
* 上雲，檔案存 GCS

<?php

function getArchiveUrl($download_record) {

    $download_file_path = $download_record->file_path;
    $start_index = $download_record->start_index;
    $list_count = $download_record->count;
    $query_json = '{"query":[{"field":"series","value":"PLACEHOLDER","attr":"+"}],"domconf":{"zong_level_content":"block","post_query_content":"none","facetsby":"fonds","fonds":"block","yearrange":"none","list_member":"none","list_location":"none","list_organ":"none","query_history_content":"block","sortby":"identifier","pageing":"20","jp_term_dictionary":"none"}}';
    $query_json = str_replace('PLACEHOLDER', $download_file_path, $query_json);
    $url_query_string = shell_exec("node " . __DIR__ . "/js_library.js '$query_json'");


    $url = "/index.php?act=Archive/search/$url_query_string/$start_index-$list_count";

    return $url;
}

<?php
handle_req();
function handle_req()
{
    global $wpdb;
    $meta_key = isset($_GET['meta_key']) ? $_GET['meta_key'] : '';
    $q = isset($_GET['q']) ? $_GET['q'] : '';

    $custom_sql = "SELECT * FROM `{$wpdb->prefix}posts` p";
    //$prepared_sql = $wpdb->prepare($sql, $q);
    echo json_encode($custom_sql);
}
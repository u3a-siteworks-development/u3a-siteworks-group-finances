<?php

// Create folder used for storing the csv file

function u3a_csv_groupfinances_install(){
    // phpcs:disable 
    // Justification for ignoring use of wp functions- simplicity.
    if (!is_dir(U3A_GROUP_FINANCES)) mkdir(U3A_GROUP_FINANCES);
    
    $htaccess = U3A_GROUP_FINANCES . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n");
    }
    createformpages();
}

//now create the page
function createformpages(){
    $content=get_base_form_code().'<!-- wp:shortcode -->
    [add_group_entries]
    <!-- /wp:shortcode -->';
    $page = get_posts(['name' => 'group-finances-form', 'post_type' => 'page']);
    if (!$page) {
        $page_id = wp_insert_post(
        [
            'comment_status' => 'close',
            'ping_status'    => 'close',
            'post_author'    => 1,
            'post_title'     => 'Group Finances Form',
            'post_name'      => 'group-finances-form',
            'post_status'    => 'publish',
            'post_content'   => $content,
            'post_type'      => 'page',
        ]
    );
    }    
    $page = get_posts(['name' => 'group-finances-saved', 'post_type' => 'page']);
    if (!$page) {
        $page_id = wp_insert_post(
        [
            'comment_status' => 'close',
            'ping_status'    => 'close',
            'post_author'    => 1,
            'post_title'     => 'Group Finances Saved',
            'post_name'      => 'group-finances-saved',
            'post_status'    => 'publish',
            'post_content'   => "",
            'post_type'      => 'page',
        ]
    );
    }
}
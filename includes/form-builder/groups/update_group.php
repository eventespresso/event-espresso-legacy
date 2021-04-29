<?php

//Function to update question groups in the database
function event_espresso_form_group_update($group_id) {
    global $wpdb;

    $group_order = (int)$_POST['group_order'];
    $group_name = sanitize_text_field($_POST['group_name']);
    $group_description = wp_kses_post($_POST['group_description']);
    $show_group_name = isset($_POST['show_group_name']) && $_POST['show_group_name'] != '' ? 1 : 0;
    $show_group_description = isset($_POST['show_group_description']) && $_POST['show_group_description'] != '' ? 1 : 0;

    $group_identifier = empty($_REQUEST['group_identifier']) ? $group_identifier = sanitize_title_with_dashes($group_name . '-' . time()) : $group_identifier = sanitize_title_with_dashes($_REQUEST['group_identifier']);

    $sql = "UPDATE " . EVENTS_QST_GROUP_TABLE .
            " SET group_name = %s, group_order = %d, group_identifier = %s, group_description = %s,
                   show_group_name = %d,
                   show_group_description = %d
                 WHERE id = %d";
    $wpdb->query(
        $wpdb->prepare(
            $sql,
            $group_name,
            $group_order,
            $group_identifier,
            $group_description,
            $show_group_name,
            $show_group_description,
            $group_id
        )
    );
    $del_group_rels = "DELETE FROM " . EVENTS_QST_GROUP_REL_TABLE . " WHERE group_id = %d";
    $wpdb->query(
        $wpdb->prepare(
            $del_group_rels,
            $group_id
        )
    );


    if (!empty($_REQUEST['question_id'])) {
        foreach ($_REQUEST['question_id'] as $k => $v) {
            if ($v != '') {
                $v = absint($v);
                $sql_group_rel = "INSERT INTO " . EVENTS_QST_GROUP_REL_TABLE . " (group_id, question_id) VALUES (%d, %d)";
                $wpdb->query(
                    $wpdb->prepare(
                        $sql_group_rel,
                        $group_id,
                        $v
                    )
                );
            }
        }
    }
}
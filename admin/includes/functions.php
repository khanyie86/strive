<?php
// Function to check if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to redirect to a specified URL if an admin is not logged in
function redirect_if_admin_not_logged_in($login_page) {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: $login_page");
        exit;
    }
}

// Function to redirect to a specified URL if a parent is not logged in
function redirect_if_parent_not_logged_in($redirect_url) {
    if (!isset($_SESSION['parent_id'])) {
        header("Location: $redirect_url");
        exit;
    }
}
?>

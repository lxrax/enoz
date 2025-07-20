<?php
session_start();

// Check if the user is logged in and is an admin, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";
$link = get_db_connection();

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $user_id_to_delete = trim($_GET["id"]);
    $current_user_id = $_SESSION['id'];

    // Prevent admin from deleting their own account
    if ($user_id_to_delete == $current_user_id) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header("location: admin_dashboard.php");
        exit;
    }

    // Check if the user to be deleted is an admin
    $sql_is_admin = "SELECT is_admin FROM users WHERE id = ?";
    if ($stmt_is_admin = mysqli_prepare($link, $sql_is_admin)) {
        mysqli_stmt_bind_param($stmt_is_admin, "i", $user_id_to_delete);
        mysqli_stmt_execute($stmt_is_admin);
        $result_is_admin = mysqli_stmt_get_result($stmt_is_admin);
        $user_to_delete_is_admin = mysqli_fetch_assoc($result_is_admin)['is_admin'];

        if ($user_to_delete_is_admin) {
            // Check if they are the last admin
            $sql_check_admins = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = TRUE";
            $result_check_admins = mysqli_query($link, $sql_check_admins);
            $admin_count = mysqli_fetch_assoc($result_check_admins)['admin_count'];

            if ($admin_count <= 1) {
                $_SESSION['error'] = "You cannot delete the last admin account.";
                header("location: admin_dashboard.php");
                exit;
            }
        }
    }

    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE id = ?";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id_to_delete);

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            if(mysqli_stmt_affected_rows($stmt) > 0){
                $_SESSION['message'] = "User deleted successfully.";
            } else {
                $_SESSION['error'] = "Could not delete user. User not found.";
            }
        } else{
            $_SESSION['error'] = "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Error preparing delete statement.";
    }

    // Close connection
    mysqli_close($link);

    // Redirect to admin dashboard
    header("location: admin_dashboard.php");
    exit;
} else{
    // If ID parameter is missing, redirect
    $_SESSION['error'] = "No user ID specified for deletion.";
    header("location: admin_dashboard.php");
    exit;
}
?>

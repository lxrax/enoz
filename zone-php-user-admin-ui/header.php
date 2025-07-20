<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

$header_link = get_db_connection();

// Fetch settings for the header
$settings = [];
$sql_settings = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('municipality_logo_path', 'province_name', 'municipality_name')";
if ($result_settings = mysqli_query($header_link, $sql_settings)) {
    while ($row = mysqli_fetch_assoc($result_settings)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
mysqli_close($header_link);

$logo_path = !empty($settings['municipality_logo_path']) ? $settings['municipality_logo_path'] : 'uploads/default_logo.png';
$province_name = !empty($settings['province_name']) ? $settings['province_name'] : 'Province';
$municipality_name = !empty($settings['municipality_name']) ? $settings['municipality_name'] : 'Municipality';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zoning, Locational, and Fishing Permit System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" class="header-logo">
        <div class="header-text">
            <h1>Republic of the Philippines</h1>
            <h2>Province of <?php echo htmlspecialchars($province_name); ?></h2>
            <h3>Municipality of <?php echo htmlspecialchars($municipality_name); ?></h3>
        </div>
    </header>
    <?php include 'navigation.php'; ?>
    <div class="container">

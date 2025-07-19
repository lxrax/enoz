<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";
$link = get_db_connection();

// Function to fetch all settings
function get_all_settings($link) {
    $settings_data = [];
    $sql = "SELECT setting_key, setting_value FROM settings";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $settings_data[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings_data;
}

$app_settings = get_all_settings($link);
$permit = null;
$permit_id = 0;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $permit_id = trim($_GET["id"]);
    $sql = "SELECT fp.*, u.username as encoded_by_username
            FROM fishing_gear_permits fp
            LEFT JOIN users u ON fp.encoded_by_user_id = u.id
            WHERE fp.id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $permit_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $permit = mysqli_fetch_assoc($result);
            }
        }
    }
}

if ($permit === null) {
    die("Error: Permit data could not be loaded.");
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Zoning Certificate - <?php echo htmlspecialchars($certificate['certificate_number']); ?></title>
    <style>
        body {
            font-family: "Bookman Old Style", serif;
            margin: 20px;
            line-height: 1.5;
            color: #333;
            font-size: 12pt;
        }
        .print-container {
            width: 100%;
            max-width: 800px; /* Adjust as per typical paper size like A4 or Letter */
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        h1, h2, h3 { text-align: center; margin-bottom: 20px; }
        h1 { font-size: 1.8em; }
        h2 { font-size: 1.5em; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        td, th {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            width: 35%; /* Label column width */
            font-weight: bold;
        }
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-section .logo { /* Placeholder for a logo */
            max-height: 80px;
            margin-bottom: 10px;
        }
        .header-section p {
            margin: 2px 0;
        }
        .footer-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }
        .signature-block {
             width: 300px; /* Define a width for the block */
             margin: 60px auto 0 auto; /* Center the block itself */
             text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }
        .signature-name {
            margin-top: 5px;
            font-weight: bold;
        }
        .important-note {
            margin-top: 30px;
            font-style: italic;
            font-size: 0.9em;
            color: #555;
        }

        @media print {
            body { margin: 0; /* Reset margins for printing */ }
            .print-container {
                border: none;
                box-shadow: none;
                width: 100%;
                max-width: 100%; /* Use full page width for printing */
                padding: 0;
            }
            .no-print { display: none; } /* For elements not to be printed */
        }
    </style>
</head>
<body onload="window.print();">
    <div class="print-container">
        <div class="header-section" style="display: flex; justify-content: space-between; align-items: center; text-align:center; flex-wrap:wrap;">
            <div style="flex-basis: 20%; text-align: left;">
                 <img src="<?php echo htmlspecialchars($app_settings['municipality_logo_path'] ?? ''); ?>" alt="Municipality Logo" class="logo">
            </div>
            <div style="flex-basis: 60%;">
                <p>Republic of the Philippines</p>
                <p>Province of <?php echo htmlspecialchars($app_settings['province_name'] ?? '[Province Name]'); ?></p>
                <p>Municipality of <?php echo htmlspecialchars($app_settings['municipality_name'] ?? '[Municipality Name]'); ?></p>
                <h3 style="margin-top:10px;">OFFICE OF THE MUNICIPAL PLANNING AND DEVELOPMENT COORDINATOR</h3>
            </div>
            <div style="flex-basis: 20%; text-align: right;">
            </div>
        </div>
        <hr style="border-top: 2px solid #000; margin-bottom: 20px;">
        <h1 style="text-align:center;">ZONING CLEARANCE</h1>
        <div class="content">
            <p style="text-indent: 2em;"> This is to certify that the Fishing Structure/Gear which is/are <strong><?php echo htmlspecialchars($permit['gear_type']); ?></strong>
            of MR./MS. <strong><?php echo htmlspecialchars($permit['owner_name']); ?></strong>
            a resident of Barangay <strong><?php echo htmlspecialchars($permit['owner_resident_of']); ?></strong>
            Located in <strong><?php echo htmlspecialchars($permit['location']); ?></strong> Batan, Aklan</p>
            <p style="text-indent: 2em;">Protection to easement of navigation is 30 meters wide with center of the river as reference.</p>
            <p style="text-indent: 2em;">Easement of 6 meters from the shoreline and mangrove areas shall be subject to protection.</p>
            <p style="text-indent: 2em;">This is issued pursuant to Section 19 of the Municipal Zoning Ordinance.</p>
            <p style="text-indent: 2em;">Issued temporarily.</p>
            <p style="text-indent: 2em;">Done this:<strong><?php echo date("F j, Y", strtotime($permit['issue_date'])); ?>.</strong>
           
            <div class="footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p class="signature-name"><?php echo strtoupper(htmlspecialchars($app_settings['mpdc_name'] ?? '[SIGNATORY NAME]')); ?>
          <p> MPDC / Zoning Administrator</p> <!-- User should customize title -->
            </div>
        </div>
            <div class="field"><div class="label">O.R. No.:<?php echo htmlspecialchars($permit['or_number']); ?></div></div>
            <div class="field"><div class="label">Amount Paid:<?php echo number_format($permit['amount_paid'], 2); ?></div></div>
            <div class="field"><div class="label">Date:<?php echo date("F j, Y", strtotime($permit['date_paid'])); ?></div></div>
            <div class="field"><div class="label">Issued at:<?php echo htmlspecialchars($permit['issued_at']); ?></div></div>
            <div class="field"><div class="label">Encoded By:<?php echo htmlspecialchars($permit['encoded_by_username']); ?></div></div>
        </div>

        

         <div class="no-print" style="margin-top:20px; text-align:center;">
            <button onclick="window.print();">Print Again</button>
            <button onclick="window.close();">Close</button>
        </div>
    </div>
</body>
</html>

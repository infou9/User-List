<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Test</h1>";

$conn = mysqli_connect("localhost", "root", "", "company");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
} else {
    echo "<p style='color:green'>Connection Successful!</p>";
}

$result = mysqli_query($conn, "SELECT * FROM employee");
if ($result) {
    $count = mysqli_num_rows($result);
    echo "<p>Table 'employee' found. Rows: $count</p>";
    if ($count > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<pre>" . print_r($row, true) . "</pre>";
        }
    } else {
        echo "<p>Table is empty.</p>";
    }
} else {
    echo "<p style='color:red'>Error querying table: " . mysqli_error($conn) . "</p>";
}
?>
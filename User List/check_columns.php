<?php
$conn = mysqli_connect("localhost", "root", "", "company");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$result = mysqli_query($conn, "SHOW COLUMNS FROM employee");

echo "<h1>Column Names in 'employee' table:</h1>";
echo "<ul>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
} else {
    echo "Error showing columns: " . mysqli_error($conn);
}
echo "</ul>";
?>
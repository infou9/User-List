<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "company");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Check action
$action = 'insert';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}

if ($action == 'insert') {
    handleInsert($conn);
} elseif ($action == 'fetch') {
    handleFetch($conn);
} elseif ($action == 'delete') {
    handleDelete($conn);
} elseif ($action == 'update') {
    handleUpdate($conn);
} elseif ($action == 'get_one') {
    handleGetOne($conn);
}

function handleInsert($conn)
{
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);
        $orig = basename($_FILES['image']['name']);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $safe = uniqid('img_', true) . ($ext ? '.' . $ext : '');
        $target = $uploadDir . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = 'images/' . $safe;
        }
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO employee (firstname, lastname, email, password, mobile, gender, image)
            VALUES ('$firstname', '$lastname', '$email', '$hashed_password', '$mobile', '$gender', '$image')";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        // Return error message with a prefix to detect it
        echo "Error: " . mysqli_error($conn);
    }
}

// Helper to get Primary Key Column Name
function getPrimaryKey($conn)
{
    $result = mysqli_query($conn, "SHOW KEYS FROM employee WHERE Key_name = 'PRIMARY'");
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['Column_name'];
    }
    // Fallback: get first column
    $result = mysqli_query($conn, "SHOW COLUMNS FROM employee");
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['Field'];
    }
    return 'id'; // Default
}

function handleFetch($conn)
{
    $sql_query = "SELECT * FROM employee ORDER BY 1 DESC"; // Order by first column (usually ID)
    $result = mysqli_query($conn, $sql_query);

    // Get PK for buttons
    $pk = getPrimaryKey($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Use the dynamic PK
            $id = $row[$pk];

            $imgSrc = !empty($row['image']) ? $row['image'] : 'images/man.png';

            // Quote the ID in onclick to handle string/numeric mixed IDs safely
            echo "<tr>
                    <td><img src='{$imgSrc}' class='user-thumb'></td>
                    <td>" . htmlspecialchars($row["firstname"]) . " " . htmlspecialchars($row["lastname"]) . "</td>
                    <td>" . htmlspecialchars($row["email"]) . "</td>
                    <td>" . htmlspecialchars($row["mobile"]) . "</td>
                    <td>" . htmlspecialchars($row["gender"]) . "</td>
                    <td>
                        <button class='btn-edit' onclick='editUser(\"{$id}\")'>Edit</button>
                        <button class='btn-delete' onclick='deleteUser(\"{$id}\")'>Delete</button>
                    </td>
                  </tr>";
        }
    } else {
        echo ""; // Return empty string so JS handles "No employees found"
    }
}

function handleDelete($conn)
{
    $id = $_POST['id'];
    $pk = getPrimaryKey($conn);
    // Use dynamic PK in query
    $sql = "DELETE FROM employee WHERE `$pk`='$id'";
    if (mysqli_query($conn, $sql)) {
        echo "Data Deleted Successfully";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

function handleGetOne($conn)
{
    $id = $_POST['id'];
    $pk = getPrimaryKey($conn);
    $sql = "SELECT * FROM employee WHERE `$pk`='$id'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        // Ensure we send back 'id' property for JS even if column is named 'emp_id'
        $row['id'] = $row[$pk];
        echo json_encode($row);
    }
}

function handleUpdate($conn)
{
    $id = $_POST['id'];
    $pk = getPrimaryKey($conn);

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];

    // Optional: Update password only if provided
    $password_clause = "";
    if (!empty($_POST['password'])) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_clause = ", password='$hashed_password'";
    }

    $image_clause = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);
        $orig = basename($_FILES['image']['name']);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $safe = uniqid('img_', true) . ($ext ? '.' . $ext : '');
        $target = $uploadDir . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = 'images/' . $safe;
            $image_clause = ", image='$image'";
        }
    }

    $sql = "UPDATE employee SET 
            firstname='$firstname', 
            lastname='$lastname', 
            email='$email', 
            mobile='$mobile', 
            gender='$gender' 
            $password_clause 
            $image_clause 
            WHERE `$pk`='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
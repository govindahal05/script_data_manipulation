<?php
$servername = "localhost";
$username = "root";
$password = "GO";
$dbname = "marriots";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM bike";
$result = $conn->query($sql);
$i = 0;
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $sql = "Update bike set content = '" . html_entity_decode($row['content']) . "' where id =" . $row['id'];
        if ($conn->query($sql) === TRUE) {
           // echo "Updated" . $row['id'];
            $i++;
        }
    }
} else {
    echo "No records round";
}

echo "$i rows updated";

$conn->close();
?> 
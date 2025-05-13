<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$fullname = $email = $password = $confirm_password = $phone = $address = $pincode = $state = $country = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm-password']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
  
    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    }

    $email_check = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($email_check);
    if ($result->num_rows > 0) {
        $error = "Email already exists.";
    }

    if (empty($error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password, phone, address, pincode, state, country) 
                VALUES ('$fullname', '$email', '$hashed_password', '$phone', '$address', '$pincode', '$state', '$country')";

        if ($conn->query($sql) === TRUE) {
    
            header("Location: login.html");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
 if (!empty($error)): ?>
    <p style="color: red; text-align: center;"><?php echo $error; ?></p>
  <?php endif; 
  $conn->close();

  ?>




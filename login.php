<?php
require_once "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT role, ref_id FROM users WHERE username=? AND temp_password=SHA2(?,256)");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($role, $ref_id);
    if ($stmt->fetch()) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['ref_id'] = $ref_id;

            switch ($role) {
                case 'admin': header("Location: admin/dashboard.php"); break;
                case 'instructor': header("Location: instructor/dashboard.php"); break;
                case 'student': header("Location: student/dashboard.php"); break;
            }
            exit;
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Page</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="width: 350px;">
      <h3 class="text-center mb-4">Login</h3>
      <form method = "POST"> 
        <!-- Username -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name = "username" placeholder="Enter username">
        </div>
        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name = "password" placeholder="Enter password">
        </div>
        <!-- Submit Button -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php if(isset($error)) echo $error; ?>

<?php
$login_error = '';
$register_error = '';
$register_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Login
        $username = $conn->real_escape_string($_POST['username']);
        $password = $conn->real_escape_string($_POST['password']);

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                header("Location: index.php?page=home");
                exit();
            } else {
                $VCM_login_error1;
            }
        } else {
            $VCM_login_error2;
        }
    } elseif (isset($_POST['register'])) {
        // Registrazione
        $username = $conn->real_escape_string($_POST['username']);
        $password = $conn->real_escape_string($_POST['password']);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['username'] = $username;
                header("Location: index.php?page=home");
                exit();
            } else {
                $VCM_register_error1 . $conn->error;
            }
        } else {
            $VCM_register_error2;
        }
    }
}

$conn->close();
?>


<div class="d-flex justify-content-center align-items-center vh-100">
    <div id="login-form" class="card shadow-sm p-4" style="display: block;">
        <h2 class="mt-3 text-center"><?php echo $VCM_login; ?></h2>
        <?php if ($login_error): ?>
        <div class="alert alert-danger"><?php echo $VCM_login_error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="<?php echo $VCM_username1; ?>" class="form-label"><?php echo $VCM_username1; ?></label>
                <input type="text" class="form-control" id="loginUsername" name="username" required>
            </div>
            <div class="mb-3">
                <label for="<?php echo $VCM_password1; ?>" class="form-label"><?php echo $VCM_password1; ?></label>
                <input type="password" class="form-control" id="loginPassword" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="login"><?php echo $VCM_login; ?></button>

            <h5 class="text-center mt-5"><?php echo $VCM_register2." "; ?><a onclick="showRegisterForm()"><?php echo $VCM_register; ?></a></h5>
        </form>
    </div>

    <div id="register-form" class="card shadow-sm p-4" style="display: none;">
        <h2 class="mt-3 text-center"><?php echo $VCM_register; ?></h2>
        <?php if ($register_error): ?>
        <div class="alert alert-danger"><?php echo $VCM_register_error; ?></div>
        <?php endif; ?>
        <?php if ($register_success): ?>
        <div class="alert alert-success"><?php echo $VCM_register_success; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="<?php echo $VCM_username1; ?>" class="form-label"><?php echo $VCM_username1; ?></label>
                <input type="text" class="form-control" id="registerUsername" name="username" required>
            </div>
            <div class="mb-3">
                <label for="<?php echo $VCM_register; ?>" class="form-label"><?php echo $VCM_password1; ?></label>
                <input type="password" class="form-control" id="registerPassword" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="register"><?php echo $VCM_register; ?></button>

            <h5 class="text-center mt-5"><?php echo $VCM_login2." "; ?> <a onclick="showLoginForm()"><?php echo $VCM_login; ?></a></h5>
        </form>
    </div>
</div>

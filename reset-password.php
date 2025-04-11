<?php

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

require __DIR__ . "/db_connect.php";
$mysqli = getConnection();

$sql = "SELECT * FROM user
        WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("token not found");
}

if (strtotime($user["t_token_expires_at"]) <= time()) {
    die("token has expired");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .banner {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 7vh;
            background-color: #b91a1a;
        }

        .container {
            width: 400px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #444444;
            margin-bottom: 20px;
        }

        p {
            font-size: 14px;
            color: gray;
            margin-bottom: 20px;
        }

        .input {
            margin-bottom: 15px;
            text-align: left;
        }

        .input label {
            display: block;
            margin-bottom: 5px;
        }

        .input input {
            width: 100%;
            padding: 10px;
            border: 1px solid #444444;
            border-radius: 4px;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #444444;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #b91a1a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #444444;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #444444;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="banner"></div>

    <div class="container">
        <h2>Reset Password</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <form method="post" action="process-reset-password.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="input">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input">
                <label for="password_confirmation">Repeat password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    </div>

</body>
</html>


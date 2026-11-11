
<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!empty($_SESSION['user_id'])) {

    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    if (strlen($username) < 3) {

        $error = 'Username must be at least 3 characters long.';

    }

    elseif (strlen($password) < 8) {

        $error = 'Password must be at least 8 characters long.';

    }

    elseif ($password !== $confirmPassword) {

        $error = 'The passwords you entered do not match.';

    }

    else {

        $checkUsername = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
        ");

        $checkUsername->execute([$username]);


        if ($checkUsername->fetch()) {

            $error = 'This username is already taken. Please choose another one.';

        }

        else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $registerUser = $pdo->prepare("
                INSERT INTO users (username, password, status, role)
                VALUES (?, ?, 'pending', 'user')
            ");

            $registerUser->execute([
                $username,
                $hashedPassword
            ]);

            flash(
                'Your registration has been submitted. Please wait for an administrator to approve your account.'
            );

            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Register';
require 'includes/header.php';

?>

<section>
    <div>
        <div>GET STARTED</div>
        <h1>Create an Account</h1>
        <p>
            Register now and wait for an administrator
            to approve your account before signing in.
        </p>

        <?php if ($error): ?>

            <div>
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                required
                minlength="3"
                placeholder="Choose a username"
                value="<?= e($_POST['username'] ?? '') ?>"
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
                minlength="8"
                placeholder="At least 8 characters"
            >

            <label for="confirm_password">Confirm Password</label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                placeholder="Enter your password again"
            >

            <button type="submit">
                Register
            </button>

        </form>

        <p>
            Already have an account?
            <a href="index.php">Sign In</a>
        </p>
    </div>
</section>

<?php require 'includes/footer.php'; ?>

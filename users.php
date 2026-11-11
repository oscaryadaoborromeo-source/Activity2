
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $userId = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId === (int) $_SESSION['user_id']) {

        flash('You cannot change your own account here.', 'error');

    } else {

        switch ($action) {

            case 'approve':

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET status = 'approved'
                    WHERE id = ?
                ");

                $stmt->execute([$userId]);

                flash('User account successfully approved.');

                break;

            case 'reject':

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET status = 'rejected'
                    WHERE id = ?
                ");

                $stmt->execute([$userId]);

                flash('User registration has been rejected.');

                break;

            case 'make_admin':

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET role = 'admin'
                    WHERE id = ? AND status = 'approved'
                ");

                $stmt->execute([$userId]);

                flash('User has been promoted to administrator.');

                break;

            case 'make_user':

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET role = 'user'
                    WHERE id = ?
                ");

                $stmt->execute([$userId]);

                flash('User role has been changed to regular user.');

                break;

            case 'delete':

                $stmt = $pdo->prepare("
                    DELETE FROM users
                    WHERE id = ?
                ");

                $stmt->execute([$userId]);

                flash('User account successfully deleted.');

                break;

            default:

                flash('Invalid action. Please try again.', 'error');

                break;
        }
    }

    header('Location: users.php');
    exit;
}

$users = $pdo->query("
    SELECT *
    FROM users
    ORDER BY
        FIELD(status, 'pending', 'approved', 'rejected'),
        created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

function user_btn(
    int $userId,
    string $action,
    string $label,
    string $confirmation = ''
): string {

    $confirmMessage = '';

    if ($confirmation !== '') {

        $confirmMessage = ' onsubmit="return confirm('
            . "'" . e($confirmation) . "'"
            . ');"';
    }

    return
        '<form method="POST"' . $confirmMessage . '>'
        . csrf_field()
        . '<input type="hidden" name="id" value="' . $userId . '">'
        . '<input type="hidden" name="action" value="' . e($action) . '">'
        . '<button type="submit">' . e($label) . '</button>'
        . '</form>';
}

$pageTitle = 'User Management';

require 'includes/header.php';

?>

<h1>User Management</h1>

<p>
    Manage registered accounts, approve new users,
    and assign user roles.
</p>

<table border="1" cellpadding="6" cellspacing="0">

    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Status</th>
        <th>Role</th>
        <th>Registered</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($users as $user): ?>
        <?php $userId = (int) $user['id']; ?>

        <tr>

            <td>
                <?= $userId ?>
            </td>

            <td>
                <?= e($user['username']) ?>

                <?= $userId === (int) $_SESSION['user_id']
                    ? ' (You)'
                    : ''
                ?>
            </td>

            <td>
                <?= e($user['status']) ?>
            </td>

            <td>
                <?= e($user['role']) ?>
            </td>

            <td>
                <?= e(date('M d, Y', strtotime($user['created_at']))) ?>
            </td>

            <td>

                <?php if ($userId === (int) $_SESSION['user_id']): ?>

                    —

                <?php else: ?>
                    <?php if ($user['status'] !== 'approved'): ?>

                        <?= user_btn(
                            $userId,
                            'approve',
                            'Approve'
                        ) ?>

                    <?php endif; ?>
                    <?php if ($user['status'] !== 'rejected'): ?>

                        <?= user_btn(
                            $userId,
                            'reject',
                            'Reject'
                        ) ?>

                    <?php endif; ?>
                    <?php if ($user['status'] === 'approved'): ?>
                        <?php if ($user['role'] === 'admin'): ?>

                            <?= user_btn(
                                $userId,
                                'make_user',
                                'Make User'
                            ) ?>

                        <?php else: ?>

                            <?= user_btn(
                                $userId,
                                'make_admin',
                                'Make Admin'
                            ) ?>

                        <?php endif; ?>

                    <?php endif; ?>

                    <?= user_btn(
                        $userId,
                        'delete',
                        'Delete',
                        'Are you sure you want to delete this user? Their products will remain in the system.'
                    ) ?>

                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>


<?php require 'includes/footer.php'; ?>

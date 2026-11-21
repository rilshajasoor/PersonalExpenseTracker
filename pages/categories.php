
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$error = '';

// Add or delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';

        if (
            $name === '' ||
            strlen($name) > 60 ||
            !in_array($type, ['income', 'expense'], true)
        ) {
            $error = 'Please enter valid category details.';
        } else {
            $stmt = db()->prepare(
                'SELECT id FROM categories WHERE name = ?'
            );
            $stmt->execute([$name]);

            if ($stmt->fetch()) {
                $error = 'This category name already exists.';
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO categories (name, type) VALUES (?, ?)'
                );
                $stmt->execute([$name, $type]);

                header('Location: categories.php?added=1');
                exit;
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);

        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM transactions WHERE category_id = ?'
        );
        $stmt->execute([$id]);
        $used = (int)$stmt->fetchColumn();

        if ($used > 0) {
            header('Location: categories.php?in_use=1');
            exit;
        }

        $stmt = db()->prepare(
            'DELETE FROM categories WHERE id = ?'
        );
        $stmt->execute([$id]);

        header('Location: categories.php?deleted=1');
        exit;
    }
}

// Get all categories
$categories = db()->query(
    'SELECT * FROM categories ORDER BY type, name'
)->fetchAll();

$page_title = 'Category Management';
require '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">MANAGEMENT</p>
        <h1>Category Management</h1>
        <p class="muted">Manage your income and expense categories.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['added'])): ?>
    <div class="alert success">Category added successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert success">Category deleted successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['in_use'])): ?>
    <div class="alert error">
        This category is being used by transactions.
        It cannot be deleted.
    </div>
<?php endif; ?>

<section class="panel">
    <h2>Add New Category</h2>

    <form method="post">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <input type="hidden" name="action" value="add">

        <label>Category Name</label>
        <input type="text" name="name"
               maxlength="60" required
               placeholder="Enter category name">

        <label>Category Type</label>
        <select name="type" required>
            <option value="expense">Expense</option>
            <option value="income">Income</option>
        </select>

        <button type="submit" class="btn">
            Add Category
        </button>
    </form>
</section>

<section class="panel">
    <h2>Available Categories</h2>

    <?php if (!$categories): ?>

        <p class="empty">No categories available.</p>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($categories as $category): ?>

                        <tr>
                            <td><?= e($category['name']) ?></td>

                            <td>
                                <span class="pill <?= e($category['type']) ?>">
                                    <?= e(ucfirst($category['type'])) ?>
                                </span>
                            </td>

                            <td>
                                <form method="post"
                                      onsubmit="return confirm('Delete this category?')">

                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrf_token()) ?>">

                                    <input type="hidden" name="action"
                                           value="delete">

                                    <input type="hidden" name="category_id"
                                           value="<?= (int)$category['id'] ?>">

                                    <button type="submit"
                                            class="text-button danger-text">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>

<?php require '../includes/footer.php'; ?>

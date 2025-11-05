<?php
require_once('php/db.php');
require_once('php/barcode.php');

session_start();

// --- AUTH ---
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT is_admin FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo "<h3>Access Denied</h3><p>You are not authorized to view this page.</p>";
    exit;
}

// --- ACTIONS ---
$message = "";

// Add new media
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_media'])) {
    $isbn = trim($_POST['isbn']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $type = $_POST['media_type'];
    $category = (int)$_POST['category_id'];
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];

    //create barcode for media
    $allBarcodes = $pdo->query("SELECT barcode FROM media")->fetchAll(PDO::FETCH_COLUMN);
    $barcode = generateBarcode($title, $allBarcodes);


    $stmt = $pdo->prepare("INSERT INTO media (isbn, barcode, title, author, media_type, category_id, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$isbn, $barcode, $title, $author, $type, $category, $desc, $price]);
    $message = "Media added successfully.";
}

// Add copy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_copy'])) {
    $mediaId = (int)$_POST['media_id'];
    $barcode = trim($_POST['barcode']);//change barcode to auto generate

    //MARK: HERE

    //make check for existing barcodes if media has noone

    //if it has just use  BarcodesForCopy function to fill in gaps



    $stmt = $pdo->prepare("INSERT INTO copy (media_id, barcode, status) VALUES (?, ?, 'available')");
    $stmt->execute([$mediaId, $barcode]);
    $message = "Copy added successfully.";
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['delete_user'];
    $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$id]);
    $message = "User ID $id deleted.";
}

// Edit user (update username, admin, password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = (int)$_POST['edit_user'];
    $username = trim($_POST['username']);
    $isAdmin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;
    $newPassword = trim($_POST['new_password']);

    if ($newPassword !== "") {
        $stmt = $pdo->prepare("UPDATE user SET username=?, password_=?, is_admin=? WHERE id=?");
        $stmt->execute([$username, $newPassword, $isAdmin, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE user SET username=?, is_admin=? WHERE id=?");
        $stmt->execute([$username, $isAdmin, $id]);
    }

    $message = "User ID $id updated.";
}

// Delete media
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media'])) {
    $id = (int)$_POST['delete_media'];
    $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
    $message = "Media ID $id deleted.";
}

// Edit media
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_media'])) {
    $id = (int)$_POST['edit_media'];
    $isbn = trim($_POST['isbn']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $type = $_POST['media_type'];
    $category = (int)$_POST['category_id'];
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];

    $stmt = $pdo->prepare("UPDATE media SET isbn=?, title=?, author=?, media_type=?, category_id=?, description=?, price=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$isbn, $title, $author, $type, $category, $desc, $price, $id]);
    $message = "Media ID $id updated.";
}

// Edit copy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_copy'])) {
    $id = (int)$_POST['edit_copy'];
    $barcode = trim($_POST['barcode']);
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE copy SET barcode=?, status=? WHERE id=?");
    $stmt->execute([$barcode, $status, $id]);
    $message = "Copy ID $id updated.";
}

// Delete copy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_copy'])) {
    $id = (int)$_POST['delete_copy'];
    $pdo->prepare("DELETE FROM copy WHERE id = ?")->execute([$id]);
    $message = "Copy ID $id deleted.";
}

// Delete loan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_loan'])) {
    $id = (int)$_POST['delete_loan'];
    $pdo->prepare("DELETE FROM loan WHERE id = ?")->execute([$id]);
    $message = "Loan ID $id deleted.";
}

// --- FETCH DATA ---
$categories = $pdo->query("SELECT id, name FROM category ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username, password_, is_admin, created_at FROM user ORDER BY id")->fetchAll();
$media = $pdo->query("
    SELECT m.id, m.isbn, m.title, m.author, m.media_type, c.name AS category, m.category_id, m.description, m.price, COUNT(cp.id) AS copies
    FROM media m
    LEFT JOIN category c ON m.category_id = c.id
    LEFT JOIN copy cp ON cp.media_id = m.id
    GROUP BY m.id
    ORDER BY m.title
")->fetchAll();
$loans = $pdo->query("
    SELECT l.id, u.username, m.title, c.barcode, l.loan_date, l.due_date, l.return_date, l.status
    FROM loan l
    JOIN user u ON u.id = l.user_id
    JOIN copy c ON c.id = l.copy_id
    JOIN media m ON c.media_id = m.id
    ORDER BY l.loan_date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/admin.js" defer></script>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <nav>
        <button id="users-btn" class="active" onclick="showTab('users')">Users</button>
        <button id="media-btn" class="nav-button" onclick="showTab('media')">Media</button>
        <button id="copies-btn" class="nav-button" onclick="showTab('copies')">Copies</button>
        <button id="loans-btn" class="nav-button" onclick="showTab('loans')">Loans</button>
        <a href="user_dashboard.php" class="back action-button">← Back to User view</a>
    </nav>

    <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- USERS TAB -->
    <div id="users" class="tab">
        <h2>Users</h2>
        <table>
            <tr><th>ID</th><th>Username</th><th>Password</th><th>Admin</th><th>Created</th><th>Actions</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['password_']); ?></td>
                <td><?php echo $u['is_admin'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $u['created_at']; ?></td>
                <td>
                    <button type="button" class="edit action-button" onclick="toggleEditForm(<?php echo $u['id']; ?>, 'user')">Edit</button>
                    <?php if ($u['id'] != $userId): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_user" value="<?php echo $u['id']; ?>">
                        <button class="delete action-button" type="submit">Delete</button>
                    </form>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <tr id="edit-form-user-<?php echo $u['id']; ?>" class="hidden">
                <td colspan="6">
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="edit_user" value="<?php echo $u['id']; ?>">
                        <label>Username:</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required>

                        <label>New Password (leave blank to keep current):</label>
                        <input type="text" name="new_password" placeholder="Enter new password">

                        <label>Admin:</label>
                        <select name="is_admin">
                            <option value="0" <?php if(!$u['is_admin']) echo 'selected'; ?>>No</option>
                            <option value="1" <?php if($u['is_admin']) echo 'selected'; ?>>Yes</option>
                        </select>

                        <button type="submit">Save Changes</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- MEDIA TAB -->
    <div id="media" class="tab hidden">
        <h2>Media</h2>

        <!-- Add Media Form -->
        <form method="POST" class="add-form">
            <h3>Add New Media</h3>
            <input type="text" name="isbn" placeholder="ISBN" required>
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <select name="media_type">
                <option value="bok">Book</option>
                <option value="ljudbok">Audiobook</option>
                <option value="film">Film</option>
            </select>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price (kr)">
            <button type="submit" name="add_media">Add Media</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Media Type</th>
                <th>Category ID</th>
                <th>Description</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($media as $m): ?>
            <tr>
                <td><?php echo $m['id']; ?></td>
                <td><?php echo htmlspecialchars($m['isbn']); ?></td>
                <td><?php echo htmlspecialchars($m['title']); ?></td>
                <td><?php echo htmlspecialchars($m['author']); ?></td>
                <td><?php echo htmlspecialchars($m['media_type']); ?></td>
                <td><?php echo htmlspecialchars($m['category_id']); ?></td>
                <td><?php echo htmlspecialchars($m['description']); ?></td>
                <td><?php echo htmlspecialchars($m['price']); ?></td>
                <td>
                    <button type="button" class="edit action-button" onclick="toggleEditForm(<?php echo $m['id']; ?>, 'media')">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden"  name="delete_media" value="<?php echo $m['id']; ?>">
                        <button class="delete action-button" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-form-media-<?php echo $m['id']; ?>" class="hidden">
                <td colspan="8">
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="edit_media" value="<?php echo $m['id']; ?>">
                        <label>ISBN:</label>
                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($m['isbn']); ?>" required>
                        <label>Title:</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($m['title']); ?>" required>
                        <label>Author:</label>
                        <input type="text" name="author" value="<?php echo htmlspecialchars($m['author']); ?>" required>
                        <label>Media Type:</label>
                        <select name="media_type">
                            <option value="bok" <?php if($m['media_type']=='bok') echo 'selected'; ?>>Book</option>
                            <option value="ljudbok" <?php if($m['media_type']=='ljudbok') echo 'selected'; ?>>Audiobook</option>
                            <option value="film" <?php if($m['media_type']=='film') echo 'selected'; ?>>Film</option>
                        </select>
                        <label>Category:</label>
                        <select name="category_id">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if($c['id']==$m['category_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Description:</label>
                        <textarea name="description"><?php echo htmlspecialchars($m['description']); ?></textarea>
                        <label>Price:</label>
                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($m['price']); ?>">
                        <button type="submit">Save Changes</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- COPIES TAB -->
    <div id="copies" class="tab hidden">
        <h2>Copies</h2>

        <!-- Add Copy Form -->
        <form method="POST" class="add-form">
            <h3>Add Copy</h3>
            <select name="media_id" required>
                <option value="">-- Select Media --</option>
                <?php foreach ($media as $m): ?>
                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="barcode" placeholder="Barcode" required>
            <button type="submit" name="add_copy">Add Copy</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Media ID</th>
                <th>Barcode</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $copies = $pdo->query("SELECT id, media_id, barcode, status FROM copy ORDER BY id DESC")->fetchAll();
            foreach ($copies as $cp): ?>
            <tr>
                <td><?php echo $cp['id']; ?></td>
                <td><?php echo htmlspecialchars($cp['media_id']); ?></td>
                <td><?php echo htmlspecialchars($cp['barcode']); ?></td>
                <td><?php echo htmlspecialchars($cp['status']); ?></td>
                <td>
                    <button type="button" class="edit action-button" onclick="toggleEditForm(<?php echo $cp['id']; ?>, 'copy')">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_copy" value="<?php echo $cp['id']; ?>">
                        <button class="delete action-button" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-form-copy-<?php echo $cp['id']; ?>" class="hidden">
                <td colspan="5">
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="edit_copy" value="<?php echo $cp['id']; ?>">
                        <label>Barcode:</label>
                        <input type="text" name="barcode" value="<?php echo htmlspecialchars($cp['barcode']); ?>" required>
                        <label>Status:</label>
                        <select name="status">
                            <option value="available" <?php if($cp['status']=='available') echo 'selected'; ?>>Available</option>
                            <option value="on_loan" <?php if($cp['status']=='on_loan') echo 'selected'; ?>>On Loan</option>
                            <option value="lost" <?php if($cp['status']=='lost') echo 'selected'; ?>>Lost</option>
                            <option value="written_off" <?php if($cp['status']=='written_off') echo 'selected'; ?>>Written Off</option>
                        </select>
                        <button type="submit">Save Changes</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>


    <!-- LOANS TAB -->
    <div id="loans" class="tab hidden">
        <h2>Loans</h2>
        <table>
            <tr><th>ID</th><th>User</th><th>Media</th><th>Barcode</th><th>Loan Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($loans as $l): ?>
            <tr>
                <td><?php echo $l['id']; ?></td>
                <td><?php echo htmlspecialchars($l['username']); ?></td>
                <td><?php echo htmlspecialchars($l['title']); ?></td>
                <td><?php echo htmlspecialchars($l['barcode']); ?></td>
                <td><?php echo $l['loan_date']; ?></td>
                <td><?php echo $l['due_date']; ?></td>
                <td><?php echo $l['return_date'] ?? '—'; ?></td>
                <td><?php echo $l['status']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="delete_loan" value="<?php echo $l['id']; ?>">
                        <button class="delete action-button" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>

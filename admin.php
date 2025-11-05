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
    $isan = trim($_POST['isan']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $type = $_POST['media_type'];

    $sabcode_preset = (int)$_POST['sab_code_preset'];
    $sabcode_custom = trim($_POST['sab_code_custom']);
    $sabcode = ($sabcode_preset === 'custom') ? $sabcode_custom : $sabcode_preset;

    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];

    //create barcode for media
    $allBarcodes = $pdo->query("SELECT barcode FROM media")->fetchAll(PDO::FETCH_COLUMN);
    $barcode = generateBarcode($title, $allBarcodes);


    $stmt = $pdo->prepare("INSERT INTO media (isbn, isan, barcode, title, author, media_type, sab_code, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$isbn, $isan, $barcode, $title, $author, $type, $sabcode, $desc, $price]);
    $message = "Media added successfully.";
}

// Add copy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_copy'])) {
    $mediaId = (int)$_POST['media_id'];
  //  $barcode = trim($_POST['barcode']);//changed barcode to auto generate
    $amountOfCopies = (int)($_POST['amount'] ?? 1); //defualt 1 copy

    $stmt = $pdo->prepare("SELECT barcode FROM copy WHERE media_id = :media_id");
    $stmt->execute(['media_id' => $mediaId]);
    $barcodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //maybe remove second sql query
    $stmt = $pdo->prepare("SELECT barcode FROM media WHERE id = :media_id");
    $stmt->execute(['media_id' => $mediaId]);
    $barcode = $stmt->fetchColumn();

    $newBarcodes=BarcodesForCopy($amountOfCopies, $barcodes, $barcode);

    $values = [];
    $params = [];

    foreach ($newBarcodes as $code) {
        $values[] = "(?, ?, 'available')";
        $params[] = $mediaId;
        $params[] = $code;
    }
    
    $sql = "INSERT INTO copy (media_id, barcode, status) VALUES " . implode(", ", $values);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $message = "Inserted " . count($newBarcodes) . " copies successfully. " . implode(", ", $newBarcodes);
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
        $stmt = $pdo->prepare("UPDATE user SET username=?, passwordhash=?, is_admin=? WHERE id=?");
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
    $isan = trim($_POST['isan']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $type = $_POST['media_type'];

    $sabcode_preset = $_POST['sab_code_preset'];
    $sabcode_custom = trim($_POST['sab_code_custom']);
    $sabcode = ($sabcode_preset === 'custom') ? $sabcode_custom : $sabcode_preset;

    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];

    $stmt = $pdo->prepare("UPDATE media SET isbn=?, isan=?, title=?, author=?, media_type=?, sab_code=?, description=?, price=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$isbn, $isan, $title, $author, $type, $sabcode, $desc, $price, $id]);
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
$sabcategories = $pdo->query("SELECT sab_code, name FROM sab_category ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username, is_admin, created_at FROM user ORDER BY id")->fetchAll();
$media = $pdo->query("
    SELECT m.id, m.isbn, m.isan, m.title, m.author, m.media_type, m.sab_code, m.description, m.price, COUNT(cp.id) AS copies
    FROM media m
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
            <tr><th>Username</th><th>Admin</th><th>Created</th><th>Active loans</th><th>Late loan</th><th>Total Loans</th><th>Total debt</th><th>Actions</th></tr>
            <?php foreach ($users as $u):?>
            <tr>
                
                
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo $u['is_admin'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $u['created_at']; ?></td>
                <td>
                    <?php
                    $activeLoansStmt = $pdo->prepare("SELECT COUNT(*) FROM loan WHERE user_id = ? AND return_date IS NULL");
                    $activeLoansStmt->execute([$u['id']]);
                    echo $activeLoansStmt->fetchColumn();
                    ?>
                </td>
                <td>
                    <?php
                    $lateLoanStmt = $pdo->prepare("SELECT COUNT(*) FROM loan WHERE user_id = ? AND return_date IS NULL AND due_date < CURDATE()");
                    $lateLoanStmt->execute([$u['id']]);
                    echo $lateLoanStmt->fetchColumn();
                    ?>
                </td>
                <td>
                    <?php
                    $totalLoansStmt = $pdo->prepare("SELECT COUNT(*) FROM loan WHERE user_id = ?");
                    $totalLoansStmt->execute([$u['id']]);
                    echo $totalLoansStmt->fetchColumn();
                    ?>
                </td>
                <td>
                    <?php
                    $debtStmt = $pdo->prepare("SELECT SUM(amount) FROM invoice WHERE user_id = ?");
                    $debtStmt->execute([$u['id']]);
                    $debt = $debtStmt->fetchColumn();
                    echo $debt ? number_format($debt, 2) . " kr" : "0 kr";
                    ?>
                </td>
                
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
            <input type="text" name="isbn" placeholder="ISBN">
            <input type="text" name="isan" placeholder="ISAN">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <select name="media_type">
                <option value="bok">Book</option>
                <option value="ljudbok">Audiobook</option>
                <option value="film">Film</option>
            </select>
            <!-- We shold have the preset dropdown or CUSTOM which allows entering string, SAB is always string -->
            <select id="add-media-sab-preset" name="sab_code_preset" required>
                <option value="">-- Select SAB Category --</option>
                <?php foreach ($sabcategories as $c): ?>
                    <option value="<?php echo $c['sab_code']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
                <option value="custom">Custom</option>
            </select>
            <input id="add-media-sab-custom"  type="text" name="sab_code_custom" placeholder="Custom SAB Code" class="hidden">
            
            <textarea name="description" placeholder="Description"></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price (kr)">
            <button type="submit" name="add_media">Add Media</button>
        </form>

        <table>
            <tr>
                <th>ISBN</th>
                <th>ISAN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Media Type</th>
                <th>SAB</th>
                <th>Description</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($media as $m): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['isbn']); ?></td>
                <td><?php echo htmlspecialchars($m['isan']); ?></td>
                <td><?php echo htmlspecialchars($m['title']); ?></td>
                <td><?php echo htmlspecialchars($m['author']); ?></td>
                <td><?php echo htmlspecialchars($m['media_type']); ?></td>
                <td><?php echo htmlspecialchars($m['sab_code']); ?></td>
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
                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($m['isbn']); ?>">
                        <label>ISAN:</label>
                        <input type="text" name="isan" value="<?php echo htmlspecialchars($m['isan']); ?>">
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
                        <label>SAB:</label>
                        <select data-id="<?php echo $m['id']; ?>" class="edit-media-sab-preset" name="sab_code_preset" required>
                            <?php foreach ($sabcategories as $c): ?>
                                <option value="<?php echo $c['sab_code']; ?>" <?php if($m['sab_code']==$c['sab_code']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="custom" <?php if(!in_array($m['sab_code'], array_column($sabcategories, 'sab_code'))) echo 'selected'; ?>>Custom</option>
                        </select>
                        <input data-id="<?php echo $m['id']; ?>" type="text" name="sab_code_custom" placeholder="Custom SAB Code" value="<?php if(!in_array($m['sab_code'], array_column($sabcategories, 'sab_code'))) echo htmlspecialchars($m['sab_code']); ?>" class="edit-media-sab-custom<?php if(in_array($m['sab_code'], array_column($sabcategories, 'sab_code'))) echo ' hidden'; ?>">
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
            <input type="number" name="amount" min="1" max="1000" value="1" required >
            <button type="submit" name="add_copy">Add Copy</button>
        </form>

        <table>
            <tr>
                <th>Media ID</th>
                <th>Barcode</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $copies = $pdo->query("SELECT id, media_id, barcode, status FROM copy ORDER BY id DESC")->fetchAll();
            foreach ($copies as $cp): ?>
            <tr>
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

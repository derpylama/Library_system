<?php
require_once('php/db.php');
require_once('php/search.php');
require_once('php/images.php');
require_once('php/account.php');

@session_start();

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Fetch user role
    $stmt = $pdo->prepare("SELECT is_admin FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = $user && $user['is_admin'] == 1;
    
    $message = "";
    
    // Handle loan creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'])) {
    
        $mediaId = (int)$_POST['media_id'];
        $stmt = $pdo->prepare("SELECT id FROM copy WHERE media_id = ? AND status = 'available' LIMIT 1");
        $stmt->execute([$mediaId]);
        $copy = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($copy) {
            $copyId = $copy['id'];
            $loanDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+21 days'));
            $pdo->beginTransaction();
            try {
                $pdo->prepare("INSERT INTO loan (copy_id, user_id, loan_date, due_date, status) VALUES (?, ?, ?, ?, 'active')")
                    ->execute([$copyId, $userId, $loanDate, $dueDate]);
                $pdo->prepare("UPDATE copy SET status = 'on_loan' WHERE id = ?")->execute([$copyId]);
                $pdo->commit();
                $message = "Successfully loaned media ID $mediaId!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error processing loan: " . $e->getMessage();
            }
        } else {
            $message = "No available copies for this media.";
        }
    
    
    }
    // Handle returning a loan
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_loan_id'])) {
        $loanId = (int)$_POST['return_loan_id'];
        $pdo->beginTransaction();
        try {
            $loanStmt = $pdo->prepare("SELECT * FROM loan WHERE id = ? AND user_id = ?");
            $loanStmt->execute([$loanId, $_SESSION['user_id']]);
            $loan = $loanStmt->fetch();
            if ($loan && $loan['status'] === 'active') {
                $pdo->prepare("UPDATE loan SET status = 'returned', return_date = CURDATE() WHERE id = ?")->execute([$loanId]);
                $pdo->prepare("UPDATE copy SET status = 'available' WHERE id = ?")->execute([$loan['copy_id']]);
                $message = "Returned loan #$loanId successfully.";
            } else {
                $message = "Loan not found or already returned.";
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error returning loan: " . $e->getMessage();
        }
    }
}



// Fetch all media
$mediaQuery = "
SELECT 
  m.*,
  COUNT(c.id) AS total_copies,
  SUM(CASE WHEN c.status = 'available' THEN 1 ELSE 0 END) AS available_copies,
  SUM(CASE WHEN c.status = 'on_loan' THEN 1 ELSE 0 END) AS loaned_copies
FROM media m
LEFT JOIN copy c ON c.media_id = m.id
GROUP BY m.id
ORDER BY m.title;
";
$mediaList = $pdo->query($mediaQuery)->fetchAll(PDO::FETCH_ASSOC);

$searchResults = [];
if (isset($_GET["q"]) && !empty(trim($_GET["q"]))) {
    $searchTerm = trim($_GET["q"]);
    // Returns [{mediaId, score, matches=[{field,index,length,score,token},...]},...]
    $searchResults = SearchMedia($mediaList, $searchTerm);
    echo "
    <script>
    console.log('Search Term: " . $searchTerm . "');
    console.log('Search Results: ', JSON.parse('" . json_encode($searchResults) . "'));
    </script>
    ";
}


// Fetch user's active and past loans
$loanQuery = "
SELECT l.*, m.title, m.author, c.barcode, m.price, 
DATEDIFF(l.due_date, CURDATE()) AS days_left
FROM loan l
JOIN copy c ON l.copy_id = c.id
JOIN media m ON c.media_id = m.id
WHERE l.user_id = ?
ORDER BY l.loan_date DESC;
";

if (isset($_SESSION['user_id'])) {
    $loanStmt = $pdo->prepare($loanQuery);
    $loanStmt->execute([$_SESSION['user_id']]);
    $userLoans = $loanStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user invoices
    $invoiceStmt = $pdo->prepare("SELECT * FROM invoice WHERE user_id = ? ORDER BY issued_at DESC");
    $invoiceStmt->execute([$_SESSION['user_id']]);
    $invoices = $invoiceStmt->fetchAll(PDO::FETCH_ASSOC);    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/index.js"></script>
</head>
<body>
    <!-- popup-wrapper-with-backdrop or with-click-through are functional classes -->
    <div id="popup-wrapper" class="popup-wrapper-with-backdrop">
        <?php

            // If a div with class "popup" and not class "hidden" exists here it is automatically rendered as a popup

        ?>
    </div>

    <header>
        <?php 
            if (isset($_SESSION["user_id"])){
                echo "<h2>Welcome, " . htmlspecialchars($username) . "</h2>";
            }else{
                echo "<h2>Welcome</h2>";
            }
        ?>
        <p id="top-menu-message" style="display: none;"></p>

        <div>
            <?php
                if (isset($_SESSION['user_id']) && $isAdmin) {
                    echo '<button class="toggle-btn nav-button" id="all-media-button" onclick="toggleView(\'all-media-view\')">All Media</button>';
                    echo '<button class="toggle-btn nav-button" id="my-account-button" onclick="toggleView(\'my-account-view\')">My Account</button>';
                    echo '<a href="admin.php" class="toggle-btn admin-button">Admin Panel</a>';
                    echo '<a href="./logout.php" class="toggle-btn logout-button">Logout</a>';
                }
                else if (isset($_SESSION['user_id'])) {
                    echo '<button class="toggle-btn nav-button" id="all-media-button" onclick="toggleView(\'all-media-view\')">All Media</button>';
                    echo '<button class="toggle-btn nav-button" id="my-account-button" onclick="toggleView(\'my-account-view\')">My Account</button>';
                    echo '<a href="./logout.php" class="toggle-btn logout-button">Logout</a>';
                }
                else {
                    echo '<button class="toggle-btn nav-button" id="all-media-button" onclick="toggleView(\'all-media-view\')">All Media</button>';
                    echo '<a href="login.php" class="toggle-btn nav-button">Login</a>';
                }
            ?>
        </div>
    </header>

    <!-- All Media View -->
    <section id="all-media-view">

        <!-- search bar -->
        <div class="search-bar">
            <form method="GET" action="index.php">
                <input type="text" name="q" placeholder="Search media..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <select name="typefilter">
                    
                    <!--
                    <option value="all">All Types</option>
                    <option value="book">Books</option>
                    <option value="audiobook">Audiobooks</option>
                    <option value="film">Films</option>
                    -->

                    <?php
                    // Select if $_GET typefilter is set
                    $typefilter = isset($_GET['typefilter']) ? $_GET['typefilter'] : 'all';
                    echo '<option value="all"' . ($typefilter === 'all' ? ' selected' : '') . '>All Types</option>';
                    echo '<option value="bok"' . ($typefilter === 'bok' ? ' selected' : '') . '>Books</option>';
                    echo '<option value="ljudbok"' . ($typefilter === 'ljudbok' ? ' selected' : '') . '>Audiobooks</option>';
                    echo '<option value="film"' . ($typefilter === 'film' ? ' selected' : '') . '>Films</option>';
                    ?>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="grid">
            
            <?php
            $filteredMediaList = []; // Maps [-1/score, <media>]
            foreach ($mediaList as $media) {

                $totalScore = 0;

                // Iterate each field, does it exists as a match in a search result? If so highlight the matching part
                //   also keep track if this media had any matches at all in search results if not and search result is not empty skip it
                // $searchResults = [{mediaId, score, matches=[{field,index,length,score,token},...]},...]
                if (count($searchResults) > 0) {
                    $foundInSearch = false;

                    foreach ($media as $field => $value) {
                        $foundField = false;
                        
                        // Find if this media has matches in search results
                        foreach ($searchResults as $result) {
                            if ($result['mediaId'] == $media['id']) {
                                // Check matches for this field
                                foreach ($result['matches'] as $match) {
                                    if ($match['field'] === $field) {
                                        // Append to total score
                                        $totalScore += $match['score'];

                                        // Get
                                        $start = $match['index'];
                                        $length = $match['length'];
                                        $before = htmlspecialchars(mb_substr($value, 0, $start));
                                        $matchText = htmlspecialchars(mb_substr($value, $start, $length));
                                        $after = htmlspecialchars(mb_substr($value, $start + $length));

                                        // Validate so we arent inside another highlight or trying to highlight somewhere inside a '<span class="search-highlight">' or '</span>'
                                        $isValidLocation = true;
                                        // Check so we arent inside another highlight tags
                                        $highlightStartTag = '<span class="search-highlight">';
                                        $highlightEndTag = '</span>';
                                        // Get indexes of all highlight-start and highlight-end tags and check if our match is inside any of them fully
                                        // MARK: TODO:...

                                        // Iterate all previous matches where we have same field and check if our index+len is inside any of their index+len, if so invalidate.
                                        //   If our match overlaps out of the other match its still valid but we should not highlight the overlapping part, i.e change index if we overlap at end and change length if we overlap at start.
                                        // MARK: TODO:...

                                        // Highlight match in value
                                        if ($isValidLocation === true) {
                                            $value = $before . '<span class="search-highlight">' . $matchText . '</span>' . $after;
                                            $media[$field] = $value;
                                        }

                                        // Mark as found
                                        $foundField = true;
                                        $foundInSearch = true;
                                    }
                                }
                            }
                        }

                        // If not htmlspecialchars the value
                        if (!$foundField) {
                            $media[$field] = htmlspecialchars($value);
                        }

                    }

                    if (!$foundInSearch) {
                        continue; // Skip this media, no matches found
                    }
                }

                // If $_GET typefilter is set filter by media type
                if (isset($_GET['typefilter']) && $_GET['typefilter'] !== 'all') {
                    if ($media['media_type'] !== $_GET['typefilter']) {
                        continue; // Skip this media, type does not match filter
                    }
                }

                $filteredMediaList[] = [$totalScore, $media];
            }

            // Sort $filteredMediaList by score descending
            usort($filteredMediaList, function($a, $b) {
                return $b[0] <=> $a[0];
            });

            foreach ($filteredMediaList as $mediaWithScore) {
                $media = $mediaWithScore[1];

                if (isset($media["image_width"]) && isset($media["image_height"])) {
                    $imageSize = [intval($media["image_width"]), intval($media["image_height"])];
                } else {
                    $imageSize = getImageSizeW($media['image_url']);
                }

                $showsISBNorISAN = ($media['media_type'] !== 'film') ? isset($media['isbn']) : isset($media['isan']);

                echo '
                <div class="card" '.cardSize($imageSize).'>
                    <div class="media-title-container">
                        <h3>' . $media['title'] . '</h3>
                    </div>
                    <div class="media-image-container">
                    ' . imageType($media['image_url'], $imageSize) . '
                    </div>
                    <p class="description">' . nl2br($media['description']) . '</p>
                    <p><strong>Author/Director:</strong> ' . $media['author'] . '</p>
                    <p><strong>Type:</strong> ' . $media['media_type'] . '</p>
                    <p>
                    ' . (
                        $media['media_type'] !== 'film'
                        ? (
                            isset($media['isbn'])
                            ? "<strong>ISBN: </strong>" . $media['isbn'] . "<br>"
                            : ""
                        )
                        : (
                            isset($media['isan'])
                            ? "<strong>ISAN: </strong>" . $media['isan'] . "<br>"
                            : ""
                        )
                    ) . '
                        <strong>SAB:</strong> ' . ($media['sab_code'] ?? 0) . '<br>
                    </p>';
                        
                echo '    
                        <strong>Avaliability:</strong> ' . ($media['available_copies'] ?? 'N/A') . ' of ' . ($media['total_copies'] ?? 'N/A') . '<br>
                    </p>
                    ' . ($showsISBNorISAN ? '' : '<br>') . '
                    <form method="POST">
                        <input type="hidden" name="media_id" value="' . $media['id'] . '">
                        <button type="submit" ' . (($media['available_copies'] == 0) ? 'disabled' : '') . '>
                            ' . (($media['available_copies'] == 0) ? 'No Copies Available' : 'Loan This Media') . '
                        </button>
                    </form>
                </div>
                ';
            }
            ?>
        </div>
    </section>

    <!-- My account -->
    <section id="my-account-view" class="hidden">

        <!-- Account -->
        <section id="account-section" class="my-account-view-section">
            <h3>Your Account</h3>
            <?=showAccountButton();?>
            <?=passwordChangeMessage();?>
        </section>

        <!-- Loans -->
        <section id="loans-section" class="my-account-view-section">
            <h3>Your Loans</h3>
            <?php if (empty($userLoans)): ?>
                <p>You currently have no loans.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($userLoans as $loan): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($loan['title']); ?></h3>
                            <p><strong>Author/Director:</strong> <?php echo htmlspecialchars($loan['author']); ?></p>
                            <p><strong>Barcode:</strong> <?php echo htmlspecialchars($loan['barcode']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($loan['status']); ?></p>
                            <p><strong>Due:</strong> <?php echo htmlspecialchars($loan['due_date']); ?></p>
                            <?php if ($loan['status'] === 'active'): ?>
                                <p>
                                    <?php
                                    $days = $loan['days_left'];
                                    if ($days < 0) echo "<span class='overdue'>Overdue by " . abs($days) . " days</span>";
                                    else echo "Due in $days days";
                                    ?>
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="return_loan_id" value="<?php echo $loan['id']; ?>">
                                    <button type="submit">Return Media</button>
                                </form>
                            <?php elseif ($loan['status'] === 'returned'): ?>
                                <p class="returned">Returned on <?php echo htmlspecialchars($loan['return_date']); ?></p>
                            <?php else: ?>
                                <p class="overdue">Written off / overdue</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Invoices -->
        <section id="invoices-section" class="my-account-view-section">
            <h3>Your Invoices</h3>
            <?php if (empty($invoices)): ?>
                <p>No invoices.</p>
            <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                    <div class="invoice">
                        <p><strong>Issued:</strong> <?php echo $inv['issued_at']; ?></p>
                        <p><strong>Amount:</strong> <?php echo $inv['amount']; ?> kr</p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($inv['description']); ?></p>
                        <p><strong>Status:</strong> <?php echo $inv['paid'] ? 'Paid' : 'Unpaid'; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </section>
</body>
</html>

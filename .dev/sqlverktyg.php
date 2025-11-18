<?php
// sql_runner.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function render_form($host='', $port='', $user='', $pass='', $db='') {
    echo <<<HTML
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>SQL Verktyg</title>
<style>
    body { font-family: Arial, sans-serif; margin: 2em; background: #f7f7f7; }
    form { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 8px #ccc; }
    input, textarea, button { width: 100%; margin: 8px 0; padding: 8px; font-size: 14px; }
    textarea { height: 150px; resize: vertical; }
    .results { margin-top: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px #ccc; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
    th { background: #eee; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; white-space: pre-wrap; }
</style>
</head>
<body>
<h2>MySQL Verktyg</h2>
<form method="post" enctype="multipart/form-data">
    <label>Adress:</label>
    <input type="text" name="host" value="{$host}" required>
    <label>Port:</label>
    <input type="text" name="port" value="{$port}" placeholder="3306">
    <label>Användarnamn:</label>
    <input type="text" name="user" value="{$user}" required>
    <label>Lösenord:</label>
    <input type="password" name="pass" value="{$pass}">
    <label>Databas:</label>
    <input type="text" name="db" value="{$db}" required>

    <label>Ladda upp en SQL fil (valfriff):</label>
    <input type="file" name="sqlfile" accept=".sql">

    <label>Eller ange SQL manuellt:</label>
    <textarea name="sqlquery" placeholder="Ange SQL här..."></textarea>

    <button type="submit" name="run">Kör SQL</button>

    <i>Verktyg gjort av <a href="https://github.com/sbamboo">@sbamboo</a> (SimonKC)</i>
</form>
HTML;
}

function render_results($output) {
    echo "<div class='results'>{$output}</div></body></html>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run'])) {
    $host = $_POST['host'];
    $port = $_POST['port'] ?: 3306;
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $db   = $_POST['db'];

    render_form($host, $port, $user, $pass, $db);

    $sql = '';
    if (!empty($_FILES['sqlfile']['tmp_name'])) {
        $sql = file_get_contents($_FILES['sqlfile']['tmp_name']);
    } elseif (!empty($_POST['sqlquery'])) {
        $sql = $_POST['sqlquery'];
    }

    if (trim($sql) === '') {
        render_results("<p class='error'>Ingen SQL angiven.</p>");
        exit;
    }

    $mysqli = @new mysqli($host, $user, $pass, $db, $port);

    if ($mysqli->connect_errno) {
        render_results("<p class='error'>Databas-kontakt misslyckades: {$mysqli->connect_error}</p>");
        exit;
    }

    $multi = $mysqli->multi_query($sql);
    if (!$multi) {
        render_results("<p class='error'>Fel: {$mysqli->error}</p>");
        $mysqli->close();
        exit;
    }

    $output = "";
    do {
        if ($result = $mysqli->store_result()) {
            $output .= "<table><tr>";
            $fields = $result->fetch_fields();
            foreach ($fields as $f) {
                $output .= "<th>{$f->name}</th>";
            }
            $output .= "</tr>";

            while ($row = $result->fetch_assoc()) {
                $output .= "<tr>";
                foreach ($row as $val) {
                    $output .= "<td>" . htmlspecialchars((string)$val) . "</td>";
                }
                $output .= "</tr>";
            }
            $output .= "</table><br>";
            $result->free();
        } else {
            if ($mysqli->errno) {
                $output .= "<p class='error'>Fel: {$mysqli->error}</p>";
            } else {
                $output .= "<p class='success'>Status: Lyckades!</p>";
            }
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    $mysqli->close();
    render_results($output);

} else {
    render_form();
}
?>

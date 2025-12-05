<?php
$xmlFile  = __DIR__ . '/broneeringud.xml';
$jsonFile = __DIR__ . '/broneeringud.json';

function xml_to_array($xmlPath)
{
    $xml = simplexml_load_file($xmlPath);
    $data = array();

    foreach ($xml->paev as $paev) {
        foreach ($paev->broneering as $br) {
            $data[] = array(
                'kuupaev'    => (string)$paev['kuupaev'],
                'kellaaeg'   => (string)$br['kellaaeg'],
                'nimi'       => (string)$br['nimi'],
                'telefon'    => (string)$br['telefon'],
                'teenus'     => (string)$br['teenus'],
                'autonumber' => (string)$br['autonumber'],
            );
        }
    }

    return $data;
}

// kui JSON faili veel pole, loome selle XML põhjal
if (!file_exists($jsonFile)) {
    $data = xml_to_array($xmlFile);
    file_put_contents(
        $jsonFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

// vormi töötlemine – lisame uue kirje JSON faili
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = array(
        'kuupaev'    => isset($_POST['kuupaev']) ? $_POST['kuupaev'] : '',
        'kellaaeg'   => isset($_POST['kellaaeg']) ? $_POST['kellaaeg'] : '',
        'nimi'       => isset($_POST['nimi']) ? $_POST['nimi'] : '',
        'telefon'    => isset($_POST['telefon']) ? $_POST['telefon'] : '',
        'teenus'     => isset($_POST['teenus']) ? $_POST['teenus'] : '',
        'autonumber' => isset($_POST['autonumber']) ? $_POST['autonumber'] : '',
    );

    $json = file_get_contents($jsonFile);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $data = array();
    }

    $data[] = $new;

    file_put_contents(
        $jsonFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $message = 'Uus broneering lisatud JSON faili.';
}

// loeme kõik andmed JSON-ist tabeli jaoks
$json = file_get_contents($jsonFile);
$data = json_decode($json, true);
if (!is_array($data)) {
    $data = array();
}

$jsonContent = htmlspecialchars(
    file_get_contents($jsonFile),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>JSON broneeringud</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-link">Broneeringud</a>
    <a href="json.php" class="nav-link active">Lisa broneering</a>
</nav>

<div class="container">
    <h1>Broneeringud JSON-failis</h1>
    <p class="subtitle">
        JSON loodud XML jada põhjal, uued broneeringud lisatakse PHP abil.
    </p>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- TABEL KÕIGIST JSON ANDMETEST -->
    <h2>JSON andmete tabel</h2>
    <?php if (empty($data)): ?>
        <p class="no-results">JSON failis pole veel andmeid.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
            <tr>
                <th>Kuupäev</th>
                <th>Kellaaeg</th>
                <th>Nimi</th>
                <th>Telefon</th>
                <th>Teenus</th>
                <th>Autonumber</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $r): ?>
                <tr>
                    <td><?php echo $r['kuupaev']; ?></td>
                    <td><?php echo $r['kellaaeg']; ?></td>
                    <td><?php echo $r['nimi']; ?></td>
                    <td><?php echo $r['telefon']; ?></td>
                    <td><?php echo $r['teenus']; ?></td>
                    <td><?php echo $r['autonumber']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="search-section">
        <h2>Lisa broneering JSON faili</h2>
        <form class="search-card" method="post">
            <label>Kuupäev
                <input type="date" name="kuupaev" required>
            </label>
            <label>Kellaaeg
                <input type="time" name="kellaaeg" required>
            </label>
            <label>Nimi
                <input type="text" name="nimi" required>
            </label>
            <label>Telefon
                <input type="text" name="telefon" required>
            </label>
            <label>Teenus
                <input type="text" name="teenus" required>
            </label>
            <label>Autonumber
                <input type="text" name="autonumber" required>
            </label>
            <button type="submit">Lisa JSON faili</button>
        </form>
    </div>

    <details>
        <summary>Näita JSON faili sisu (toores JSON)</summary>
        <pre><?php echo $jsonContent; ?></pre>
    </details>
</div>
</body>
</html>

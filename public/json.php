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
                'kellaaeg'   => (string)$br->kellaaeg,
                'nimi'       => (string)$br->nimi,
                'telefon'    => (string)$br->telefon,
                'teenus'     => (string)$br->teenus,
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // loeme uued andmed vormist
    $kuupaev    = isset($_POST['kuupaev']) ? $_POST['kuupaev'] : '';
    $kellaaeg   = isset($_POST['kellaaeg']) ? $_POST['kellaaeg'] : '';
    $nimi       = isset($_POST['nimi']) ? $_POST['nimi'] : '';
    $telefon    = isset($_POST['telefon']) ? $_POST['telefon'] : '';
    $teenus     = isset($_POST['teenus']) ? $_POST['teenus'] : '';
    $autonumber = isset($_POST['autonumber']) ? $_POST['autonumber'] : '';

    // 1) lisame uue broneeringu XML faili
    $xml = simplexml_load_file($xmlFile);

    // otsime, kas selle kuupäevaga <paev> juba olemas
    $targetPaev = null;
    foreach ($xml->paev as $paev) {
        if ((string)$paev['kuupaev'] === $kuupaev) {
            $targetPaev = $paev;
            break;
        }
    }

    // kui päev puudub, loome uue
    if ($targetPaev === null) {
        $targetPaev = $xml->addChild('paev');
        $targetPaev->addAttribute('kuupaev', $kuupaev);
    }

    // lisame sinna uue <broneering>
    $br = $targetPaev->addChild('broneering');
    $br->addAttribute('autonumber', $autonumber);
    $br->addChild('telefon', $telefon);
    $br->addChild('nimi', $nimi);
    $br->addChild('kellaaeg', $kellaaeg);
    $br->addChild('teenus', $teenus);

    // salvestame XML uuesti faili
    $xml->asXML($xmlFile);

    // 2) uuendame JSON faili XML põhjal
    $data = xml_to_array($xmlFile);
    file_put_contents(
        $jsonFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $message = 'Uus broneering lisatud XML ja JSON faili.';
}

// näitame JSON sisu ainult infoks
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
    <title>Lisa broneering</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-link">Broneeringud</a>
    <a href="json.php" class="nav-link active">Lisa broneering</a>
</nav>

<div class="container">
    <h1>Lisa broneering</h1>
    <p class="subtitle">
        Uus broneering salvestatakse XML faili ja selle põhjal uuendatakse JSON faili.
    </p>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="search-section">
        <h2>Uus broneering</h2>
        <!-- добавлен класс form-compact -->
        <form class="search-card form-compact" method="post">
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
            <button type="submit">Lisa</button>
        </form>
    </div>

    <details>
        <summary>Näita JSON faili sisu (informatiivne)</summary>
        <pre><?php echo $jsonContent; ?></pre>
    </details>
</div>
</body>
</html>

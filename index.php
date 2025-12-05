<?php
function load_xml()
{
    return simplexml_load_file(__DIR__ . '/broneeringud.xml');
}

function search_by_car($car)
{
    $xml = load_xml();
    $result = array();

    foreach ($xml->paev as $paev) {
        foreach ($paev->broneering as $br) {
            if (strcasecmp((string)$br['autonumber'], $car) === 0) {
                $result[] = array(
                    'kuupaev'    => (string)$paev['kuupaev'],
                    'kellaaeg'   => (string)$br['kellaaeg'],
                    'nimi'       => (string)$br['nimi'],
                    'telefon'    => (string)$br['telefon'],
                    'teenus'     => (string)$br['teenus'],
                    'autonumber' => (string)$br['autonumber'],
                );
            }
        }
    }
    return $result;
}

function search_by_phone($phone)
{
    $xml = load_xml();
    $result = array();

    foreach ($xml->paev as $paev) {
        foreach ($paev->broneering as $br) {
            if ((string)$br['telefon'] === $phone) {
                $result[] = array(
                    'kuupaev'    => (string)$paev['kuupaev'],
                    'kellaaeg'   => (string)$br['kellaaeg'],
                    'nimi'       => (string)$br['nimi'],
                    'telefon'    => (string)$br['telefon'],
                    'teenus'     => (string)$br['teenus'],
                    'autonumber' => (string)$br['autonumber'],
                );
            }
        }
    }
    return $result;
}

function filter_by_service($service)
{
    $xml = load_xml();
    $result = array();

    foreach ($xml->paev as $paev) {
        foreach ($paev->broneering as $br) {
            if (stripos((string)$br['teenus'], $service) !== false) {
                $result[] = array(
                    'kuupaev'    => (string)$paev['kuupaev'],
                    'kellaaeg'   => (string)$br['kellaaeg'],
                    'nimi'       => (string)$br['nimi'],
                    'telefon'    => (string)$br['telefon'],
                    'teenus'     => (string)$br['teenus'],
                    'autonumber' => (string)$br['autonumber'],
                );
            }
        }
    }
    return $result;
}

function render_main_table()
{
    $xml = new DOMDocument();
    $xml->load(__DIR__ . '/broneeringud.xml');

    $xsl = new DOMDocument();
    $xsl->load(__DIR__ . '/tabel.xsl');

    $proc = new XSLTProcessor();
    $proc->importStylesheet($xsl);

    echo $proc->transformToXML($xml);
}

function render_results_table($rows, $title)
{
    echo '<h2>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>';

    if (empty($rows)) {
        echo '<p class="no-results">Tulemusi ei leitud.</p>';
        return;
    }

    echo '<table class="data-table">
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
            <tbody>';

    foreach ($rows as $r) {
        echo '<tr>
                <td>' . $r['kuupaev'] . '</td>
                <td>' . $r['kellaaeg'] . '</td>
                <td>' . $r['nimi'] . '</td>
                <td>' . $r['telefon'] . '</td>
                <td>' . $r['teenus'] . '</td>
                <td>' . $r['autonumber'] . '</td>
              </tr>';
    }

    echo '</tbody></table>';
}

$results = array();
$searchTitle = '';

if (!empty($_GET['car'])) {
    $results = search_by_car(trim($_GET['car']));
    $searchTitle = 'Otsing autonumbri järgi: ' . htmlspecialchars($_GET['car']);
} elseif (!empty($_GET['phone'])) {
    $results = search_by_phone(trim($_GET['phone']));
    $searchTitle = 'Otsing telefoni järgi: ' . htmlspecialchars($_GET['phone']);
} elseif (!empty($_GET['service'])) {
    $results = filter_by_service(trim($_GET['service']));
    $searchTitle = 'Filtreerimine teenuse järgi: ' . htmlspecialchars($_GET['service']);
}

$xmlRaw = file_get_contents(__DIR__ . '/broneeringud.xml');
$xmlEscaped = htmlspecialchars($xmlRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Autoteeninduse broneerimissüsteem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-link active">Broneeringud</a>
    <a href="json.php" class="nav-link">Lisa broneering</a>
</nav>

<div class="container">
    <h1>Autoteeninduse broneeringud</h1>
    <p class="subtitle">Andmed XML-failist, kuvamine XSLT abil HTML tabelina.</p>

    <?php render_main_table(); ?>

    <div class="search-section">
        <h2>Otsing ja filtreerimine (PHP funktsioonid)</h2>
        <div class="search-forms">
            <form class="search-card" method="get">
                <h3>Otsing autonumbri järgi</h3>
                <label for="car">Autonumber</label>
                <input type="text" id="car" name="car" placeholder="nt 123ABC">
                <button type="submit">Otsi</button>
            </form>

            <form class="search-card" method="get">
                <h3>Otsing telefoni järgi</h3>
                <label for="phone">Telefon</label>
                <input type="text" id="phone" name="phone" placeholder="nt 5551234">
                <button type="submit">Otsi</button>
            </form>

            <form class="search-card" method="get">
                <h3>Filtreeri teenuse järgi</h3>
                <label for="service">Teenus</label>
                <input type="text" id="service" name="service" placeholder="nt Õlivahetus">
                <button type="submit">Filtreeri</button>
            </form>
        </div>

        <?php
        if ($searchTitle !== '') {
            render_results_table($results, $searchTitle);
        }
        ?>
    </div>

    <details>
        <summary>Näita broneeringuid XML-kujul</summary>
        <pre><?php echo $xmlEscaped; ?></pre>
    </details>
</div>
</body>
</html>

<!-- Auteur  : Corentin Robin
     Version : 09/10/2018 -->

<?php
// on récupère les éditions disponibles

// éditions et formats de date
$editions = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/resources/editions.json"), true);
$dates = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/resources/dates.json"), true);

$available_editions = [];

foreach($editions as $edition => $articles)
{
    array_push($available_editions, $edition);
}


// édition choisie
$edition = (isset($_GET["edition"]) ? (in_array($_GET["edition"], $available_editions) ? $_GET["edition"] : "uk") : "uk");

if($edition != "uk")
    $wording = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/resources/wording_" . $edition . ".json"), true);

// pour afficher le bon mot
function word($key)
{
    global $edition, $wording;

    if($edition != "uk") return $wording[$key];
    else return $key;
}

// récupérer la date dans le bon format et la bonne langue
$day = strftime("%A");
$month = strftime("%B");

$date = strftime($dates[$edition]);

$date = preg_replace("%" . $day . "%", word($day), $date);
$date = preg_replace("%" . $month . "%", word($month), $date);

// sélecteur d'édition
$selector_editions = ["uk" => "United Kingdom", "fr" => "France", "es" => "España"];
?>

<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Journal</title>
        <script src="/scripts/js/index.js"></script>
        <link rel="stylesheet" href="/styles/index.css">
	</head>

<body>

    <script>
        Journal.edition = "<?= $edition ?>";
        Journal.wording = <?= json_encode($wording) ?>;
    </script>

    <header>
        <div class="title">
            <div>
                Journal
            </div>

            <div>
                <span><?= word("Edition") ?> :</span>
                <select onchange="Journal.changeEdition()">
                    <?php foreach($selector_editions as $country_code => $country_name): ?>
                        <option value="<?= $country_code ?>"<?= ($country_code == $edition ? " selected" : "") ?>><?= $country_name ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="button" onclick="Journal.refresh()"><img src="/images/refresh_icon.svg"> <?= word("Refresh") ?></span>
            </div>
        </div>

        <div class="information">
            <div>
                &copy; 2018 Corentin Robin
            </div>

            <div>
                <?= $date ?>
            </div>

            <div class="statistics"></div>
        </div>
    </header>

    <main>
        <div class="controls">
            <div>
                <input type="text" id="keyword" placeholder="<?= word("e.g., london") ?>" onkeyup="Journal.search()">

                <span class="button" onclick="Journal.search()"><?= word("Search") ?></span>
                <span class="button" onclick="Journal.showAll()"><?= word("Show all") ?></span>
            </div>
        </div>

        <div class="links"></div>
    </main>

    <div class="loading-message">
        <?= word("Reading newspapers") ?>...
    </div>

</body>
</html>
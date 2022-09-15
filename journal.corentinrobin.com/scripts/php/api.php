<?php
// Auteur : Corentin Robin - Version : 08/10/2018
// On utilise les codes pays ISO 3166 (https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes)

// https://www.bbc.co.uk/news/uk
// https://www.theguardian.com/uk
// https://www.channel4.com/news/
// https://www.economist.com/
// https://www.express.co.uk/
// https://www.independent.co.uk/
// https://www.thetimes.co.uk/
// https://www.empireonline.com

function source_code($url)
{
    // on utilise cURL, car parfois file_get_contents n'arrive pas à récupérer les sources en https
    $session = curl_init($url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER,false);
    $content = curl_exec($session);
    curl_close($session);

    return $content;
}

function rebased_url($article_url, $website_url)
{
    $output = "";

    // si commence par //, cela remplace http://, donc c'est une URL absolue, pas relative
    // donc uniquement le premier caractère doit être /
    if($article_url[0] == "/" && $article_url[1] != "/")
    {
        // si l'URL commence par /, elle part de la racine du site
        $scheme = parse_url($website_url, PHP_URL_SCHEME);
        $host = parse_url($website_url, PHP_URL_HOST);
        
        $output = $scheme . "://" . $host . $article_url;
    }

    else
        $output = $article_url;

    return $output;
}

function links($website_url)
{
    $source = source_code($website_url);

    $output = [];
    $titles = [];

    preg_match_all('%<a.*href *= *"(.*?)".*>(.*?)</a>%', $source, $matches, PREG_SET_ORDER);

    for($i = 0; $i < count($matches); $i++)
    {
        $article_url = $matches[$i][1];
        $title = strip_tags($matches[$i][2]);

        // on prend uniquement les titres assez long (plus susceptibles d'être des articles, et pas des liens parasites), et on ne prend pas les titres déjà sélectionnés
        if(strlen($title) > 39 && !in_array($title, $titles))
        {
            array_push($titles, $title);
            array_push($output, [rebased_url($article_url, $website_url), $title]);
        }
    }

    return $output;
}

$data = json_decode(file_get_contents("php://input"), true);
$edition = $data["edition"];

$urls = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/resources/editions.json"), true)[$edition]["urls"];

$links = [];

for($i = 0; $i < count($urls); $i++)
{
    $url = $urls[$i];
    $links[parse_url($url, PHP_URL_HOST)] = links($url);
}

echo json_encode($links);
<?php

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

$movies = [];

$cacheMovies = json_decode(file_get_contents($moviesCacheFileName = 'movies.json'));

$context = stream_context_create(['http' => ['header' => "Accept-language: en"]]);

if (!$html = file_get_contents('https://www.imdb.com/chart/top', false, $context)) {
    exit;
}

/* @var DOMElement $tr */
foreach ((new Crawler($html))->filter('tbody tr') as $tr) {
    preg_match('/\d{1,3}\.\s+(.+)\s+\((\d{4})\)/', trim($tr->nodeValue), $match);
    $movies[] = "$match[1] ($match[2])";
}

if ($diff = array_diff($movies, $cacheMovies)) {
    $message = '';
    file_put_contents($moviesCacheFileName, json_encode($movies));

    foreach ($diff as $movie) {
        $rank = array_search($movie, $movies) + 1;
        $message .= '#' . $rank . ' ' . $movie . PHP_EOL;
    }

    mail('dvdtygc@gmail.com', 'IMDB Top 250 Updates!', $message, ['Content-Type: text/html']);
}

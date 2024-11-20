<?php

require "./simplehtmldom/simple_html_dom.php";

$path = "result";
if (!is_dir($path)) {
    mkdir($path);
}

$str_b = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фильмы</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }
        .container {
            padding: 30px;
            margin-top: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        .movie-card {
            margin: 20px;
            border: 1px solid #ff0000;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }
        .movie-card:hover {
            box-shadow: 0 8px 30px rgba(255, 0, 0, 0.6);
        }
        .movie-image {
            width: 100%;
            height: 600px;
            object-fit: cover;
        }
        .movie-title {
            font-size: 1.5em;
            color: #ff0000;
            text-decoration: none;
        }
        .movie-theater, .movie-date {
            color: #ccc;
        }
        .card-body {
            background-color: #222;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Список фільмів</h1>
EOD;

$str_e = <<<EOD
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
EOD;

$multiplex = file_get_html("https://multiplex.ua");
$domain = "https://multiplex.ua";

$movies = [];

if (count($multiplex->find('.mp_postersList div.mp_poster'))) {
    foreach ($multiplex->find('.mp_postersList div.mp_poster') as $movie) {
        $title = $movie->find('.mpp_title span', 0)->innertext ?? 'Не вказано';
        $link = $movie->find('.mpp_title', 0)->href ?? '#';
        $theater = $movie->find('.current_cinema', 0)->innertext ?? 'Не вказано';
        $date = $movie->find('.current_date span', 0)->innertext ?? 'Не вказано';

        $bgStyle = $movie->find('.bg', 0)->style ?? '';
        preg_match('/url\\([\'"]?(.*?)[\'"]?\\)/i', $bgStyle, $matches);
        $imageSrc = $matches[1] ?? '';

        if (strpos($imageSrc, 'http') !== 0) {
            $imageSrc = rtrim($domain, '/') . '/' . ltrim($imageSrc, '/');
        }

        $movies[] = [
            'title' => $title,
            'link' => $domain . $link,
            'theater' => $theater,
            'date' => $date,
            'imageSrc' => $imageSrc,
        ];
    }
}

$cards = [];
foreach ($movies as $movie) {
    $cards[] = "<div class='col-md-4'>
        <a href='" . $movie['link'] . "' class='text-decoration-none'>
            <div class='movie-card card'>
                <img src='" . $movie['imageSrc'] . "' class='movie-image card-img-top' alt='" . $movie['title'] . "'>
                <div class=' card-body'>
                    <h5 class='movie-title card-title'>" . $movie['title'] . "</h5>
                    <p class='movie-theater'>Кінотеатр: " . $movie['theater'] . "</p>
                    <p class='movie-date'>Дата: " . $movie['date'] . "</p>
                </div>
            </div>
        </a>
    </div>";
}

$content = "<div class='row'>" . implode("\n", $cards) . "</div>";

$f = fopen($path . "/index.html", "w");
fwrite($f, $str_b . $content . $str_e);
fclose($f);

echo "HTML файл успішно записаний у файлі: $path/index.html";

?>
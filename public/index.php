<?php
// Home page dedicata ai clienti
// Carosello immagini da public/img_carousel/
// Sezione notizie a tema turismo

// Funzione per leggere immagini dalla cartella
function getCarouselImages($dir = 'img_carousel') {
    $images = [];
    $path = __DIR__ . "/$dir";
    if (is_dir($path)) {
        foreach (scandir($path) as $file) {
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                $images[] = "$dir/$file";
            }
        }
    }
    return $images;
}

$carouselImages = getCarouselImages();
// Notizie statiche (puoi modificarle o renderle dinamiche)
// Recupera notizie dal feed RSS di Google News (tema turismo)
$feedUrl = 'https://news.google.com/rss/search?q=turismo+italia&hl=it&gl=IT&ceid=IT:it';
$notizie = [];
try {
    $rss = @simplexml_load_file($feedUrl);
    if ($rss && isset($rss->channel->item)) {
        foreach ($rss->channel->item as $item) {
            $notizie[] = [
                'titolo' => (string)$item->title,
                'testo' => (string)$item->description,
                'link' => (string)$item->link
            ];
            if (count($notizie) >= 6) break; // Mostra solo le prime 6 notizie
        }
    }
} catch (Exception $e) {
    $notizie[] = [
        'titolo' => 'Errore nel caricamento delle notizie',
        'testo' => 'Impossibile recuperare le notizie dal feed RSS.'
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Home Cliente - Noleggio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .carousel-img { height: 350px; object-fit: cover; }
        .notizie { margin-top: 2rem; }
        /* Pulsanti personalizzati carosello notizie */
        #carouselNotizie .carousel-control-prev, #carouselNotizie .carousel-control-next {
            width: 56px;
            height: 56px;
            top: 50%;
            transform: translateY(-50%);
            background: #007bff;
            border-radius: 50%;
            opacity: 0.92;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, opacity 0.2s;
        }
        #carouselNotizie .carousel-control-prev:hover, #carouselNotizie .carousel-control-next:hover {
            background: #0056b3;
            opacity: 1;
        }
        #carouselNotizie .carousel-control-prev-icon, #carouselNotizie .carousel-control-next-icon {
            background-image: none;
            font-size: 2rem;
            color: #fff;
            font-weight: bold;
            width: auto;
            height: auto;
        }
        #carouselNotizie .carousel-control-prev {
            left: -60px;
        }
        #carouselNotizie .carousel-control-next {
            right: -60px;
        }
        @media (max-width: 600px) {
            #carouselNotizie .carousel-control-prev {
                left: 0;
            }
            #carouselNotizie .carousel-control-next {
                right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 text-center">Benvenuto nella tua area clienti</h1>
        <div class="text-center mb-4">
            <a href="accesso_cliente.php" class="btn btn-success btn-lg shadow">Accedi alla tua area</a>
        </div>
        <!-- Carosello Immagini -->
        <div id="carouselTurismo" class="carousel slide mb-5" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($carouselImages as $i => $img): ?>
                <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                    <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100 carousel-img" alt="Turismo <?= $i+1 ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselTurismo" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Precedente</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselTurismo" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Successivo</span>
            </button>
        </div>
        <!-- Sezione Notizie -->
        <div class="notizie">
            <h2 class="mb-3">Notizie Turismo</h2>
            <div id="carouselNotizie" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($notizie as $i => $notizia): ?>
                    <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                        <div class="card h-100 mx-auto" style="max-width: 600px;">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php if (!empty($notizia['link'])): ?>
                                        <a href="<?= htmlspecialchars($notizia['link']) ?>" target="_blank" rel="noopener" style="text-decoration:none; color:inherit;">
                                            <?= htmlspecialchars($notizia['titolo']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($notizia['titolo']) ?>
                                    <?php endif; ?>
                                </h5>
                                <?php
                                // Rimuovi entità HTML come &nbsp; dal testo
                                $testoPulito = preg_replace('/&nbsp;|&#160;|&amp;nbsp;/', ' ', $notizia['testo']);
                                $testoPulito = strip_tags($testoPulito);
                                $testoPulito = htmlspecialchars($testoPulito);
                                ?>
                                <p class="card-text"><?= $testoPulito ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselNotizie" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true">&#8592;</span>
                    <span class="visually-hidden">Precedente</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselNotizie" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true">&#8594;</span>
                    <span class="visually-hidden">Successivo</span>
                </button>
            </div>
        </div>
    </div>
<div style="text-align:right; margin: 2em 0;">
    <a href="accesso.php" class="btn btn-success" style="font-size:1.2em; padding:0.7em 2em; border-radius:8px;">
        Area privata
    </a>
</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

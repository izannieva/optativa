<?php
require_once 'config.php';

// Obtener el formato deseado (RSS o Atom) a través de un parámetro GET
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'rss';

// Consulta para obtener las últimas 5 fotos
$query = "SELECT IdFoto, Titulo, Descripcion, Fecha AS FechaPublicacion, Fichero FROM fotos ORDER BY Fecha DESC LIMIT 5";
$stmt = $pdo->prepare($query);
$stmt->execute();
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear un nuevo documento DOM
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

if ($format === 'rss') {
    // Crear el nodo raíz <rss>
    $rss = $doc->createElement('rss');
    $rss->setAttribute('version', '2.0');
    $doc->appendChild($rss);

    // Crear el canal <channel>
    $channel = $doc->createElement('channel');
    $rss->appendChild($channel);

    // Añadir elementos al canal
    $channel->appendChild($doc->createElement('title', 'Canal RSS de Fotos'));
    $channel->appendChild($doc->createElement('link', 'http://localhost/daw_xamp/'));
    $channel->appendChild($doc->createElement('description', 'Últimas fotos publicadas'));

    // Añadir las fotos como elementos <item>
    foreach ($fotos as $foto) {
        $item = $doc->createElement('item');
        $item->appendChild($doc->createElement('title', $foto['Titulo']));
        $item->appendChild($doc->createElement('link', "http://localhost/daw_xamp/fotos/" . $foto['Fichero']));
        $item->appendChild($doc->createElement('description', $foto['Descripcion']));
        $item->appendChild($doc->createElement('pubDate', $foto['FechaPublicacion']));
        $channel->appendChild($item);
    }
} elseif ($format === 'atom') {
    // Crear el nodo raíz <feed>
    $feed = $doc->createElement('feed');
    $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
    $doc->appendChild($feed);

    // Añadir elementos al feed
    $feed->appendChild($doc->createElement('title', 'Canal Atom de Fotos'));
    $feed->appendChild($doc->createElement('link', 'http://localhost/daw_xamp/'));
    $feed->appendChild($doc->createElement('updated', date(DATE_ATOM)));

    // Añadir las fotos como elementos <entry>
    foreach ($fotos as $foto) {
        $entry = $doc->createElement('entry');
        $entry->appendChild($doc->createElement('title', $foto['Titulo']));
        $entry->appendChild($doc->createElement('link', "http://localhost/daw_xamp/fotos/" . $foto['Fichero']));
        $entry->appendChild($doc->createElement('summary', $foto['Descripcion']));
        $entry->appendChild($doc->createElement('updated', date(DATE_ATOM, strtotime($foto['FechaPublicacion']))));
        $feed->appendChild($entry);
    }
} else {
    die('Formato no soportado');
}

// Mostrar el XML generado
header('Content-Type: application/xml');
echo $doc->saveXML();
?>

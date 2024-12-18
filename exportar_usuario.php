<?php
require_once 'config.php';

// Supongamos que el ID del usuario se pasa como parámetro
$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener información del usuario
$queryUsuario = "SELECT IdUsuario, NomUsuario, Email FROM usuarios WHERE IdUsuario = :id";
$stmtUsuario = $pdo->prepare($queryUsuario);
$stmtUsuario->execute(['id' => $idUsuario]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado");
}

// Obtener los álbumes del usuario
$queryAlbumes = "SELECT IdAlbum, Titulo, Descripcion FROM albumes WHERE Usuario = :id";
$stmtAlbumes = $pdo->prepare($queryAlbumes);
$stmtAlbumes->execute(['id' => $idUsuario]);
$albumes = $stmtAlbumes->fetchAll(PDO::FETCH_ASSOC);

// Para cada álbum, obtener sus fotos
foreach ($albumes as &$album) {
    $queryFotos = "SELECT IdFoto, Titulo, Descripcion FROM fotos WHERE Album = :id";
    $stmtFotos = $pdo->prepare($queryFotos);
    $stmtFotos->execute(['id' => $album['IdAlbum']]);
    $album['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
}

// Crear un nuevo documento DOM
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

// Nodo raíz <PI>
$root = $doc->createElement('PI');
$doc->appendChild($root);

// Nodo del usuario
$usuarioNode = $doc->createElement('Usuario');
$usuarioNode->setAttribute('IdUsuario', $usuario['IdUsuario']);
$root->appendChild($usuarioNode);

$usuarioNode->appendChild($doc->createElement('NomUsuario', $usuario['NomUsuario']));
$usuarioNode->appendChild($doc->createElement('Email', $usuario['Email']));

// Añadir álbumes
$albumesNode = $doc->createElement('Albumes');
foreach ($albumes as $album) {
    $albumNode = $doc->createElement('Album');
    $albumNode->setAttribute('IdAlbum', $album['IdAlbum']);
    $albumNode->appendChild($doc->createElement('Titulo', $album['Titulo']));
    $albumNode->appendChild($doc->createElement('Descripcion', $album['Descripcion']));

    // Añadir fotos al álbum
    $fotosNode = $doc->createElement('Fotos');
    foreach ($album['fotos'] as $foto) {
        $fotoNode = $doc->createElement('Foto');
        $fotoNode->setAttribute('IdFoto', $foto['IdFoto']);
        $fotoNode->appendChild($doc->createElement('Titulo', $foto['Titulo']));
        $fotoNode->appendChild($doc->createElement('Descripcion', $foto['Descripcion']));
        $fotosNode->appendChild($fotoNode);
    }
    $albumNode->appendChild($fotosNode);
    $albumesNode->appendChild($albumNode);
}
$usuarioNode->appendChild($albumesNode);

// Mostrar el XML generado
header('Content-Type: application/xml');
echo $doc->saveXML();
?>

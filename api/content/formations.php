<?php
require_once __DIR__ . '/../includes/api_common.php';

$formations = readJsonFile(__DIR__ . '/../../pages/admin/data/formations.json');

// Filtrer uniquement les formations publiées
$published = array_filter($formations, function($f) {
    return ($f['statut'] ?? 'brouillon') === 'publié';
});

// Réorganiser les données pour le mobile (si besoin)
$data = array_values($published);

sendResponse('success', $data);

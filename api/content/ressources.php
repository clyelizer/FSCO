<?php
require_once __DIR__ . '/../includes/api_common.php';

$ressources = readJsonFile(__DIR__ . '/../../pages/admin/data/ressources.json');

$published = array_filter($ressources, function($r) {
    return ($r['statut'] ?? 'brouillon') === 'publié';
});

sendResponse('success', array_values($published));

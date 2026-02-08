<?php
require_once __DIR__ . '/../includes/api_common.php';

$blogs = readJsonFile(__DIR__ . '/../../pages/admin/data/blogs.json');

$published = array_filter($blogs, function($b) {
    return ($b['statut'] ?? 'brouillon') === 'publié';
});

sendResponse('success', array_values($published));

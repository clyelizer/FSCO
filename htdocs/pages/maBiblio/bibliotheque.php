<?php
session_start();
require_once '../../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDBConnection();

// Fetch user's library items
$stmt = $pdo->prepare("SELECT * FROM user_library WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$library_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load all resources and blogs to cross-reference
$ressources_data = json_decode(file_get_contents('../../pages/admin/data/ressources.json'), true) ?? [];
$blogs_data = json_decode(file_get_contents('../../pages/admin/data/blogs.json'), true) ?? [];

// Helper to find item details
function getItemDetails($id, $type, $ressources, $blogs)
{
    $data = ($type === 'blog') ? $blogs : $ressources;
    foreach ($data as $item) {
        if ($item['id'] === $id)
            return $item;
    }
    return null;
}

// Process library items to include details
$my_library = [];
foreach ($library_items as $item) {
    $details = getItemDetails($item['resource_id'], $item['type'], $ressources_data, $blogs_data);
    if ($details) {
        $item['details'] = $details;
        $my_library[] = $item;
    }
}

// Filter by status for tabs (simple implementation: we'll just show all for now or filter in PHP)
// The HTML had sections: En cours, Terminés, Favoris. 
// Our DB has 'status' enum.
// Filter by status for tabs
// Filter by status for tabs
$en_cours = array_filter($my_library, fn($i) => $i['status'] === 'en_cours');
$favoris = array_filter($my_library, fn($i) => !empty($i['is_favorite']) && $i['is_favorite'] == 1);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ma Bibliothèque - FSCo</title>
    <link rel="stylesheet" href="../../index.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- HEADER -->
    <!-- HEADER -->
    <?php include '../includes/header.php'; ?>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h2>Ma Bibliothèque Personnelle</h2>
                <p class="subtitle">Organisez et suivez votre progression dans vos ressources favorites. Gardez une
                    trace de vos lectures en cours et terminez ce que vous avez commencé.</p>
            </div>
        </div>
    </section>

    <!-- SECTION: EN COURS DE LECTURE -->
    <section id="reading" class="features-section" style="padding-bottom: 0;">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <div
                    style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-book-reader"></i>
                </div>
                <h2 class="section-title" style="margin-bottom: 0; text-align: left;">En cours de lecture</h2>
            </div>

            <?php if (empty($en_cours)): ?>
                <p style="color: #64748b; font-style: italic;">Vous n'avez aucune lecture en cours.</p>
            <?php else: ?>
                <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                    <?php foreach ($en_cours as $item):
                        $detail = $item['details'];
                        $image = !empty($detail['image']) ? '../../' . $detail['image'] : null;
                        // Use secure viewer for resources
                        $lien = ($item['type'] === 'ressource' && !empty($detail['fichier']))
                            ? '../Ressources/viewer.php?id=' . urlencode($item['resource_id'])
                            : (!empty($detail['fichier']) ? '../../' . $detail['fichier'] : ($detail['url_externe'] ?? '#'));
                        $is_fav = !empty($item['is_favorite']) && $item['is_favorite'] == 1;
                        ?>
                        <div class="card">
                            <div style="position: relative; height: 200px; border-radius: 8px 8px 0 0; overflow: hidden;">
                                <?php if ($image): ?>
                                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($detail['titre']) ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-bookmark"
                                            style="font-size: 4rem; color: var(--primary-color); opacity: 0.5;"></i>
                                    </div>
                                <?php endif; ?>
                                <div
                                    style="position: absolute; top: 10px; right: 10px; background: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; color: var(--primary-color);">
                                    En cours
                                </div>
                            </div>

                            <h3 style="margin-top: 1rem;"><?= htmlspecialchars($detail['titre']) ?></h3>
                            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Démarré le
                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                            </p>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <?php if ($is_fav): ?>
                                    <button onclick="unmarkFavorite(<?= $item['id'] ?>)" class="btn btn-primary"
                                        style="justify-content: center; font-size: 0.9rem; background-color: #f59e0b; border-color: #f59e0b;">
                                        <i class="fas fa-heart" style="margin-right: 0.5rem;"></i> Favori
                                    </button>
                                <?php else: ?>
                                    <button onclick="markAsFavorite(<?= $item['id'] ?>)" class="btn btn-outline"
                                        style="justify-content: center; font-size: 0.9rem;">
                                        <i class="far fa-heart" style="margin-right: 0.5rem;"></i> Favoris
                                    </button>
                                <?php endif; ?>

                                <button onclick="removeFromLibrary(<?= $item['id'] ?>)" class="btn btn-outline"
                                    style="justify-content: center; color: #ef4444; border-color: #ef4444; font-size: 0.9rem;">
                                    <i class="fas fa-trash"></i> Retirer
                                </button>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <a href="<?= htmlspecialchars($lien) ?>" target="_blank" class="btn btn-secondary"
                                    style="width: 100%; justify-content: center;">
                                    <i class="fas fa-play" style="margin-right: 0.5rem;"></i> Continuer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION: MES FAVORIS -->
    <section id="favoris" class="features-section">
        <div class="container">
            <div style="border-top: 1px solid #e2e8f0; margin: 2rem 0;"></div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <div
                    style="width: 40px; height: 40px; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-star"></i>
                </div>
                <h2 class="section-title" style="margin-bottom: 0; text-align: left;">Mes Favoris</h2>
            </div>

            <?php if (empty($favoris)): ?>
                <p style="color: #64748b; font-style: italic;">Vous n'avez aucun favori pour le moment.</p>
                <?php if (empty($en_cours)): ?>
                    <div style="margin-top: 2rem;">
                        <a href="../Ressources/ressources.php" class="btn btn-primary">Découvrir les ressources</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                    <?php foreach ($favoris as $item):
                        $detail = $item['details'];
                        $image = !empty($detail['image']) ? '../../' . $detail['image'] : null;
                        // Use secure viewer for resources
                        $lien = ($item['type'] === 'ressource' && !empty($detail['fichier']))
                            ? '../Ressources/viewer.php?id=' . urlencode($item['resource_id'])
                            : (!empty($detail['fichier']) ? '../../' . $detail['fichier'] : ($detail['url_externe'] ?? '#'));
                        ?>
                        <div class="card">
                            <div style="position: relative; height: 200px; border-radius: 8px 8px 0 0; overflow: hidden;">
                                <?php if ($image): ?>
                                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($detail['titre']) ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-bookmark"
                                            style="font-size: 4rem; color: var(--primary-color); opacity: 0.5;"></i>
                                    </div>
                                <?php endif; ?>
                                <div
                                    style="position: absolute; top: 10px; right: 10px; background: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; color: #f59e0b;">
                                    Favori
                                </div>
                            </div>

                            <h3 style="margin-top: 1rem;"><?= htmlspecialchars($detail['titre']) ?></h3>
                            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Ajouté le
                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                            </p>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <a href="<?= htmlspecialchars($lien) ?>" target="_blank" class="btn btn-secondary"
                                    style="justify-content: center;">
                                    <i class="fas fa-eye"></i> Consulter
                                </a>
                                <button onclick="unmarkFavorite(<?= $item['id'] ?>)" class="btn btn-outline"
                                    style="justify-content: center; color: #ef4444; border-color: #ef4444;">
                                    <i class="fas fa-trash"></i> Retirer
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <!-- FOOTER -->
    <?php include '../includes/footer.php'; ?>

    <script>
        function removeFromLibrary(id) {
            if (confirm('Voulez-vous vraiment retirer cet élément de votre bibliothèque ?')) {
                const formData = new FormData();
                formData.append('action', 'remove_from_library');
                formData.append('id', id);

                fetch('../Ressources/actions.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message || 'Erreur lors de la suppression');
                        }
                    });
            }
        }

        function markAsFavorite(id) {
            const formData = new FormData();
            formData.append('action', 'mark_as_favorite');
            formData.append('id', id);

            fetch('../Ressources/actions.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la mise à jour');
                    }
                });
        }

        function unmarkFavorite(id) {
            if (confirm('Retirer des favoris ?')) {
                const formData = new FormData();
                formData.append('action', 'unmark_favorite');
                formData.append('id', id);

                fetch('../Ressources/actions.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message || 'Erreur lors de la mise à jour');
                        }
                    });
            }
        }
    </script>
</body>

</html>
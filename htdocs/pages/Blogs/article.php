<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Article - FSCo</title>
    <link rel="stylesheet" href="../../index.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .article-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .article-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .article-meta {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            color: #666;
            margin: 1rem 0;
            font-size: 0.95rem;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .article-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }

        .article-body h2 {
            margin-top: 2rem;
            color: var(--primary-color);
        }

        .article-body p {
            margin-bottom: 1.5rem;
        }

        .article-tags {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tag {
            background: #f1f5f9;
            color: #475569;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <!-- HEADER -->
    <?php include '../includes/header.php'; ?>

    <?php
    // Charger les blogs
    $blogs = json_decode(file_get_contents('../../pages/admin/data/blogs.json'), true) ?? [];

    // Récupérer l'ID depuis l'URL
    $id = $_GET['id'] ?? null;
    $article = null;

    // Chercher l'article correspondant
    if ($id) {
        foreach ($blogs as $b) {
            if ($b['id'] === $id) {
                $article = $b;
                break;
            }
        }
    }
    ?>

    <section class="hero-section" style="padding: 4rem 0 2rem;">
        <div class="container">
            <a href="blogs.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux articles</a>

            <?php if ($article): ?>
                <div class="article-content">
                    <div class="article-header">
                        <span
                            style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                            <?= htmlspecialchars($article['categorie'] ?? 'Général') ?>
                        </span>
                        <h1 style="margin-top: 1rem; font-size: 2.5rem; line-height: 1.2; color: var(--secondary-color);">
                            <?= htmlspecialchars($article['titre']) ?>
                        </h1>
                        <div class="article-meta">
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($article['auteur'] ?? 'FSCo') ?></span>
                            <span><i class="fas fa-calendar"></i>
                                <?= htmlspecialchars($article['date'] ?? date('d/m/Y')) ?></span>
                        </div>
                    </div>

                    <img src="<?= !empty($article['image']) ? '../../' . htmlspecialchars($article['image']) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800' ?>"
                        alt="<?= htmlspecialchars($article['titre']) ?>" class="article-image">

                    <div class="article-body">
                        <p class="lead" style="font-size: 1.25rem; color: #555; font-weight: 500; margin-bottom: 2rem;">
                            <?= htmlspecialchars($article['extrait'] ?? '') ?>
                        </p>

                        <!-- Affichage du contenu HTML (décodé) -->
                        <?= html_entity_decode($article['contenu'] ?? '') ?>
                    </div>

                    <?php if (!empty($article['tags'])): ?>
                        <div class="article-tags">
                            <?php foreach ($article['tags'] as $tag): ?>
                                <span class="tag">#<?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <h2>Article non trouvé</h2>
                    <p>L'article que vous cherchez n'existe pas ou a été supprimé.</p>
                    <a href="blogs.php" class="btn btn-primary" style="margin-top: 1rem;">Retour au blog</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <!-- FOOTER -->
    <?php include '../includes/footer.php'; ?>

</body>

</html>
<?php
session_start();
require_once '../../pages/admin/includes/config.php';

// Get resource ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Ressource non spécifiée.");
}

// Load resources
$ressources = readJsonData(DATA_RESSOURCES);
$resource = null;

foreach ($ressources as $r) {
    if ($r['id'] === $id) {
        $resource = $r;
        break;
    }
}

if (!$resource) {
    die("Ressource introuvable.");
}

// Check if file exists
$filePath = '../../' . $resource['fichier'];
if (!file_exists($filePath)) {
    die("Le fichier n'est pas disponible sur le serveur.");
}

// For the viewer, we want to show the file via secure stream
$viewerPath = 'stream.php?id=' . urlencode($resource['id']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Lecture : <?= htmlspecialchars($resource['titre']) ?> - FSCo</title>
    <link rel="stylesheet" href="../../index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .viewer-header {
            background: #2d2d2d;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            z-index: 100;
            flex-shrink: 0;
        }

        .viewer-title {
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .back-btn {
            color: #aaa;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .back-btn:hover {
            color: white;
        }

        .viewer-container {
            flex: 1;
            position: relative;
            width: 100%;
            height: 100%;
            background: #525659;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
        }

        .pdf-page {
            position: relative;
            background: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 95%;
            /* Default width */
            max-width: 1000px;
            /* Max width for desktop */
        }

        .pdf-page svg,
        .pdf-page canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #333;
            font-size: 1.2rem;
        }

        /* Security Overlay */
        .page-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            /* Allow pointer events to pass through for scrolling/zooming, 
               but block selection if possible. 
               Note: SVG text is selectable by default. 
               We use user-select: none on the container to prevent selection. */
        }

        /* Disable selection */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media print {
            body {
                display: none;
            }
        }

        .upgrade-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .upgrade-btn:hover {
            background: var(--secondary-color);
        }

        @media (max-width: 600px) {
            .viewer-title span {
                font-size: 0.9rem;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .pdf-page {
                width: 100%;
                /* Full width on mobile */
                margin-bottom: 10px;
            }

            .viewer-container {
                padding: 10px 0;
            }
        }
    </style>
</head>

<body oncontextmenu="return false;">

    <div class="viewer-header">
        <div class="viewer-title">
            <a href="ressources.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> <span class="desktop-only">Retour</span>
            </a>
            <span><?= htmlspecialchars($resource['titre']) ?></span>
        </div>
        <div>
            <a href="download_handler.php?id=<?= htmlspecialchars($resource['id']) ?>" class="upgrade-btn">
                <i class="fas fa-download"></i> <span class="desktop-only">Télécharger</span>
            </a>
        </div>
    </div>

    <div class="viewer-container" id="viewerContainer">
        <!-- Pages will be injected here -->
    </div>

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set worker source
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const url = '<?= htmlspecialchars($viewerPath) ?>';
        const container = document.getElementById('viewerContainer');
        let pdfDoc = null;

        // Intersection Observer for Lazy Loading
        const observerOptions = {
            root: container,
            rootMargin: '200px', // Load pages 200px before they appear
            threshold: 0
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pageDiv = entry.target;
                    const pageNum = parseInt(pageDiv.dataset.pageNum);

                    if (!pageDiv.dataset.loaded) {
                        renderPageCanvas(pageNum, pageDiv);
                        pageDiv.dataset.loaded = true;
                        observer.unobserve(pageDiv); // Stop observing once loaded
                    }
                }
            });
        }, observerOptions);

        async function loadPDF() {
            try {
                const loadingTask = pdfjsLib.getDocument(url);
                pdfDoc = await loadingTask.promise;

                // Create placeholders for all pages immediately
                for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                    await createPagePlaceholder(pageNum);
                }

                // Restore saved page
                // Small delay to ensure layout is stable
                setTimeout(() => {
                    restoreState();
                }, 100);

            } catch (error) {
                console.error('Error loading PDF:', error);
                container.innerHTML = '<div style="color: white; padding: 2rem;">Erreur lors du chargement du document.</div>';
            }
        }

        async function createPagePlaceholder(pageNum) {
            // Get page info to determine aspect ratio
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: 1 });

            const pageDiv = document.createElement('div');
            pageDiv.className = 'pdf-page';
            pageDiv.dataset.pageNum = pageNum;

            // Set aspect ratio to prevent layout shift
            // We set height based on width using padding-bottom hack or explicit calculation if width is fixed
            // Here we'll set a min-height based on the aspect ratio and the container width
            // But since width is %, height needs to be dynamic. 
            // Simplest way: set aspect-ratio CSS property if supported, or calculate height on render.
            // For placeholder, we can just set a rough height or wait for render.
            // Better: Use style.aspectRatio
            pageDiv.style.aspectRatio = `${viewport.width} / ${viewport.height}`;

            // Add loading text
            const loading = document.createElement('div');
            loading.className = 'loading-indicator';
            loading.innerText = `Chargement page ${pageNum}...`;
            pageDiv.appendChild(loading);

            container.appendChild(pageDiv);

            // Start observing for lazy loading
            observer.observe(pageDiv);

            // Start observing for active page tracking
            activePageObserver.observe(pageDiv);
        }

        async function renderPageCanvas(pageNum, pageDiv) {
            try {
                const page = await pdfDoc.getPage(pageNum);

                // FORCE HIGH QUALITY (3x) - Sharp as SVG on mobile
                const outputScale = 3.0;
                const viewport = page.getViewport({ scale: outputScale });

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                // Set actual size (High Res)
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                // Set display size (CSS handles the fit)
                // We don't need to set specific pixels here because the CSS 
                // .pdf-page svg (now canvas) { width: 100%; height: 100%; } 
                // will handle it if we update the CSS selector.
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                canvas.style.display = 'block';

                // Clear loading indicator
                pageDiv.innerHTML = '';

                // Add Canvas
                pageDiv.appendChild(canvas);

                // Add security overlay
                const overlay = document.createElement('div');
                overlay.className = 'page-overlay';
                pageDiv.appendChild(overlay);

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                await page.render(renderContext).promise;

            } catch (error) {
                console.error(`Error rendering page ${pageNum}:`, error);
                pageDiv.innerHTML = '<div style="color: red; padding: 1rem;">Erreur de rendu.</div>';
            }
        }

        // State Management
        const STORAGE_KEY = `viewer_state_${<?= json_encode($resource['id']) ?>}`;
        let currentPage = 1;
        let isInitialLoad = true;
        let isRotating = false; // Lock to prevent state overwrite during rotation

        // Intersection Observer for Active Page Detection
        const activePageObserverOptions = {
            root: container,
            rootMargin: '-50% 0px -50% 0px', // Trigger when page is in middle of screen
            threshold: 0
        };

        const activePageObserver = new IntersectionObserver((entries) => {
            if (isRotating) return; // Don't update page during rotation
            
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    currentPage = parseInt(entry.target.dataset.pageNum);
                }
            });
        }, activePageObserverOptions);

        // Save State (Throttled)
        let saveTimeout;
        container.addEventListener('scroll', () => {
            if (isRotating) return; // Don't save state during rotation layout shift
            
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveState, 100);
        });

        function saveState() {
            if (isRotating) return; // Double check
            
            const pageDiv = document.querySelector(`.pdf-page[data-page-num="${currentPage}"]`);
            if (pageDiv) {
                // Calculate ratio: distance from top of page to top of viewport / page height
                const relativeTop = container.scrollTop - pageDiv.offsetTop;
                const ratio = relativeTop / pageDiv.clientHeight;
                
                const state = {
                    page: currentPage,
                    ratio: ratio
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            }
        }

        // Restore State
        function restoreState() {
            try {
                const savedState = JSON.parse(localStorage.getItem(STORAGE_KEY));
                if (savedState && savedState.page) {
                    const pageDiv = document.querySelector(`.pdf-page[data-page-num="${savedState.page}"]`);
                    if (pageDiv) {
                        // Calculate new scroll position based on saved ratio
                        const targetTop = pageDiv.offsetTop + (pageDiv.clientHeight * savedState.ratio);
                        container.scrollTo({ top: targetTop, behavior: 'auto' });
                        
                        // Update current page to match restored state
                        currentPage = savedState.page;
                    }
                }
            } catch (e) {
                console.error('Error restoring state:', e);
            } finally {
                // Release lock after restoration is done
                setTimeout(() => {
                    isRotating = false;
                }, 200);
            }
        }

        // Initialize
        loadPDF();

        // Handle Resize / Rotation
        let resizeTimeout;
        window.addEventListener('resize', () => {
            isRotating = true; // Lock state saving immediately
            
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                restoreState();
            }, 100);
        });

        // Disable keyboard shortcuts for print/save
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's')) {
                e.preventDefault();
                alert('Le téléchargement et l\'impression sont réservés aux membres Premium.');
            }
        });
    </script>
</body>

</html>
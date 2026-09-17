<?php
// Template base du layout de la page

/**
 * @var string $resultat - String de la reponse avec les données HTML
 * @var object|false $utilisateur - Utilisateur connecté ou false
 * @var array $filAriane - Données du fil d'Ariane
 */


$erreursCapturees = getenv('APP_DEBUG') == "true" ? \App\Fonction\GestionnaireException::recupererErreurs() : [];

?>
<!DOCTYPE html>
<html lang="fr" class="bg-slate-50">
<?php include(__DIR__ . "/../fragment/main/head.php"); ?>
<body class="min-h-screen w-full font-sans antialiased text-slate-800">
<div class="max-w-6xl mx-auto p-4 sm:p-6">
    <div class="w-[content] py-4">
        <img class="m-auto rounded-lg" src="public/images/logo.svg" aria-label="Logo QuiDitMieux avec le texte La plateforme d'enchères entre particuliers, Proposez, enchérissez, remportez" alt="QuiDitMieux, La plateforme d'enchères entre particuliers, Proposez, enchérissez, remportez">
    </div>
    <!-- Navigation -->
    <header class="flex items-center justify-between gap-4 mb-4 flex-wrap">
        <div class="flex items-center gap-1 bg-white border border-slate-200/80 rounded-xl p-1 shadow-sm">
            <a href="index.php" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors duration-150">
                Page Principale
            </a>
            <a href="index.php?categorie&action=list" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors duration-150">
                Nos Categories
            </a>
        </div>

        <div class="flex items-center gap-3">
            <?php if (!empty($utilisateur)): ?>
                <a href="index.php?utilisateur&action=index"
                   class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-900 text-white text-sm font-bold shadow-sm hover:bg-slate-700 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150">
                    <?= e(ucfirst(substr((string)$utilisateur->pseudo, 0, 1))) ?>
                </a>
            <?php else: ?>
                <a href="index.php?utilisateur&action=connecter"
                   class="h-10 w-10 flex items-center justify-center rounded-full bg-white border border-slate-200/80 shadow-sm text-slate-500 hover:text-slate-800 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <!-- Fil d'ariane -->
    <?php if (!empty($filAriane)): ?>
    <nav class="text-sm text-slate-600 flex items-center gap-1.5 flex-wrap mb-4 px-1">
        <?php foreach ($filAriane as $index => $item): ?>
            <?php if (!empty($item['url'])): ?>
                <a class="<?= $index === (count($filAriane) - 1) ? "font-bold" : "" ?>" href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
            <?php else: ?>
                <span class="<?= $index === (count($filAriane) - 1) ? "font-bold " : "" ?>text-slate-600"><?= e($item['label']) ?></span>
            <?php endif; ?>
            <span class="text-slate-600"><?= $index !== (count($filAriane) - 1) ? ">" : "" ?></span>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>



    <div class="rounded-2xl overflow-hidden border border-slate-200/80 shadow-sm bg-white">
        <?php if (!empty($erreursCapturees)): ?>
            <div class="my-4">
                <?php $erreurs = $erreursCapturees; ?>
                <?php include(__DIR__ . "/erreurs.php"); ?>
            </div>
        <?php endif; ?>
        <div class="flex-1 flex flex-col min-w-0">
            <main class="flex-1 p-4 sm:p-8 overflow-x-hidden">
                <?php if (isset($resultat)): ?>
                    <?= $resultat; ?>
                <?php endif; ?>
            </main>
            <?php include(__DIR__ . "/../fragment/main/footer.php"); ?>
        </div>
    </div>
</div>

<script type="module" src="./public/js/main.mjs"></script>

<?php if (getenv('APP_DEBUG') == "true"): ?>
    <?php include(__DIR__ . "/debug.php"); ?>
<?php endif; ?>
</body>
</html>
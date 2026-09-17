<?php
// Template des infos deboggage en bas de page
//
/**
 * @var array $stats - Liste avec les données des statistiques d'execution
 */
use App\Composant\Debogueur;
?>
<?php if (getenv('APP_DEBUG') == "true"): ?>
    <?php $stats = Debogueur::statistiques(); ?>
    <div class="fixed bottom-0 left-0 right-0 bg-slate-900 text-slate-200 text-xs font-mono px-4 py-2 flex gap-6 z-50 overflow-x-auto">
        <span>⏱ <?= $stats['temps_total_ms'] ?> ms</span>
        <span>🧠 <?= $stats['memoire_actuelle'] ?> (pic: <?= $stats['memoire_max'] ?>)</span>
        <span>🗄 <?= $stats['numero_requetes_sql'] ?> requêtes SQL</span>
        <span>🗄 <?= $stats['numero_requetes_api'] ?> requêtes API</span>        
        <span>🏭 Objets créés: <?= $stats['fabrique_modele']['objets_crees'] ?></span>
        <span>♻️ Objets réutilisés: <?= $stats['fabrique_modele']['objets_reutilises'] ?></span>
        <details class="cursor-pointer">
            <summary>Détails par classe</summary>
            <pre><?= print_r($stats['fabrique_modele']['par_classe'], true) ?></pre>
        </details>
        <details class="cursor-pointer">
            <summary>SQL (<?= $stats['numero_requetes_sql'] ?>)</summary>
            <pre><?= print_r($stats['requetes_sql'], true) ?></pre>
        </details>
        <details class="cursor-pointer">
            <summary>API (<?= $stats['numero_requetes_api'] ?>)</summary>
            <pre><?= print_r($stats['requetes_api'], true) ?></pre>
        </details>
        <details class="cursor-pointer">
            <summary>Messages (<?= count($stats['messages']) ?>)</summary>
            <?php foreach ($stats['messages'] as $msg): ?>
                <div class="border-t border-slate-700 py-1">
                    <span class="text-yellow-400"><?= htmlspecialchars($msg['etiquette'] ?: '—') ?></span>
                    <span class="text-slate-500">(<?= basename($msg['fichier'] ?? '') ?>:<?= $msg['ligne'] ?>)</span>
                    <pre><?= htmlspecialchars($msg['contenu']) ?></pre>
                </div>
            <?php endforeach; ?>
        </details>
    </div>
<?php endif; ?>
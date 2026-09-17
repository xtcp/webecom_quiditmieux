<?php
// Template pour une "card" d'une vente de la page principale/recherche

/**
 * @var object $vente - L'objet Vente a afficher
 */

// Calcul de la note moyenne à partir des notes associées

$labelsStatut = [0 => "À venir", 1 => "En cours", 2 => "", 3 => "Vendue", 4 => "Annulée"];
$labelsEtat = [0 => "Non renseignée", 1 => "Neuf", 2 => "Trés bon état", 3 => "Bon état", 4 => "État correct"];
?>

    <a href="index.php?vente&action=view&id=<?= e($vente->id) ?>"
        data-date="<?= e($vente->date) ?>"
        data-difficulte="<?= e($vente->difficulte) ?>"
        class="block bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-150 overflow-hidden group">

        <div class="h-32 bg-slate-100 flex items-center justify-center text-xs text-slate-400 font-medium">
        <?php $imagePrincipale = $vente->imagePrincipale(); ?>
        <?php if ($imagePrincipale): ?>
            <img src="<?= e("public/images/vente/".(string)$imagePrincipale->image) ?>" alt="<?= e($vente->titre) ?>" class="w-full h-full object-cover">
        <?php endif; ?>
            <div class="w-full h-full flex items-center justify-center text-slate-300">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16v12H4V6z"/>
                </svg>
            </div>
        </div>

        <div class="p-4 space-y-2">
            <span data-get-categorie class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase">
                <?= e($vente->categorie) ?>
            </span>

            <h4 class="text-sm font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors duration-150">
                <?= e($vente->titre) ?>
            </h4>

            <div class="flex items-center gap-1 text-amber-400 text-sm leading-none">
                <span><?= $labelsEtat[$vente->etat_produit->getValue()] ?? "Non renseignée" ?></span>
            </div>
            <div class="flex items-center gap-1 text-amber-400 text-sm leading-none">
                <span><?= $labelsStatut[$vente->status->getValue()] ?? "Indisponible" ?></span>
            </div>
            <div class="flex justify-between items-center gap-3 text-xs text-slate-400 pt-1">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= tempsRestant($vente->dateheure_fin) ?> (fin)
                </span>
                <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xl font-bold text-slate-600 w-fit">
                    <span id="enchere"><?= e($vente->derniereEnchere()?->prix ?? $vente->prix_depart) ?> €</span>
                </div>

            </div>
        </div>
    </a>

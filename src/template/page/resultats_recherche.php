<?php
// ~resultats_recherche.php Template pour afficher les resultats de recherche
/**
 * @var array $objets - Array avec les objets du modéle trouvées
 * @var ?string $table - Le nom du tableau/view template
 */

?>
<div class="max-w-5xl w-full px-4 font-sans">
    
    <div class="border-b border-slate-200 pb-5 mb-6">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Résultats de recherche</h2>
        <p class="text-sm text-slate-500 mt-1">
            <?php if (empty($objets)): ?>
                Aucune resultat ne correspond à vos critères de recherche.
            <?php else: ?>
                <?= e(count($objets)) ?> resultat(s) trouvé(s) correspondant à votre recherche.
            <?php endif; ?>
        </p>
    </div>

    <?php if (!empty($objets)): ?>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
            <?php 
  
            // ! A FAIRE - Mieux coder cette appel (passer probablement par un controleur ou service et seulement faire l'include de la ligne)
            $fragment = __DIR__ . '/../fragment/element/card/' . $table . '.php';
            if (!file_exists($fragment)) {
                $fragment = __DIR__ . '/../fragment/element/card/default.php';
                ?>
                <div class="p-5 flex flex-col sm:flex-row items-center gap-4 hover:bg-slate-50/60 transition-all duration-150 group">
                    <?php foreach ($objets[0]->champsAffichage() as $key => $value): ?>
                        <div class="flex-1 sm:w-32 flex-shrink-0 pt-0.5">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[8px] font-bold border tracking-wide uppercase bg-slate-50 border-slate-200 text-slate-600">
                                <?= e($key) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            }
            ?>
            <?php foreach ($objets as $objet): ?>
                <?php 
                    include($fragment);
                ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 border-dashed max-w-lg mx-auto mt-8 px-4">
            <div class="h-12 w-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mb-4 mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Aucun résultat trouvé</h3>
            <p class="text-sm text-slate-400 mt-1 max-w-xs mx-auto">Modifiez vos termes de recherche ou assurez-vous d'avoir saisi les bons filtres pour retrouver des resultats.</p>
            <div class="mt-6">
                <a href="index.php?vente&action=list" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs tracking-wide shadow-sm transition-all active:scale-[0.98]">
                    Retour à la liste complète
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
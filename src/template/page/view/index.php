<?php
// Template de page de recherche des ventes
//
/**
 * @var object|null   $utilisateur - Utilisateur connecté (null si visiteur)
 * @var array         $objets - Liste de Vente trouvées avec les criteres actuels (vide au premier chargement)
 */

$labelsStatut = [0 => "À venir", 1 => "En cours", 2 => "Terminée"];
$labelsEtat = [1 => "Neuf", 2 => "Trés bon état", 3 => "Bon état", 4 => "État correct"];
?>
<div id="vente-index" class="max-w-5xl w-full mx-auto py-2 px-4 font-sans space-y-8">

    <?php if (!$utilisateur): ?>
        <div class="bg-amber-50 border border-amber-200/80 text-amber-800 text-sm font-medium rounded-2xl px-5 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Vous voulez créer votre propre vente ou acheter?
            <a href="index.php?utilisateur&action=connecter" class="text-indigo-600 hover:text-indigo-700 font-bold underline underline-offset-2">Connectez-vous!</a>
        </div>
    <?php endif; ?>


    <div class="flex flex-col lg:flex-row gap-8">

        <aside class="w-full lg:w-64 flex-shrink-0 space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-5">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 border-b">Filtre des ventes</label>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">État tu produit</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <select name="etat_produit" id="etat_produit"  class="pill-select">
                            <option value="">Tous</option>
                        <?php foreach ($labelsEtat as $index => $label): ?>
                            <option value="<?= e($index) ?>">
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Statut</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <select name="status" id="status" class="pill-select">
                            <option value="">Tous</option>
                            <option value="1">En cours</option>
                            <option value="2">Terminée</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input id="jsrecherche-prix-min" type="number" min="0" placeholder="Prix min"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm focus:outline-none placeholder:text-slate-400">
                    <span class="text-slate-400 text-sm">—</span>
                    <input id="jsrecherche-prix-max" type="number" min="0" placeholder="Prix max"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm focus:outline-none placeholder:text-slate-400">
                </div>
            </div>
        </aside>

        <!-- Colonne de droite: recherche + résultats -->
        <div class="flex-1 min-w-0 space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
                <h2 class="text-base font-bold text-slate-900 tracking-tight text-center">Quel objet vous fait envie?</h2>

                <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 flex-wrap">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <div class="flex-1 flex items-center gap-2 flex-wrap">

                        <input id="jsrecherche-vente-input" type="text" placeholder="Rechercher une vente..."
                                    class="flex-1 min-w-[120px] bg-transparent text-sm focus:outline-none placeholder:text-slate-400">
                    </div>

                    <button type="button" id="recherche-effacer" class="text-xs font-semibold text-slate-600 hover:text-red-500 flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Effacer
                    </button>
                </div>
                

                <div>
                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rechercher par catégorie:</span>
                    <div class="pb-1">
                        <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 flex-wrap">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <div class="flex-1 flex items-center gap-2 flex-wrap">
                                <div id="jsrecherche-recherche_categorie-container" class="flex">
                                    <div id="jsrecherche-recherche_categorie-inputs" class="flex flex-wrap gap-2"></div>
                                </div>
                                <input id="jsrecherche-recherche_categorie-input" type="text" placeholder="Rechercher une categorie..."
                                    class="flex-1 min-w-[120px] bg-transparent text-sm focus:outline-none placeholder:text-slate-400">
                            </div>

                            <button type="button" id="recherche-effacer" class="text-xs font-semibold text-slate-600 hover:text-red-500 flex items-center gap-1 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Effacer
                            </button>
                        </div>
                        <div id="jsrecherche-recherche_categorie-results" class="hidden results z-99999 fixed z-40 mt-1 w-64 bg-white/10 backdrop-blur-lg border border-gray-200 rounded-md shadow-lg px-4">
                            <ul>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 id="jsrecherche-vente-resultinfo" class="text-sm font-bold text-slate-500 mb-3">
                    <?php if (empty($objets)): ?>
                        Aucune vente trouvée pour le moment
                    <?php else: ?>
                        <?php if (count($objets) === 1): ?>
                            <?= e(count($objets)) ?> vente trouvée:
                        <?php else: ?>
                            <?= e(count($objets)) ?> ventes trouvées:
                        <?php endif; ?>
                    <?php endif; ?>
                </h3>

                <?php if (empty($objets)): ?>
                    <div class="text-center py-14 bg-white rounded-2xl border border-slate-200 border-dashed">
                        <div class="h-12 w-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Ajoutez des ingrédients pour commencer</h3>
                        <p class="text-sm text-slate-400 mt-1">Les ventes correspondant à vos critères apparaîtront ici.</p>
                    </div>
                <?php else: ?>

                    <div id="jsrecherche-vente-container" class="grid sm:grid-cols-2 gap-4">
                        <div id="jsrecherche-vente-inputs" class="flex flex-wrap gap-2 hidden"></div>
                        <?php foreach ($objets as $vente): ?>
                            <?php include(__DIR__ . "../../../fragment/element/card/template_card_vente.php"); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!--
<?php // include(__DIR__ . "../../../fragment/element/card/template_chargement.php"); ?>
<template id="jsrecherche-template-vente">
    <?php // include(__DIR__ . "../../../fragment/element/card/template_card_vente.php"); ?>
</template>

 -->
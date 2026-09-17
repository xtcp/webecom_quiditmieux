<?php
// Template de la section ventes du tableau de bord

/**
 * @var array $encheresEnCours - Tableau avec toutes les enchères en cours
 * @var array $mesEncheresEnCours - Tableau avec les enchères en cours de l'utilisateur (suivis, enchèries)
 * @var array $dernieresVentes - Tableau avec les dérniéres vente de l'utilisateur
 * @var array $mesEncheresRemportees - Tableau avec les dérniéres vente de l'utilisateur

*/


?>
<div class="max-w-5xl w-full mx-auto py-8 px-4 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 mt-7">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Enchères en cours</h2>
            <p class="text-sm text-slate-500">Dernières enchères en cours</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?vente&action=add" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-500/10 hover:shadow-md transition-all active:scale-[0.98]">
                Ajouter une vente
            </a>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
    <?php if (!empty($encheresEnCours)): ?>
    <?php foreach ($encheresEnCours as $vente): ?>
        <div class="p-5 flex flex-col sm:flex-row items-start gap-4 hover:bg-slate-50/60 transition-all duration-150 group">
            
            <div class="sm:w-32 flex-shrink-0 pt-0.5">
                <span data-get-categorie class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border tracking-wide uppercase bg-slate-50 border-slate-200 text-slate-600">
                    <?= e($vente->categorie) ?>
                </span>
            </div>
            <div class="flex w-full justify-between">
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-semibold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors duration-150">
                        <a href="index.php?vente&action=view&id=<?= e($vente->id) ?>" class="focus:outline-none">
                            <?= e($vente->titre) ?>
                        </a>
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="font-medium text-slate-700"><?= e($vente->utilisateur->pseudo) ?></span>
                        <span class="text-slate-300">•</span>
                        <span>Le <?= e($vente->dateFormatee) ?></span>
                    </div>
                </div>
                <div class="sm:w-62 flex-shrink-0 pt-0.5 flex items-center justify-between text-nowrap">
                    <div class="">
                        <div class="px-4 py-2.5 w-[fit-content] rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm bg-white">
                        <?php if ($vente->estTerminee()): ?>
                        Terminée
                        <?php else: ?>
                        En cours
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xl font-bold text-slate-600 w-fit">
                        <span id="enchere"><?= e($vente->derniereEnchere()?->prix ?? $vente->prix_depart) ?> €</span>
                    </div>
                </div>
            </div>
            <div class="self-center hidden sm:block opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-x-2 group-hover:translate-x-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    <?php endforeach; ?>
    <?php else: ?>
        <span class="px-5 text-base/15">Aucune enchère en cours, revenez plus tard!</span>
    <?php endif; ?>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 mt-7">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Mes Enchères en cours</h2>
            <p class="text-sm text-slate-500">Historique de vos dernières enchères</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?vente&action=add" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-500/10 hover:shadow-md transition-all active:scale-[0.98]">
                Ajouter une vente
            </a>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
    <?php if (!empty($mesEncheresEnCours)): ?>
    <?php foreach ($mesEncheresEnCours as $vente): ?>
        <div class="p-5 flex flex-col sm:flex-row items-start gap-4 hover:bg-slate-50/60 transition-all duration-150 group">
            
            <div class="sm:w-32 flex-shrink-0 pt-0.5">
                <span data-get-categorie class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border tracking-wide uppercase bg-slate-50 border-slate-200 text-slate-600">
                    <?= e($vente->categorie) ?>
                </span>
            </div>
            <div class="flex w-full justify-between">
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-semibold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors duration-150">
                        <a href="index.php?vente&action=view&id=<?= e($vente->id) ?>" class="focus:outline-none">
                            <?= e($vente->titre) ?>
                        </a>
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="font-medium text-slate-700"><?= e($vente->utilisateur->pseudo) ?></span>
                        <span class="text-slate-300">•</span>
                        <span>Le <?= e($vente->dateFormatee) ?></span>
                    </div>
                </div>
                <div class="sm:w-62 flex-shrink-0 pt-0.5 flex items-center justify-between text-nowrap">
                    <div class="">
                        <div class="px-4 py-2.5 w-[fit-content] rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm bg-white">
                        <?php if ($vente->estTerminee()): ?>
                        Terminée
                        <?php else: ?>
                        En cours
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xl font-bold text-slate-600 w-fit">
                        <span id="enchere"><?= e($vente->derniereEnchere()?->prix ?? $vente->prix_depart) ?> €</span>
                    </div>
                </div>
            </div>
            <div class="self-center hidden sm:block opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-x-2 group-hover:translate-x-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    <?php endforeach; ?>
    <?php else: ?>
        <span class="px-5 text-base/15">Vous n'avez pas d'enchères en cours, encherissez une vente pour la voir dans cette liste!</span>
    <?php endif; ?>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 mt-7">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Mes Dernières Ventes</h2>
            <p class="text-sm text-slate-500">Historique de vos dernières ventes</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?vente&action=add" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-500/10 hover:shadow-md transition-all active:scale-[0.98]">
                Ajouter une vente
            </a>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
    <?php if (!empty($dernieresVentes)): ?>
        <?php foreach ($dernieresVentes as $vente): ?>
        <div class="p-5 flex flex-col sm:flex-row items-start gap-4 hover:bg-slate-50/60 transition-all duration-150 group">
            
            <div class="sm:w-32 flex-shrink-0 pt-0.5">
                <span data-get-categorie class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border tracking-wide uppercase bg-slate-50 border-slate-200 text-slate-600">
                    <?= e($vente->categorie) ?>
                </span>
            </div>
            <div class="flex w-full justify-between">
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-semibold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors duration-150">
                        <a href="index.php?vente&action=view&id=<?= e($vente->id) ?>" class="focus:outline-none">
                            <?= e($vente->titre) ?>
                        </a>
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="font-medium text-slate-700"><?= e($vente->utilisateur->pseudo) ?></span>
                        <span class="text-slate-300">•</span>
                        <span>Le <?= e($vente->dateFormatee) ?></span>
                    </div>
                </div>
                <div class="sm:w-62 flex-shrink-0 pt-0.5 flex items-center justify-between text-nowrap">
                    <div class="">
                        <div class="px-4 py-2.5 w-[fit-content] rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm bg-white">
                        <?php if ($vente->estTerminee()): ?>
                        Terminée
                        <?php else: ?>
                        En cours
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xl font-bold text-slate-600 w-fit">
                        <span id="enchere"><?= e($vente->derniereEnchere()?->prix ?? $vente->prix_depart) ?> €</span>
                    </div>
                </div>
            </div>
            <div class="self-center hidden sm:block opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-x-2 group-hover:translate-x-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <span class="px-5 text-base/15">Aucune vente ajoutée, créer votre premiére vente!</span>
    </div>
    <?php endif; ?>
</div>

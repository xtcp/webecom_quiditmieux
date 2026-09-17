<?php
// Template pour afficher le tableau de bord avec section ventes et gestion de compte
/**
 * @var string $sectionVentes - Données HTML de la section ventes
 */

?>
<div>
    <div class="row">
        <div class="max-w-5xl w-full mx-auto px-4 font-sans">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <div class="pb-4">
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Mon Compte</h2>
                        <p class="text-sm text-slate-500">Gestion de mon compte</p>
                    </div>
                    <div>
                        <a class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm bg-white hover:bg-slate-50/80 transition-all active:scale-[0.98]" title="Modifier les données de son compte" href="index.php?utilisateur&action=edit" aria-label="Modifier les données de son compte">Modifier mon compte</a>

                        <a class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm bg-white hover:bg-slate-50/80 transition-all active:scale-[0.98]" title="Telecharger les données de son compte" href="index.php?utilisateur&action=telecharger" aria-label="Telecharger les données de son compte">Telecharger mes données</a>
                        <a class="px-3 py-2 rounded-lg border border-red-200 bg-red-30 hover:bg-red-100 text-red-600 font-semibold text-xs tracking-wide transition-all duration-150" title="Supprimer mon compte" href="index.php?utilisateur&action=del" aria-label="Supprimer mon compte">Supprimer mon compte</a>
                        <a class="px-3 py-2 rounded-lg border border-red-200 bg-red-30 hover:bg-red-100 text-red-600 font-semibold text-xs tracking-wide transition-all duration-150" title="Se deconnected du compte" href="index.php?utilisateur&action=deconnecter" aria-label="Se deconnecter du compte">Deconnecter</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <?= $sectionVentes ?>
        </div>
        <div class="col-md-4">
            
        </div>
    </div>
</div>
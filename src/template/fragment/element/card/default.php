<?php
// fragment/element/card/default.php Fragment pour afficher une ligne d'une vue (resultat recherche)
/**
 * @var object $objet - L'objet a afficher
 * @var ?string $table - La table du modele/template
 */

?>

<a class="block" href="index.php?<?= e($table) ?>&action=view&id=<?= e($objet->id) ?>">
    <div class="p-5 flex flex-col sm:flex-row items-start gap-4 hover:bg-slate-50/60 transition-all duration-150 group">
        
        <?php foreach ($objet->champsAffichage() as $cle => $champ): ?>
            <div class="flex-1 space-y-1">
                <div class="flex justify-center gap-2 text-xs text-slate-400">
                    <span><?= e($champ->getValue()) ?></span>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="self-center hidden sm:block opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-x-2 group-hover:translate-x-0">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>
</a>

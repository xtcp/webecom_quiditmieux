<?php
// Fragment pour afficher une page view generique
/**
 * @var string $name - Le nom du modéle de l'objet
 * @var string $table - La table du modéle de l'objet
 * @var object $objet - L'objet a afficher
 */

?>

<div class="max-w-4xl w-full mx-auto py-8 px-4 font-sans space-y-8">
    
    <div class="flex items-center justify-between border-b border-slate-200/60 pb-6">
        <div class="flex items-center gap-4">
            <h3 class="text-xl font-bold text-slate-900 tracking-tight"><?= e($table) ?> #<?= e($objet->id) ?></h3>
        </div>
    </div>

    <?php foreach ($objet as $key => $value): ?>

        <?php if (!$value->blocked || $value->blocked === false): ?>
        <div class="bg-white rounded-2xl border-2 border-slate-200/80 shadow-sm p-6 space-y-4 relative">
            <div class="flex items-center gap-3">
                <div>
                    <span class="block text-sm font-bold text-slate-900"><?= e($key) ?></span>
                </div>
            </div>
            <div class="text-sm leading-relaxed text-slate-700 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
            <?= e($value) ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
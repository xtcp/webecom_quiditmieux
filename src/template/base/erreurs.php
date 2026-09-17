<?php
// Fragment réutilisable pour afficher la liste des erreurs/avertissements capturés
/**
 * @var array $erreurs - Liste des erreurs enregistrées par GestionnaireException
 */
?>
<?php if (!empty($erreurs)): ?>
    <?php foreach ($erreurs as $erreur): ?>
        <div class="max-w-2xl mx-auto mt-8 bg-red-50 border border-red-200 rounded-2xl p-6 text-red-800 shadow-sm">
            <div class="flex items-center gap-3 mb-2 text-red-700">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <?php if ($erreur['type'] === 'Exception'): ?>
                <h3 class="font-bold text-base">Une erreur est survenue</h3>
                <?php else: ?>
                <h3 class="font-bold text-base"><?= e($erreur['type']) ?></h3>
                <?php endif; ?>
            </div>
            <p class="text-sm font-medium leading-relaxed">
                <?= e($erreur['message'] ?? $erreur['objet']->getMessage()); ?>
            </p>

            <?php if (getenv('APP_DEBUG') == "true"): ?>
                <div class="mt-4 pt-4 border-t border-red-200/60">
                    <span class="block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">Stack Trace :</span>
                    <pre class="p-4 bg-slate-900 text-slate-300 rounded-xl text-xs overflow-x-auto font-mono leading-relaxed max-h-96"><?= e(formaterJava($erreur['objet'], null, $erreur['trace'] ?? null)); ?></pre>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
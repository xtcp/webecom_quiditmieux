<?php
// Template pour modifier le profil de l'utilisateur (pseudo, email, mot de passe)

/**
 * @var object $utilisateur - L'objet Utilisateur connecté à modifier
 * @var object $erreurs_form - Liste d'erreurs de champ de formulaire
 * @var object $message - Message du resultat de l'action
 */

use App\Composant\Debogueur;

?>
<div class="max-w-2xl w-full mx-auto space-y-8 font-sans">

    <h2 class="text-lg font-bold text-slate-400 uppercase tracking-wide">Modifier mon profil</h2>

    <?php if ($message && $message["texte"]): ?>
        <div class="info-message text-<?= $message['couleur'] ?>-500 bg-<?= $message['couleur'] ?>-200 border rounded-xl border-<?= $message['couleur'] ?>-200 p-4">
            <?= $message["texte"] ?>
        </div>
    <?php endif; ?>
    <form id="form-modifier-profil" action="index.php?utilisateur&action=edit" method="post" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8 space-y-6">

        <!-- Pseudo -->
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pseudo</label>
            <input name="pseudo" type="text" required value="<?= $_POST["pseudo"] ?? e($utilisateur->pseudo) ?>"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <?php if (isset($erreurs_form["pseudo"])): ?>
                <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["pseudo"]); ?></p>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Email</label>
            <input name="email" type="email" required value="<?= $_POST["email"] ?? e($utilisateur->email) ?>"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <?php if (isset($erreurs_form["email"])): ?>
                <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["email"]); ?></p>
            <?php endif; ?>
        </div>

        <!-- Mot de passe -->
        <div class="pt-4 border-t border-slate-200/80 space-y-4">
            <p class="text-xs text-slate-400">Laissez les champs ci-dessous vides pour conserver votre mot de passe actuel.</p>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nouveau mot de passe</label>
                <input name="motdepasse" type="password" placeholder="••••••••" autocomplete="new-password"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400">
                <?php if (isset($erreurs_form["motdepasse"])): ?>
                    <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["motdepasse"]); ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Confirmer le mot de passe</label>
                <input name="motdepasse_confirmation" type="password" placeholder="••••••••" autocomplete="new-password"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400">
                <?php if (isset($erreurs_form["motdepasse_confirmation"])): ?>
                    <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["motdepasse_confirmation"]); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-150">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
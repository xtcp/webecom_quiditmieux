<?php
// Template pour formulaire de connection/inscription
/**
 * @var string $emailpseudo - L'email/utilisateur utilisée l'ors du dernier essaie de connection
 * @var ?array $message - Message d'info ou erreur
 * @var array $erreurs_form - Erreurs de formulaire associé aux champs
 */


?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="max-w-4xl w-full mx-auto space-y-10 font-sans">

    <div class="text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Mon compte</h2>
        <p class="mt-2 text-sm text-slate-500">Vous voulez créer vos propres ventes et enchérire?<br>Accédez à votre compte.</p>
    </div>
    <?php if ($message && $message["texte"]): ?>
        <div class="info-message text-<?= $message['couleur'] ?>-500 bg-<?= $message['couleur'] ?>-200 border rounded-xl border-<?= $message['couleur'] ?>-200 p-4">
            <?= $message["texte"] ?>
        </div>
    <?php endif; ?>
    <div class="grid md:grid-cols-2 gap-8">

        <!-- Connexion -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 transition-shadow duration-150 hover:shadow-md flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Connectez-vous</h3>
            </div>

            <form id="form-login" class="space-y-4" action="index.php?utilisateur&action=connecter" method="post">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email / Pseudo</label>
                    <input name="emailpseudo" type="text" required placeholder="nom@exemple.fr ou pseudo" value="<?= e($emailpseudo ?? ''); ?>"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Mot de passe</label>
                    <input name="motdepasse" type="password" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-sm transition-all duration-150">
                        Se connecter
                    </button>
                </div>
            </form>
        </div>

        <!-- Inscription -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 transition-shadow duration-150 hover:shadow-md flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Créez un compte</h3>
            </div>

            <form id="form-inscription" class="space-y-4" action="index.php?utilisateur&action=create" method="post">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email</label>
                    <input name="email" type="email" required placeholder="expert@societe.com" value="<?= e($email ?? ''); ?>"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800">
                <?php if (isset($erreurs_form["email"])): ?>
                    <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["email"]); ?></p>
                <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Pseudo</label>
                    <input name="pseudo" type="text" required placeholder="Votre pseudo" value="<?= e($pseudo ?? ''); ?>"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800">
                <?php if (isset($erreurs_form["pseudo"])): ?>
                    <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["pseudo"]); ?></p>
                <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Mot de passe</label>
                    <input name="motdepasse" type="password" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800">
                    <?php if (isset($erreurs_form["motdepasse"])): ?>
                        <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["motdepasse"]); ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex items-start gap-2 pt-1">
                        <input type="checkbox" name="consentement" id="consentement" required value="1"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/40">
                        <label for="consentement" class="text-xs text-slate-500 leading-relaxed">
                            J'accepte que mes données personnelles soient traitées conformément à la
                            <a href="index.php?app&action=politique" target="_blank" class="text-emerald-600 underline hover:text-emerald-700">politique de confidentialité</a>. *
                        </label>
                    <?php if (isset($erreurs_form["consentement"])): ?>
                        <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["consentement"]); ?></p>
                    <?php endif; ?>
                    </div>
                <div class="g-recaptcha" data-sitekey="<?= e(getenv("RECAPTCHA_SITE_KEY")); ?>" data-callback="onSubmit" data-action="submit"></div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-sm transition-all duration-150">
                        Créer mon compte
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
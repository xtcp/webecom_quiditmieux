<?php
// Fragment/main: Fragment pour le footer du site
// Role: Affiche le pied de page avec le logo (texte), les liens de navigation et le lien vers la politique de confidentialité
?>
<footer class="bg-white border-t border-slate-200/80 px-8 py-6">
    <div class="flex items-center justify-between flex-wrap gap-4">

        <div>
            <span class="font-bold text-lg text-slate-900">QuiDitMieux</span>
        </div>

        <nav class="flex items-center gap-6 text-sm">
            <a href="index.php">Page Principale</a>
            <a href="index.php?categorie&action=list">Nos Categories</a>
            <a href="index.php?app&action=politique">Politique de confidentialité</a>
        </nav>

    </div>

    <div class="mt-4 text-xs text-slate-400 text-center">
        <p>© 2026 QuiDitMieux. Tous droits réservés.</p>
        <p>Projet de examen Web Dev Webecom 2026 par Micael Pereira Ribeiros</p>
    </div>
</footer>
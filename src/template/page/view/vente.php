<?php
// Template pour consulter une vente

/**
 * @var object $objet - L'objet Vente a afficher
 * @var object|false $utilisateur - Utilisateur connecté ou false
 * @var array $message - Array avec message d'info ou d'erreur
 */


$labelsStatut = [0 => "À venir", 1 => "En cours", 2 => "Terminée"];
$labelsEtat = [1 => "Neuf", 2 => "Trés bon état", 3 => "Bon état", 4 => "État correct"];
$estProprietaire = fn($proprietaireId) => $utilisateur && (int)(string)$utilisateur->id->getValue() === $proprietaireId;
use App\Composant\Debogueur;
if (isset($message) && getenv("APP_DEBUG") == "true") Debogueur::message($message);
if (isset($erreurs_form) && getenv("APP_DEBUG") == "true") Debogueur::message($erreurs_form);

?>
<div id="consulter-vente" class="max-w-5xl w-full mx-auto space-y-10 font-sans">
    <?php if (isset($message)): ?>
        <div class="info-message text-<?= $message['couleur'] ?>-500 bg-<?= $message['couleur'] ?>-200 border rounded-xl border-<?= $message['couleur'] ?>-200 p-4">
            <?= $message["texte"] ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start">

        <!-- Images -->
        <div class="lg:col-span-2 relative">
            <div class="aspect-[4/3] max-w-sm rounded-2xl overflow-hidden bg-slate-100 border border-slate-200/80 relative group">

                <?php if (!empty($objet->images)): ?>
                    <div id="carousel-images" class="flex h-full overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar">
                        <?php foreach ($objet->images as $image): ?>
                            <img src="<?= e("public/images/vente/" . (string)$image->image) ?>"
                                alt="<?= e($objet->titre) ?>"
                                class="w-full h-full object-cover flex-shrink-0 snap-center">
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($objet->images) > 1): ?>
                        <button type="button" id="carousel-prev" aria-label="Image précédente"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full bg-white/80 backdrop-blur text-slate-700 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" id="carousel-next" aria-label="Image suivante"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full bg-white/80 backdrop-blur text-slate-700 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
                            <?php foreach ($objet->images as $index => $image): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-white/70"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16v12H4V6z"/>
                        </svg>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <!-- Infos -->
        <div class="lg:col-span-3 flex flex-col gap-4">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight"><?= e($objet->titre) ?></h2>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <?php if ($utilisateur): ?>
                    <?php if (!$estProprietaire): ?>
                    <a href="index.php?vente&action=favorite&id=<?= e($objet->id) ?>"
                        class="px-3 py-2 rounded-lg border border-green-200 bg-blue-50 hover:bg-green-100 text-green-700 font-semibold text-xs tracking-wide transition-all duration-150">
                        Ajouter aux favoris
                    </a>
                    <?php endif; ?>
                    <?php if (($estProprietaire((int)$objet->utilisateur->id->getValue())) && ($objet->status->getValue() != 2) && (!(!empty($objet->encheres) && $objet->status->getValue() == 1))): ?>
                    <a href="index.php?vente&action=edit&id=<?= e($objet->id) ?>"
                        class="px-3 py-2 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs tracking-wide transition-all duration-150">
                        Modifier
                    </a>
                    <a href="index.php?vente&action=del&id=<?= e($objet->id) ?>"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vente?');"
                        title="Effacer la vente"
                        aria-label="Effacer la vente"
                        class="px-3 py-2 rounded-lg border border-red-200 bg-red-30 hover:bg-red-100 text-red-600 font-semibold text-xs tracking-wide transition-all duration-150">
                        Effacer
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                
            </div>

            <span class="text-sm text-slate-500">Par <span class="font-semibold text-slate-700"><?= e($objet->utilisateur->pseudo) ?></span></span>


            <div class="flex flex-row gap-2 justify-between items-center">
                <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xs text-slate-600 w-fit">
                    <span><?= e($labelsStatut[$objet->status->getValue()]) ?></span>
                </div>
                <div class="border border-slate-200 rounded-xl px-4 py-3 bg-slate-50/60 text-xl font-bold text-slate-600 w-fit">
                    <span id="enchere"><?= e($objet->derniereEnchere()?->prix ?? $objet->prix_depart) ?> €</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2" data-get-categorie>
                <span data-get-categorie class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase transition-transform duration-150">
                    <?= e($objet->categorie) ?>
                </span>
            </div>

            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 border-y border-slate-100 py-4">


                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m16.5 9.4-9-5.19"/>
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/>
                    <path d="m3.27 6.96 8.73 5.05 8.73-5.05"/>
                    <path d="M12 22.08V12"/>
                    <circle cx="18" cy="18" r="4" fill="currentColor" stroke="none"/>
                    <path d="m16.5 18 1 1 2-2" stroke="white"/>
                </svg>
                <span class="flex items-center gap-1.5">
                    État du Produit: <?= e($labelsEtat[$objet->etat_produit->getValue()]) ?>
                </span>

                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= tempsRestant($objet->dateheure_fin) ?> (fin)
                </span>
                <span class="text-slate-300">•</span>

            </div>
            <p class="text-sm leading-relaxed text-slate-600"><?= e(nl2br($objet->description)) ?></p>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-[300px_1fr] gap-8">

    </div>


    <!-- Encheres -->
    <div class="space-y-6">
        <h3 class="text-lg font-bold text-slate-900 border-b border-slate-200/60 pb-4">Encheres</h3>

        <div class="space-y-4">
            <?php if (!empty($objet->enchere)): ?>
                <?php foreach ($objet->enchere as $enchere): ?>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2 transition-shadow duration-150 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900"><?= e($enchere->utilisateur->pseudo) ?></span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs text-grey-200">Le <?= e($enchere->dateFormatee) ?></span>
                                
                            </div>
                        </div>
                        <p class="text-xl font-bold leading-relaxed text-slate-600"><?= e(nl2br($enchere->prix)) ?> €</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-slate-400 italic">Aucune enchère pour le moment. Soyez le premier à enchérir!</p>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
            <form id="form-repondre" class="space-y-4 flex flex-col w-full" action="index.php?enchere&action=add" method="post">
                <input id="prix" name="prix" type="number" required placeholder="Ecrivez le valeur de l'enchère que vous voulez placer"
                          class="w-full min-h-24 p-4 rounded-xl border border-slate-200 text-3xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-400 text-slate-800 leading-relaxed"></textarea>
                <div class="flex justify-end pt-1">
                    <input type="hidden" name="vente" value="<?= e($objet->id) ?>">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-150">
                        Placer Enchère
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

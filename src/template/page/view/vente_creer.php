<?php
// Template pour creer/modifier une vente

/**
 * @var ?object $objet - L'objet Vente à modifier
 * @var array $message - Array avec un message d'info ou erreur
 * @var array $erreurs_form - Array avec des erreurs des champs du formulaire
 */


$labelsEtat = [1 => "Neuf", 2 => "Trés bon état", 3 => "Bon état", 4 => "État correct"];
$labelsStatut = [0 => "À venir", 1 => "En cours"];
$type = empty($objet) ? "add" : "edit";

$dateheureFinValue = '';
if (!empty($objet)) {
    $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $objet->dateheure_fin->getValue());
    if ($dateTime) $dateheureFinValue = $dateTime->format('Y-m-d\TH:i:s');
}

use App\Composant\Debogueur;
if (!empty($message) && getenv("APP_DEBUG") == "true") Debogueur::message($message);
if (!empty($erreurs_form) && getenv("APP_DEBUG") == "true") Debogueur::message($erreurs_form);
?>
<div id="vente-creer" class="max-w-5xl w-full mx-auto space-y-8 font-sans">
    <?php if (!empty($message)): ?>
        <div class="info-message text-<?= $message['couleur'] ?>-500 bg-<?= $message['couleur'] ?>-200 border rounded-xl border-<?= $message['couleur'] ?>-200 p-4">
            <?= $message["texte"] ?>
        </div>
    <?php endif; ?>
    <h2 class="text-lg font-bold text-slate-400 uppercase tracking-wide">
    <?php if (!empty($objet)): ?>
        Modifier la vente
    <?php else: ?>
        Creer une vente
    <?php endif; ?>
    </h2>

    <!-- Formulaire principal : champs directs de la vente -->
    <form id="form-modifier-vente" data-type="<?= $type ?>" action="index.php?vente&action=<?= $type ?>" method="post" enctype="multipart/form-data" class="space-y-8">
    <?php if (!empty($objet)): ?>
        <input type="hidden" name="id" value="<?= e($objet->id) ?>">
    <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start">
            <div class="lg:col-span-2">
                <div class="max-w-sm">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Images de la vente</label>

                    <div id="galerie-images" class="grid grid-cols-3 gap-2 mb-3"></div>
                    <template id="tpl-carte-image">
                        <div class="carte relative aspect-square rounded-xl overflow-hidden bg-slate-100 border-2 border-slate-200 cursor-pointer group">
                            <img class="w-full h-full object-cover">
                            <span class="badge absolute top-1 left-1 bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded hidden">★ Principale</span>
                            <button type="button" class="supprimer absolute top-1 right-1 w-5 h-5 flex items-center justify-center rounded-full bg-black/50 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">✕</button>
                        </div>
                    </template>
                    <input type="hidden" name="image_principale" id="image_principale" value="<?= !empty($objet) ? $objet->image_principale : 0 ?>" />
                    <input type="file" id="input-images" name="images[]" accept="image/*" multiple class="hidden">
                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
                    <button type="button" onclick="document.getElementById('input-images').click()"
                            class="px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 hover:-translate-y-0.5 active:translate-y-0 text-slate-700 text-xs font-semibold shadow-sm transition-all duration-150">
                        Ajouter des images
                    </button>
                    <p class="text-[11px] text-slate-400 mt-2">Clique sur une image pour la définir comme image principale (★).</p>

                    <?php if (!empty($objet)): ?>
                        <?php
                            $imagesExistantes = [];
                            foreach ($objet->images as $image) {
                                $imagesExistantes[] = [
                                    "id"         => (string)$image->id,
                                    "url"        => "public/images/vente/" . (string)$image->image,
                                    "principale" => (string)$image->id === (string)$objet->image_principale,
                                ];
                            }
                        ?>
                        <script id="donnees-images-existantes" type="application/json"><?= json_encode($imagesExistantes) ?></script>
                    <?php endif; ?>
                </div>
            </div>
            <div class="lg:col-span-3">
                <!-- Titre + temps -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start">
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Titre</label>
                        <input name="titre" type="text" required value="<?= !empty($objet) ? e($objet->titre) : '' ?>"
                            class="w-full text-2xl sm:text-xl font-bold text-slate-900 tracking-tight px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["titre"] ?? ''); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 md:grid-cols-2 gap-6 lg:gap-10 items-start py-4">
                    <div class="flex flex-wrap flex-col gap-4 w-full">
                        <div class="">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                            <div class="custom-select-container">
                                <select name="status" class="pill-select">
                                    <?php foreach ($labelsStatut as $valeur => $label): ?>
                                        <option value="<?= e($valeur) ?>" <?= !empty($objet) && $objet->status->getValue() == $valeur ? "selected" : "" ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["status"] ?? ''); ?></p>
                        </div>
                        <div class="">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">État du produit</label>
                            <div class="custom-select-container">
                                <select name="etat_produit" class="pill-select">
                                    <?php foreach ($labelsEtat as $valeur => $label): ?>
                                        <option value="<?= e($valeur) ?>" <?= !empty($objet) && $objet->etat_produit->getValue() == $valeur ? "selected" : "" ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["etat_produit"] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="flex flex-wrap flex-col gap-4 w-full">
                        
                        <div class="">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Heure Fin de l'Enchère</label>
                            <input id="dateheure_fin" name="dateheure_fin" class="pill-date" type="datetime-local"  min="<?= date('Y-m-d\TH:i') ?>" step="1" max="" value="<?= e($dateheureFinValue) ?>">
                            <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["dateheure_fin"] ?? ''); ?></p>
                        </div>
                    </div>
                </div>

                
                <div>
                    <!-- Catégorie -->
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Catégorie</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <div id="jsrecherche-categorie-container">
                            <div id="jsrecherche-categorie-inputs"></div>
                            
                        </div>

                        <input id="jsrecherche-categorie-input" class="pl-5 w-full rounded-lg border border-blue-200/20 focus:outline-none focus:border-blue-400/40" autocomplete="off" placeholder="Rechercher une categorie..."/>
                        <div id="jsrecherche-categorie-results" class="results z-99999 fixed z-40 mt-1 w-64 bg-white/10 backdrop-blur-lg border border-gray-200 rounded-md shadow-lg ">
                            <ul>
                            </ul>
                        </div>
                    <p class="pt-2 ml-2 text-xs text-red-700"><?= e($erreurs_form["categorie"] ?? ''); ?></p>
                    </div>
                    <!-- Description -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Description</label>
                        <textarea name="description" class="w-full min-h-28 p-4 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all leading-relaxed"
                            required><?= !empty($objet) ? e($objet->description) : '' ?></textarea>
                    </div>
                    <!-- Prix Depart -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Prix de depart</label>
                        <div class="inline-flex items-center gap-2">
                            <input name="prix_depart" class="w-45 py-2 px-6 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all leading-relaxed"
                                required><?= !empty($objet) ? e($objet->prix_depart) : '' ?></textarea>
                            <span>€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-center pt-2">
            <input type="submit" value="Confirmer Modifications" class="px-8 py-3 bg-slate-900 hover:bg-slate-800 cursor-pointer text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-150">
        </div>
    </form>
</div>

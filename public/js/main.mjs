// Module principale qui charge les autres modules en rapport avec la page/elements affichées
import { e, tempsRestant } from "./helpers.mjs";

import { Rechercher } from "./rechercher.mjs";

import { initCategories, afficherCategorie } from "./categories.mjs";
import { initImages } from "./images.mjs";
import { initEncheres } from "./encheres.mjs";


// ---------
// Vente
// ---------

const labelsStatut = {0: "À venir", 1: "En cours", 2: "Terminée"};
const labelsEtat = { 1: "Neuf", 2: "Trés bon état", 3: "Bon état", 4: "État correct"};

const svgNS = "http://www.w3.org/2000/svg";


// ---------
// Modification Vente
// ---------

const useNameCategorie = 'libelle';

document.getElementById('input-image')?.addEventListener('change', function (e) {
    // Aperçu de l'image sélectionnée avant envoi du formulaire
    const fichier = e.target.files[0];
    if (!fichier) return;
    const apercu = document.getElementById('apercu-image');
    const placeholder = document.getElementById('placeholder-image');
    apercu.src = URL.createObjectURL(fichier);
    apercu.classList.remove('hidden');
    if (placeholder) placeholder.classList.add('hidden');
});

const modifierExiste = document.getElementById('vente-creer');
const searchExiste = document.getElementById('vente-index');
const consulterExiste = document.getElementById('consulter-vente');

// Si on est sur la page pour consulter une vente, activer le carousel d'images
if (consulterExiste) {
    const carousel = document.getElementById('carousel-images');

    if (carousel) {
        const boutonPrev = document.getElementById('carousel-prev');
        const boutonNext = document.getElementById('carousel-next');

        const defiler = (direction) => {
            const largeur = carousel.clientWidth;
            carousel.scrollBy({ left: direction * largeur, behavior: 'smooth' });
        };

        boutonPrev?.addEventListener('click', () => defiler(-1));
        boutonNext?.addEventListener('click', () => defiler(1));
    }
}

if (modifierExiste) {
    const categories = new Rechercher({
        nom: 'categorie',
        nom_elements: 'categorie',
        rechercherPar: 'text',
        useName: useNameCategorie,
        elements: (item) => {
            const span = document.createElement("span");
            span.classList = "gap-2 inline-flex  cursor-pointer items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase transition-transform duration-150";
            span.style.backgroundColor = "color-mix(in srgb, "+e(item.couleur)+" 15%, white";
            span.style.borderColor = e(item.couleur);
            span.style.color = e(item.couleur);
            span.setAttribute("id", "jsrecherche-categorie-line_"+e(item.id));
            span.textContent = " "+e(item[useNameCategorie])+" ";
            return span;
        }

    });
}
//
//  Rechercher Vente
//

if (searchExiste) {
    const categoriesInclus = new Rechercher({
            nom: 'categorie',
            nom_elements: 'recherche_categorie',
            rechercherPar: 'text',
            useName: useNameCategorie,

            elements: (item) => {
                const span = document.createElement("span");
                span.classList = "gap-2 inline-flex cursor-pointer items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase transition-transform duration-150";
                span.style.backgroundColor = "color-mix(in srgb, " + e(item.couleur) + " 15%, white)";
                span.style.borderColor = e(item.couleur);
                span.style.color = e(item.couleur);
                span.setAttribute("id", "jsrecherche-recherche_categorie-line_" + e(item.id));
                span.textContent = " " + e(item[useNameCategorie]) + " ";
                return span;
            }
        });

    document.getElementById('recherche-effacer')?.addEventListener('click', () => {
        document.getElementById('jsrecherche-recherche_categorie-inputs')?.replaceChildren();
        document.querySelectorAll('#jsrecherche-recherche_categorie-container span[id^="jsrecherche-recherche_categorie-line_"]')
            .forEach((el) => el.remove());
    });

    document.querySelectorAll('.jsrecherche-categorie-rapide').forEach((bouton) => {
        bouton.addEventListener('click', () => {
            const id = e(bouton.dataset.categorieId);
            if (id) categoriesInclus.ajouterParId?.(id);
        });
    });


    const vente_card = (item) => {

        const a = document.createElement("a");
        a.classList = "block bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-150 overflow-hidden group";
        a.href = "index.php?vente&action=view&id="+item.id;

        a.dataset.date = item.dateheure ?? "";

        const imageDiv = document.createElement("div");
        imageDiv.classList = "h-32 bg-slate-100 flex items-center justify-center text-xs text-slate-400 font-medium";

        let imageNom = null;
        if (Array.isArray(item.images) && item.images.length) {
            const principale = item.images.find((img) => String(img.id) === String(item.image_principale));
            imageNom = (principale ?? item.images[0]).image;
        }

        const img = document.createElement("img");
        img.setAttribute("alt", e(item.titre));
        img.classList = "w-full h-full object-cover" + (imageNom ? "" : " hidden");
        if (imageNom) img.src = "public/images/vente/"+e(imageNom);

        const emptyImage = document.createElement("div");
        emptyImage.classList = "w-full h-full flex items-center justify-center text-slate-300" + (imageNom ? " hidden" : "");
        const emptyImageSvg = document.createElementNS(svgNS, "svg");
        emptyImageSvg.classList = "w-16 h-16";
        emptyImageSvg.setAttribute("fill", "none");
        emptyImageSvg.setAttribute("stroke", "currentColor");
        emptyImageSvg.setAttribute("viewBox", "0 0 24 24");
        const emptyImageSvgPath = document.createElementNS(svgNS, "path");
        emptyImageSvgPath.setAttribute("stroke-linecap", "round");
        emptyImageSvgPath.setAttribute("stroke-linejoin", "round");
        emptyImageSvgPath.setAttribute("stroke-width", "1.5");
        emptyImageSvgPath.setAttribute("d", "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16v12H4V6z");
        emptyImageSvg.append(emptyImageSvgPath);
        emptyImage.append(emptyImageSvg);
        imageDiv.append(img, emptyImage);

        const detailsDiv = document.createElement("div");
        detailsDiv.classList = "p-4 space-y-2";

        const span = document.createElement("span");
        span.classList = "inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase";
        span.setAttribute("data-get-categorie", "");

        afficherCategorie(span, e(item.categorie));
        detailsDiv.append(span);

        const h4 = document.createElement("h4");
        h4.classList = "text-sm font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors duration-150";
        h4.textContent = e(item.titre);

        const infoDiv = document.createElement("div");
        infoDiv.classList = "flex items-center gap-2 text-xs";
        const prixSpan = document.createElement("span");
        prixSpan.classList = "font-bold text-slate-900";
        prixSpan.textContent = (item.prix_depart ?? 0) + " €";
        const etatSpan = document.createElement("span");
        etatSpan.classList = "text-slate-400";
        etatSpan.textContent = labelsEtat[item.etat_produit] ?? "";
        infoDiv.append(prixSpan, etatSpan);

        const tempsDiffDiv = document.createElement("div");
        tempsDiffDiv.classList = "flex items-center gap-3 text-xs text-slate-400 pt-1";
        const tempsSpan = document.createElement("span");
        tempsSpan.classList = "flex items-center gap-1";

        const tempsSvg = document.createElementNS(svgNS, "svg");
        tempsSvg.classList = "w-3.5 h-3.5";
        tempsSvg.setAttribute("fill", "none");
        tempsSvg.setAttribute("stroke", "currentColor");
        tempsSvg.setAttribute("viewBox", "0 0 24 24");
        const tempsSvgPath = document.createElementNS(svgNS, "path");
        tempsSvgPath.setAttribute("stroke-linecap", "round");
        tempsSvgPath.setAttribute("stroke-linejoin", "round");
        tempsSvgPath.setAttribute("stroke-width", "2");
        tempsSvgPath.setAttribute("d", "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z");
        tempsSvg.append(tempsSvgPath);
        tempsSpan.append(tempsSvg, tempsRestant(item.dateheure_fin) + " (fin)");

        const statutSpan = document.createElement("span");
        statutSpan.textContent = labelsStatut[item.status] ?? "";
        const statutIconSpan = document.createElement("span");
        statutIconSpan.classList = "text-slate-300";
        statutIconSpan.textContent = "•";
        tempsDiffDiv.append(tempsSpan, statutIconSpan, statutSpan);
        detailsDiv.append(h4, infoDiv, tempsDiffDiv);
        a.append(imageDiv, detailsDiv);

        return a;
    };
    const filtreEtat = document.getElementById('etat_produit');
    const filtreStatus = document.getElementById('status');
    const filtrePrixMin = document.getElementById('jsrecherche-prix-min');
    const filtrePrixMax = document.getElementById('jsrecherche-prix-max');
    const rechercheTexteInput = document.getElementById('jsrecherche-vente-input');

    const categoriesSelectionnees = () => Array.from(
        document.querySelectorAll('#jsrecherche-recherche_categorie-inputs input[data-id]')
    ).map((input) => input.dataset.id);

    const ventes = new Rechercher({
        nom: 'vente',
        nom_elements: 'vente',
        mode: 'card',
        rechercherPar: 'titre',
        resultInfo: true,
        elements: vente_card,
        extraParams: () => ({
            etat_produit: filtreEtat?.value,
            status: filtreStatus?.value,
            prix_min: filtrePrixMin?.value,
            prix_max: filtrePrixMax?.value,
            categorie: categoriesSelectionnees()
        })
    });
    let filtresTemporisateur = null;
    const lancerRechercheVentes = () => {
        clearTimeout(filtresTemporisateur);
        filtresTemporisateur = setTimeout(() => {
            ventes.rechercher(rechercheTexteInput?.value.trim() ?? '');
        }, 300);
    };

    [filtreEtat, filtreStatus].forEach((el) => el?.addEventListener('change', lancerRechercheVentes));
    [filtrePrixMin, filtrePrixMax].forEach((el) => el?.addEventListener('input', lancerRechercheVentes));

    const categorieInputsContainer = document.getElementById('jsrecherche-recherche_categorie-inputs');
    if (categorieInputsContainer) {
        new MutationObserver(lancerRechercheVentes).observe(categorieInputsContainer, { childList: true });
    }
    function trierVentes(container, critere) {
    // Fonction trierVentes
    // Role: Trier les ventes par Note, difficulte ou Date
        const cartes = Array.from(container.querySelectorAll("a[data-note], a[data-date], a[data-difficulte]"));

        const valeur = (carte) => {
            switch (critere) {
                case "note":
                    return parseFloat(carte.dataset.note) || 0;
                case "difficulte":
                    return parseFloat(carte.dataset.difficulte) || 0;
                case "date":
                    return new Date(carte.dataset.date).getTime() || 0;
                default:
                    return 0;
            }
        };
        cartes.sort((a, b) => {
            const diff = valeur(a) - valeur(b);
            return -diff;
        });
        cartes.forEach((carte) => container.append(carte));
    }
    const conteneurVentes = document.getElementById("jsrecherche-vente-container");

    document.querySelectorAll('input[name="tri"]').forEach((radio) => {
        
        radio.addEventListener("change", (e) => {
            if (!e.target.checked) return;
            trierVentes(conteneurVentes, e.target.value);
        });
    });
}
const containerEdit = document.getElementById("vente-creer");
if (containerEdit) {
    initImages();
}
initCategories();
const idUtilisateur = document.querySelector('[data-utilisateur-id]')?.dataset.utilisateurId || null;
initEncheres({ idUtilisateur });
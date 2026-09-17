//  categories.mjs
//    Role: Recuperer les categories necessaires via l'API por rechercher et/ou mettre à jour le texte des elements qui contient 
//
// Methodes: chargerCategories, initCategories
//
function construireURL(ids) {
// Fonction construireURL
// Role: Construit l'URL de l'API selon le nombre d'ids demandes
//       (id= pour un seul, ids= separes par virgule pour plusieurs)
//
// Params: ids - Tableau d'ids (string) uniques
// Retour: L'URL a fetch

    if (ids.length === 1) {
        return `index.php?categorie&action=view&id=${encodeURIComponent(ids[0])}`;
    }
    return `index.php?categorie&action=view&ids=${ids.map(encodeURIComponent).join(",")}`;
}

function creerChargement() {
// Fonction creerChargement
// Role: Cree un petit loader qui tourne (spinner), a placer a la place du texte
//
// Retour: L'element span du loader

    const span = document.createElement("span");
    span.className = "inline-block w-3 h-3 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin align-middle";
    span.setAttribute("role", "status");
    span.setAttribute("aria-label", "Chargement");
    return span;
}

const cache = new Map(); // cle = ids tries et joints par "," => Promise<Map id => libelle>

export function chargerCategories(ids) {
// Fonction chargerCategories
// Role: Recupere uniquement les categories demandees depuis l'API (id ou ids selon le nombre),
//       mise en cache par jeu d'ids pour eviter les doublons de requete
//
// Params:
//      ids - Tableau d'ids (string) a recuperer
// Retour:
//      Promise resolue avec une Map id (string) => libelle

    const uniques = [...new Set(ids)].sort();
    if (!uniques.length) return Promise.resolve(new Map());

    const cle = uniques.join(",");
    if (cache.has(cle)) return cache.get(cle);

    const promesse = fetch(construireURL(uniques), {
        headers: { "Accept": "application/json" }
    })
        .then((res) => {
            if (!res.ok) throw new Error("Erreur API");
            return res.json();
        })
        .then((data) => {
            
            if (data.erreur) {
                throw new Error("Erreur API: "+data.erreur);
            }
            const categories = {};
            for (const objet of data.objets ?? []) {
                categories[Number(objet.id)] = objet.libelle;
            }
            return categories;
        })
        .catch((err) => {
            console.error("Erreur API" +err);
            return {};
        });

    cache.set(cle, promesse);
    return promesse;
}
export function afficherCategorie(el, id) {
// Fonction afficherCategorie
// Role: Affiche un loader dans l'element, puis le remplace par le libelle
//       de la categorie une fois recuperee (utile pour les elements crees dynamiquement,
//       non couverts par initCategories())
//
// Params:
//      el - L'element (span, etc.) dans lequel afficher le libelle
//      id - L'id de la categorie a afficher
// Retour: Neant

    el.textContent = "";
    el.append(creerChargement());

    chargerCategories([String(id)]).then((categories) => {
        el.textContent = categories[id] ?? id;
    });
}
function rechercherCategories(texte) {
// Fonction rechercherCategories
// Role: Recupere les categories qui correspond a un texte
//
// Params:
//      texte - Le texte a rechercher
// Retour:
//      Promise resolue avec une Map id (string) => libelle

    const uniques = [...new Set(ids)].sort();
    if (!uniques.length) return Promise.resolve(new Map());

    const cle = uniques.join(",");
    if (cache.has(cle)) return cache.get(cle);

    const promesse = fetch(construireURL(uniques), {
        headers: { "Accept": "application/json" }
    })
        .then((res) => {
            if (!res.ok) throw new Error("Erreur API");
            return res.json();
        })
        .then((data) => {
            if (data.erreur) {
                throw new Error("Erreur API: "+data.erreur);
            }
            const categories = {};
            for (const objet of data.objets ?? []) {
                categories[Number(objet.id)] = objet.libelle;
            }
            return categories;
        })
        .catch((err) => {
            console.error("Erreur API" +err);
            return {};
        });

    cache.set(cle, promesse);
    return promesse;
}
export function initCategories() {
// Fonction initCategories
// Role: Recense les ids necessaires (elements simples + listes), affiche un loader,
//       recupere les categories en une seule requete puis met a jour le texte
//
// Params: Neant
// Note: Un element <option> ne peut pas contenir d'element HTML (donc pas de spinner dedans) -
//       le select est desactive et un texte "Chargement…" est affiche a la place

    const elementsSimples = document.querySelectorAll("[data-get-categorie]");
    const conteneursListes = document.querySelectorAll("[data-get-categories]");

    if (!elementsSimples.length && !conteneursListes.length) return;

    const idsOriginaux = new Map();
    elementsSimples.forEach((el) => {
        idsOriginaux.set(el, el.textContent.trim());
        el.textContent = "";
        el.append(creerChargement());
    });

    conteneursListes.forEach((conteneur) => {
        conteneur.disabled = true;
        conteneur.querySelectorAll("option").forEach((option) => {
            option.dataset.texteOriginal = option.textContent;
            option.textContent = "Chargement…";
        });
    });

    const idsRequis = new Set();
    idsOriginaux.forEach((id) => idsRequis.add(id));
    conteneursListes.forEach((conteneur) => {
        conteneur.querySelectorAll("option").forEach((option) => idsRequis.add(option.value.trim()));
    });

    chargerCategories([...idsRequis]).then((categories) => {
        elementsSimples.forEach((el) => {
            const id = idsOriginaux.get(el);
            el.textContent = categories[id] ?? id;
        });

        conteneursListes.forEach((conteneur) => {
            conteneur.querySelectorAll("option").forEach((option) => {
                const id = option.value.trim();
                option.textContent = categories[id] ?? option.dataset.texteOriginal ?? id;
                delete option.dataset.texteOriginal;
            });
            conteneur.disabled = false;
        });
    });
}

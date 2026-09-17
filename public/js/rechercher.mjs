//  Rechercher.mjs
//    Role: Enregistre un objet Rechercher qui gere la recherche d'un objet en AJAX
//
//    Note: Prefixer les elements avec jsrecherche-
//
// Methodes: creerSpanResultat, posicioner, rechercher, existe, ajouter, effacer
//    
import { e } from "./helpers.mjs";

const templateChargement = document.getElementById("jsrecherche-template-chargement");

export class Rechercher {

    constructor(options) {
        this.url = `?${options.nom}&action=search`;

        this.input = document.getElementById(`jsrecherche-${options.nom_elements}-input`);

        this.container = document.getElementById(`jsrecherche-${options.nom_elements}-container`);
        this.inputs = document.getElementById(`jsrecherche-${options.nom_elements}-inputs`);

        this.a = document.querySelectorAll(`.jsrecherche-${options.nom_elements}-line-a`);
        this.useName = options.useName;
        this.nom = options.nom;
        this.nom_elements = options.nom_elements;
        this.rechercherPar = options.rechercherPar;
        this.elements = options.elements;
        this.mode = options.mode ?? 'inline';

        if (this.mode === "inline") {
            this.resultats = document.getElementById(`jsrecherche-${options.nom_elements}-results`);
        } else {
            this.resultats = document.getElementById(`jsrecherche-${options.nom_elements}-container`);
        }
        this.resultInfo = options.resultInfo;

        if (this.resultInfo) {
            this.resultInfoDiv = document.getElementById(`jsrecherche-${options.nom_elements}-resultinfo`);
        }
        this.extraParams = options.extraParams ?? null;
        this.inputName = options.inputName ?? options.nom_elements;
        this.limiteRecherche = options.limiteRecherche ?? 6;
        this.rechercheDelai = options.debounceDelay ?? 500;

        this.rechercherTemporisateur = null;

        this.init();
    }

    init() {
        if (this.mode === "inline") this.resultats.classList.add("hidden");

        this.input.addEventListener("input", (e) => {

            const value = e.target.value.trim();

            clearTimeout(this.rechercherTemporisateur);
            if (this.mode === "inline") {
                
                this.resultats.innerHTML = "";

                if (value.length < 2) {
                    this.resultats.classList.add("hidden");
                    return;
                }
            }
            
            this.rechercherTemporisateur = setTimeout(() => {
                
                if (this.mode === "inline") this.posicioner();
                
                this.resultats.innerHTML = "";

                this.resultats.classList.remove("hidden");

                this.rechercher(value);
            }, this.rechercheDelai);
        });

        window.addEventListener("scroll", () => {
            if (this.mode === "inline") this.resultats.classList.add("hidden");
        });

        this.container.addEventListener("click", (e) => {
            const id = e.target.closest(".jsrecherche-effacer");
            if (!id) return;
            if (confirm("Effacer "+this.nom+"?")) {
                this.effacer(id);
            }
        });
    }
    creerChargement() {
    // Fonction creerChargement
    // Role: Cree un petit loader qui tourne (spinner), a placer a la place du texte
    //
    // Retour: L'element span du loader
        const div = document.createElement("div");
        div.classList = "inline-flex gap-3 px-1";
        const span = document.createElement("span");
        span.className = "inline-block w-3 h-3 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin align-middle";
        span.setAttribute("role", "status");
        span.setAttribute("aria-label", "Chargement");
        const spanText = document.createElement("span");
        spanText.textContent = "Recherche en cours...";
        div.append(span, spanText);
        return div;
    }
    creerSpanResultat(item) {

        const span = document.createElement("span");
        span.classList = "gap-2 inline-flex bg-white/50 cursor-pointer items-center px-2.5 py-1 rounded-full text-[11px] font-bold border tracking-wide uppercase transition-transform duration-150";
        span.style.backgroundColor = "color-mix(in srgb, "+item.couleur+" 15%, white";
        span.style.borderColor = item.couleur;
        span.style.color = item.couleur;
        span.setAttribute("id", "jsrecherche-categorie-line_"+item.id);
        span.textContent = " "+item[this.useName]+" ";
        return span;
    }
    posicioner() {
        const rect = this.input.getBoundingClientRect();

        this.resultats.style.top = `${rect.bottom}px`;
        this.resultats.style.left = `${rect.left}px`;
        this.resultats.style.width = `${rect.width}px`;
    }


    async rechercher(text) {
    // Fonction rechercher
    // Role: Effectuer la recheche sur l'API pour les resultats
    //
    // Params: texte a rechercher

        try {
            const formData = new URLSearchParams();
            formData.append(this.rechercherPar, text);
            if (typeof this.extraParams === "function") {
                const extra = this.extraParams() || {};
                for (const [cle, valeur] of Object.entries(extra)) {
                    if (valeur === undefined || valeur === null || valeur === "") continue;
                    if (Array.isArray(valeur)) {
                        valeur.forEach((item) => formData.append(`${cle}[]`, item));
                    } else {
                        formData.append(cle, valeur);
                    }
                }
            }

            const chargement = this.creerChargement();
            this.resultats.replaceChildren(chargement);
            const res = await fetch(this.url, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            });

            if (!res.ok) {
                throw new Error("API error");
            }

            const data = await res.json();

            this.resultats.innerHTML = "";
            if (!data.objets?.length) {
                this.resultats.innerHTML = 'Aucun resultat!';
                //this.resultats.classList.add("hidden");
                return;
            }
            
            if (typeof data.objets.message !== 'undefined') {
                if (!data.message.erreur == 1) {
                    this.resultats.innerHTML = 'Aucun resultat!';
                    //this.resultats.classList.add("hidden");
                    return;
                }
            }

            const fragment = document.createDocumentFragment();
            let count = 0;

            for (const item of data.objets.slice(0, this.limiteRecherche)) {

                if (this.mode === "inline" && this.existe(item.id)) {
                    continue;
                }
                if (this.mode === "inline") {
                    const li = document.createElement("li");
                    li.className = "result";
                    li.append(this.creerSpanResultat(item));
                    li.addEventListener("click", () => this.ajouter(item));
                    fragment.append(li);
                } else {
                    this.ajouter(item);
                }

                count++;
            }

            if (count === 0) {
                if (this.resultInfo) {
                    this.resultInfoDiv.textContent = `Aucun resultat trouvé pour le moment`;
                } else {
                    this.resultats.textContent = "Aucun resultat!";
                }
            } else {
                if (this.resultInfo) {
                    if (count > 1) {
                        this.resultInfoDiv.textContent = `${count} ${this.nom}s trouvées:`;
                    } else {
                        this.resultInfoDiv.textContent = `${count} ${this.nom} trouvée:`;
                    }
                }
                this.resultats.append(fragment);
            }

        } catch (err) {
            console.error(err);
        }
    }

    existe(id) {
        return this.inputs.querySelector(`[data-id="${CSS.escape(String(id))}"]`);
    }

    ajouter(item) {
    // Fonction ajouter
    // Role: Ajoute un item a la liste 
    //
    // Params: item a ajouter

        let elementContents = null;
        if (typeof this.elements === "function") {
            elementContents = this.elements(item);
        } else {
            elementContents = this.elements;
        }
        if (this.mode === "inline") {
            if (this.existe(item.id)) {
                return;
            }
            const boutonEffacer = document.createElement("a");
            boutonEffacer.textContent = "✕";
            boutonEffacer.className = "jsrecherche-effacer hover:opacity-60 cursor-pointer";
            boutonEffacer.dataset.id = e(item.id);

            elementContents.append(boutonEffacer);

            this.resultats.classList.add("hidden");
            this.input.value = "";
        }
        if (this.mode === "inline") {
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = e(this.inputName);
            hidden.value = e(item.id);
            hidden.dataset.id = e(item.id);
            this.inputs.append(hidden);
        }
        this.container.append(elementContents);
        
    }

    effacer(id) {
    // Fonction effacer
    // Role: Ajoute un item a la liste 
    //
    // Params: item a ajouter

        this.container.querySelector(`#jsrecherche-${this.nom_elements}-line_${CSS.escape(String(id))}`)?.remove();
        this.inputs.querySelector(`[data-id="${CSS.escape(String(id))}"]`)?.remove();
    }
}
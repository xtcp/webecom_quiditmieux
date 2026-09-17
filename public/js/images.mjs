//  images.mjs
//    Role: Traite l'action de modifier/ajouter/effacer des images d'un formulaire de vente, la selection de l'image principale
//       et la preparation des donnés des images à envoyer avec le formulaire (DataTransfer/$_FILES)
//
const formulaire = document.getElementById('form-modifier-vente');
const inputImages = document.getElementById('input-images');
const galerie = document.getElementById('galerie-images');
const donneesExistantes = document.getElementById('donnees-images-existantes');
const tpl = document.getElementById('tpl-carte-image');

let fichiersImages = []; // { id, fichier?, url, existant }
let idPrincipale = null;

function genererGalerie() {
// Rôle: Génére la galerie d'images (appelée au debut et au changement d'une image)

    galerie.innerHTML = "";
    const inputImagePrincipal = document.getElementById('image_principale');

    fichiersImages.forEach((item) => {
        const noeud = tpl.content.cloneNode(true);
        const carte = noeud.querySelector('.carte');
        const img = noeud.querySelector('img');
        const badge = noeud.querySelector('.badge');
        const supprimer = noeud.querySelector('.supprimer');

        img.src = item.url;
        carte.classList.toggle('border-indigo-500', item.id === idPrincipale);
        carte.classList.toggle('border-slate-200', item.id !== idPrincipale);
        badge.classList.toggle('hidden', item.id !== idPrincipale);

        carte.addEventListener('click', () => {
            idPrincipale = item.id;
            inputImagePrincipal.value = item.id
            genererGalerie();
        });

        supprimer.addEventListener('click', (evt) => {
            evt.stopPropagation();
            fichiersImages = fichiersImages.filter((f) => f.id !== item.id);
            if (idPrincipale === item.id) {
                idPrincipale = fichiersImages[0]?.id ?? null;
            }
            genererGalerie();
        });

        galerie.append(noeud);
    });
    
    synchroniserInputFichiers();
}
function synchroniserInputFichiers() {
// Rôle: Corriger les problèmes du upload d'image classique (même en multiple)
//  Explication:
//      L'upload d'images standard permet d'uploader plusieurs images mais si l'utilisateur re-selectione des images,
//          les anciennes images son't ecrasés par les nouvelles, avec cette methode, on utilise DataTransfer pour
//          recréer le FileList de l'input avec les images[] en permettant un utilisateur d'uploader les images une par
//          une et effacer une image specifique

    const dt = new DataTransfer();
    fichiersImages
        .filter((item) => !item.existant && item.fichier)
        .forEach((item) => dt.items.add(item.fichier));
    inputImages.files = dt.files;
}

export function initImages() {
    if (donneesExistantes) {
        try {
            const images = JSON.parse(donneesExistantes.textContent || "[]");
            for (const image of images) {
                fichiersImages.push({
                    id: image.id,       // id réel en base, pas un uuid local
                    url: image.url,
                    existant: true
                });
                if (image.principale) {
                    idPrincipale = image.id;
                }
            }
        } catch (erreur) {
            console.error("Impossible de charger les images existantes", erreur);
        }
    }
    if (idPrincipale === null && fichiersImages.length) {
        idPrincipale = fichiersImages[0].id;
    }
    genererGalerie();

    inputImages?.addEventListener('change', (evt) => {
        for (const fichier of evt.target.files) {
            fichiersImages.push({
                id: crypto.randomUUID(),
                fichier,
                url: URL.createObjectURL(fichier),
                existant: false
            });
        }
        if (idPrincipale === null && fichiersImages.length) {
            idPrincipale = fichiersImages[0].id;
        }
        inputImages.value = "";
        genererGalerie();
    });

    if (formulaire) {
        const type = formulaire.getAttribute("data-type");
        
        if (type === "edit") {
            formulaire.addEventListener('submit', () => {
                const form = document.getElementById('form-modifier-vente');
                fichiersImages
                    .filter((item) => item.existant)
                    .forEach((item) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'images_conservees[]';
                        input.value = item.id;
                        form.appendChild(input);
                    });
            });
        }
    }
}
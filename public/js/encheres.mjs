//  encheres.mjs
//    Role: Rafraîchit périodiquement, par les IDs, les cartes des sections
//      "Enchères en cours" et "Mes Enchères en cours" du tableau de bord
//          (met à jour uniquement le prix et le statut de chaque carte, sans tout reconstruire)
//
// Methodes: initEncheres

const REFRESH_TOUTES = 10000; // "Enchères en cours" (toutes) - 10s
const REFRESH_MIENNES = 3000;  // "Mes Enchères en cours" - 3s

function derniereEnchere(vente) {
// Role: Retrouve l'enchère la plus élevée (objet complet, pas juste le prix) pour connaître
//       à la fois son montant et qui l'a placée
    let plusHaute = null;
    for (const enchere of vente.enchere ?? []) {
        if (plusHaute === null || Number(enchere.prix) > Number(plusHaute.prix)) {
            plusHaute = enchere;
        }
    }
    return plusHaute;
}

function appliquerMiseAJour(vente, idUtilisateur) {
    const spanPrix = document.getElementById(`enchere-${vente.id}`);
    if (spanPrix) {
        const derniere = derniereEnchere(vente);
        const prix = derniere?.prix ?? vente.prix_depart ?? 0;
        spanPrix.textContent = `${prix} €`;

        const estMonEnchere = idUtilisateur && derniere && String(derniere.utilisateur?.id) === String(idUtilisateur);
        spanPrix.classList.toggle("text-green-600", !!estMonEnchere);
        spanPrix.classList.toggle("text-amber-500", !estMonEnchere);
    }

    const divStatut = document.getElementById(`statut-${vente.id}`);
    if (divStatut) {
        const nouveauTexte = Number(vente.status) > 1 ? "Terminée" : "En cours";
        if (divStatut.textContent.trim() !== nouveauTexte) {
            divStatut.textContent = nouveauTexte;
        }
    }
}

async function rafraichir(ids, idUtilisateur) {
    if (!ids) return;

    try {
        const formData = new URLSearchParams();
        formData.append("ids", ids);

        const res = await fetch("?vente&action=search", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData
        });

        if (!res.ok) throw new Error("Erreur API vente");

        const data = await res.json();
        for (const vente of data.objets ?? []) {
            appliquerMiseAJour(vente, idUtilisateur);
        }

    } catch (err) {
        console.error(err);
    }
}

export function initEncheres() {
// Role: Démarre le rafraîchissement périodique des deux sections d'enchères du tableau de
//       bord, à partir des listes d'ids déjà rendues côté serveur dans des inputs cachés.

    const inputToutes = document.getElementById("ids-encheres-en-cours");
    const inputMiennes = document.getElementById("ids-mes-encheres-en-cours");
    const idUtilisateur = document.getElementById("utilisateur-connecte-id")?.value || null;

    if (inputToutes && inputToutes.value) {
        const idsToutes = inputToutes.value;
        rafraichir(idsToutes, idUtilisateur);
        setInterval(() => rafraichir(idsToutes, idUtilisateur), REFRESH_TOUTES);
    }

    if (inputMiennes && inputMiennes.value) {
        const idsMiennes = inputMiennes.value;
        rafraichir(idsMiennes, idUtilisateur);
        setInterval(() => rafraichir(idsMiennes, idUtilisateur), REFRESH_MIENNES);
    }
}
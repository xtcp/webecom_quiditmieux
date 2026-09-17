//  helpers.mjs
//    Role: Fonctions generiques partagées par les modules
//

export function e(value) {
    // Fonction e
    // Role: Equivalent de htmlentities de php
    //

    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
};


function pluriel(valeur, mot) {
// Fonction pluriel
// Role: Accorde un mot au pluriel si besoin (regle simple, sans exceptions)
//
// Params: valeur - Le nombre associe au mot
//         mot - Le mot au singulier
// Retour: Le mot accorde

    return valeur > 1 ? mot + "s" : mot;
}

export function tempsRestant(dateheureStr) {
// Fonction tempsRestant
// Role: Calcule le temps restant entre maintenant et une date de fin,
//       et le formate en texte lisible (jours/heures, ou heures/minutes)
//
// Params: dateheureStr - Date au format "YYYY-MM-DD HH:MM:SS"
// Retour: string - Le temps restant formate (ex: "23 jours, 3 heures"),
//         ou "Terminé" si la date est passee

    // Safari/certains navigateurs n'acceptent pas "YYYY-MM-DD HH:MM:SS" tel quel
    const dateFin = new Date(dateheureStr.replace(" ", "T"));
    const diffMs = dateFin.getTime() - Date.now();

    if (diffMs <= 0) return "Terminé";

    const diffSecondes = Math.floor(diffMs / 1000);

    const jours = Math.floor(diffSecondes / 86400);
    const heures = Math.floor((diffSecondes % 86400) / 3600);
    const minutes = Math.floor((diffSecondes % 3600) / 60);

    const parties = [];

    if (jours > 0) {
        parties.push(`${jours} ${pluriel(jours, "jour")}`);
        if (heures > 0) parties.push(`${heures} ${pluriel(heures, "heure")}`);
    } else if (heures > 0) {
        parties.push(`${heures} ${pluriel(heures, "heure")}`);
        if (minutes > 0) parties.push(`${minutes} ${pluriel(minutes, "minute")}`);
    } else {
        const minutesAffichees = Math.max(1, minutes);
        parties.push(`${minutesAffichees} ${pluriel(minutesAffichees, "minute")}`);
    }

    return parties.join(", ");
}

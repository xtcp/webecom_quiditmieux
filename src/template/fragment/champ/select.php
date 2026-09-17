<?php
// Template d'un select html

/**
 * @var string $cle - Nom du champ
 * @var string $valeurActuelle - Valleur actuelle du champ
 * @var array $champ - Details du champ input
 */

?>

<div class="custom-select-container inline-block">
    <select id="<?= e($cle) ?>" name="<?= e($cle) ?>" class="pill-select"> 
        <option value="">Tous</option>
        <?php foreach ($champ['options'] as $option): ?>
            <?php 
            $nomAffichage = isset($option->prenom) ? ($option->prenom . " " . $option->nom) : $option->nom;
            $selected = ($valeurActuelle == $option->id) ? 'selected' : '';
            ?>
            <option value="<?= e($option->id) ?>" <?= e($selected) ?>><?= e($nomAffichage) ?></option>
        <?php endforeach; ?>
    </select>
</div>
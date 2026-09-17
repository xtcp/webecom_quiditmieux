<?php
// Template d'un input html

/**
 * @var string $cle - Nom du champ
 * @var string $valeurActuelle - Valleur actuelle du champ
 * @var array $champ - Details du champ input
 */

?>
<?php if ($champ['champ_input_type'] === 'text' || $champ['champ_input_type'] === 'number' || $champ['champ_input_type'] === 'email'): ?>
    <input id="<?= e($cle) ?>" name="<?= e($cle) ?>" type="text" value="<?= e($valeurActuelle) ?>">
<?php elseif ($champ['champ_input_type'] === 'date'): ?>
    <input id="<?= e($cle) ?>" name="<?= e($cle) ?>" class="pill-date" type="date" min="" max="" value="<?= e($valeurActuelle) ?>">
<?php elseif ($champ['champ_input_type'] === 'checkbox'): ?>
    <label class="checkbox-container">
        <input type="checkbox" class="pill-checkbox" id="remember-me">
        <span class="checkmark"></span>
        <span class="text-gray-600 text-sm font-medium"><?= e($valeurActuelle) ?></span>
    </label>
<?php endif; ?>
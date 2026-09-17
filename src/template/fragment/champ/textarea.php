<?php
// Template d'un textarea html

/**
 * @var string $cle - Nom du champ
 * @var string $valeurActuelle - Valleur actuelle du champ
 */

?>
<textarea id="<?= e($cle) ?>" name="<?= e($cle) ?>" type="text"><?= e($valeurActuelle) ?></textarea>

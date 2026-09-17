<?php
// Template pour afficher un message
/**
 * @var string $message - Le texte du message
 * @var string $couleur - La couleur du message
 * @var bool $showButton - Afficher ou pas le bouton de retour à l'index
 */

?>
<div class="mt-4 w-full">
    <div class="flex flex-col gap-4 border-solid border-2 rounded-sm border-<?= e($couleur); ?>-400 bg-<?= e($couleur); ?>-200 w-[max-content] p-4">

        <div><?= e($message); ?></div>
        <?php if ($showButton): ?>
        <div><a href="index.php"><button class="button">Retour à l'accueil</button></div></a>
        <?php endif; ?>
    </div>
</div>
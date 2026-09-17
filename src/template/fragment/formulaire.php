<?php
// Template de formulaire générique pour rechercher ou ajouter un modéle

/**
 * @var array $optionsFormulaire - Tableau avec les options du formulaire comme nom, type
 * @var array $champsRecherche - Tableau avec les données des champs du formulaire
 */

    // Grouper les champs avec la meme 'ligne'
    $lignesGroupes = [];

    foreach ($champsRecherche as $nomChamp => $data) {
        if (isset($data['ligne'])) {
            $lignesGroupes[$data['ligne']][$nomChamp] = array_merge(['name' => $nomChamp], $data);
        } else {
            $lignesGroupes[$nomChamp][$nomChamp] = $data;
        }
    }
?>

<div class="flex-1">
    <h2>Rechercher <?= e(ucfirst($optionsFormulaire["nom"])) ?></h2>
    <div class="border-solid border-2 rounded-sm border-gray-400 p-4">
        <form id="rechercher" class="column mt-5" action="index.php?<?= e($optionsFormulaire["nom"]) ?>&action=search" method="post">

            <?php foreach ($lignesGroupes as $cleLigne => $champsDeLaLigne): ?>
                <?php
                    $premier_element = current($champsDeLaLigne);
                    $hidden = isset($premier_element['champ_hidden']) ? $premier_element['champ_hidden'] : false;
                    if (!$hidden) $hidden = (isset($premier_element['champ_input_type']) && $premier_element['champ_input_type'] == 'hidden') ? true : false;
                ?>
                <?php if ($hidden == true || $hidden == 1): ?>
                <?php else: ?>
                    <div class="search-line">
                        <?php if (isset($champsDeLaLigne[0]['champ_label']) && $champsDeLaLigne[0]['champ_label'] !== ""): ?>
                            <div class="search-title">
                                <?= e($champsDeLaLigne[0]['champ_label']) ?>
                            </div>
                            <div class="search-fields">
                                <?php foreach ($champsDeLaLigne as $cle => $champ): ?>
                                    <?php $valeurActuelle = $champsActuelles[$cle] ?? ""; ?>
                                    <?php if (isset($champ['champ_prefix'])): ?>
                                        <span><?= e($champ['champ_prefix']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($champ['champ_form_type'] === 'input'): ?>
                                    <?php include(__DIR__ . '/champ/input.php'); ?>
                                    <?php elseif ($champ['champ_form_type'] === 'select'): ?>
                                        <?php include(__DIR__ . '/champ/select.php'); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                           <div class="search-title">
                                <?= e(($premier_element['champ_nom'] ?? "")) ?>
                            </div>
                            <div class="search-fields">

                                <?php foreach ($champsDeLaLigne as $cle => $champ): ?>
                                        <?php
                                            $valeurActuelle = $champsActuelles[$cle] ?? "";
                                            if (!isset($champ['champ_form_type'])) {
                                                $champ['champ_form_type'] = 'input';
                                            } 
                                        ?>
                                        <?php if (isset($champ['prefix'])): ?>
                                            <span><?= e($champ['prefix']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($champ['champ_form_type'] === 'input'): ?>
                                            <?php include(__DIR__ . '/champ/input.php'); ?>
                                        <?php elseif ($champ['champ_form_type'] === 'select'): ?>
                                            <?php include(__DIR__ . '/champ/select.php'); ?>
                                        <?php elseif ($champ['champ_form_type'] === 'textarea'): ?>
                                            <?php include(__DIR__ . '/champ/textarea.php'); ?>
                                        <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="mt-6">
                <?php if (isset($optionsFormulaire["type"]) && $optionsFormulaire["type"] == "search"): ?>
                    <input id="searchButton" class="button" type="submit" value="Rechercher">
                <?php elseif (isset($optionsFormulaire["type"]) && $optionsFormulaire["type"] == "add"): ?> 
                    <input id="searchButton" class="button" type="submit" value="Ajouter">
                <?php else: ?>
                    <input id="searchButton" class="button" type="submit" value="Rechercher">
                <?php endif ?>
            </div>
        </form>
    </div>
</div>
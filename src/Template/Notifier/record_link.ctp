<?php
list($plugin, $controller) = pluginSplit($registryAlias);

$url = $this->Html->link($recordName, [
    'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $recordId, '_full' => true
]);
?>
<?= $modelName ?> record <?= $url ?> has been assinged to you via '<?= $field ?>' field.
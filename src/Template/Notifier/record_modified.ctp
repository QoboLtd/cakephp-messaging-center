<?php
use Cake\Utility\Inflector;

list($plugin, $controller) = pluginSplit($registryAlias);

$url = $this->Html->link($recordName ?: 'record', [
    'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $recordId, '_full' => true
]);
?>
<?= $modelName ?> <?= $url ?> has been modified.

<?php foreach ($modifiedFields as $k => $v) : ?>
    <?= '* <strong>'  . Inflector::humanize($k) . '</strong>: changed from \'' . h($v['oldValue']) . '\' to \'' . h($v['newValue']) . '\'.' . "\n" ?>
<?php endforeach; ?>

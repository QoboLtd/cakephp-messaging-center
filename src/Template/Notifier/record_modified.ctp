<?php
use Cake\Utility\Inflector;

list($plugin, $controller) = pluginSplit($registryAlias);

$url = $this->Html->link($recordName ?: 'record', [
    'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $recordId, '_full' => true
]);
$text = '* <strong>%s</strong>: changed from \'%s\' to \'%s\'.';
?>
<?= $modelName ?> <?= $url ?> has been modified.

<?php foreach ($modifiedFields as $k => $v) {
    if (is_array($v['oldValue'])) {
        $text = '* <strong>%s</strong>: were modified.';
        $v['oldValue'] = null;
        $v['newValue'] = null;
    }
    echo sprintf($text, Inflector::humanize($k), h($v['oldValue']), h($v['newValue'])) . "\n";
} ?>
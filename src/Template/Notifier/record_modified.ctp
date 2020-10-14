<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Utility\Inflector;

list($plugin, $controller) = pluginSplit($registryAlias);

$url = $this->Html->link($recordName ?: 'record', [
    'plugin' => $plugin,
    'controller' => $controller,
    'action' => 'view',
    $recordId,
    'prefix' => false,
    '_full' => true
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

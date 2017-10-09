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

list($plugin, $controller) = pluginSplit($registryAlias);

$url = $this->Html->link($recordName, [
    'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $recordId, '_full' => true
]);
?>
<?= $modelName ?> record <?= $url ?> has been assinged to you via '<?= $field ?>' field.

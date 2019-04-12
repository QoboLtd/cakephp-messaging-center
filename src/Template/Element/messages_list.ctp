<div class="box box-primary">
    <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
        <thead>
            <tr>
                <th><?= __('Subject') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Action') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($folder->get('messages') as $message) : ?>
            <tr>
                <td><?= $message->get('subject') ?></td>
                <td><?= $message->get('status') ?></td>
                <td><?= $message->get('created'); ?></td>
                <td><?= $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'Messages', 'action' => 'view', $message->get('id')], ['escape' => false, 'class' => 'btn btn-default', 'title' => __('View')]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

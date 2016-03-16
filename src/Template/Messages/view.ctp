<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <!-- Panel header -->
            <div class="panel-heading">
                <h3 class="panel-title"><?= h($message->subject) ?></h3>
            </div>
            <table class="table table-striped" cellpadding="0" cellspacing="0">
                <tr>
                    <td><?= __('From') ?></td>
                    <td><?= $message->has('user') ? $this->Html->link($message->user->username, ['controller' => 'Users', 'action' => 'view', $message->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <td><?= __('To') ?></td>
                    <td><?= h($message->to_user) ?></td>
                </tr>
                <tr>
                    <td><?= __('Subject') ?></td>
                    <td><?= h($message->subject) ?></td>
                </tr>
                <tr>
                    <td><?= __('Related Model') ?></td>
                    <td><?= h($message->related_model) ?></td>
                </tr>
                <tr>
                    <td><?= __('Related Id') ?></td>
                    <td><?= h($message->related_id) ?></td>
                </tr>
                <tr>
                    <td><?= __('Date Sent') ?></td>
                    <td><?= h($message->date_sent) ?></td>
                </tr>
                <tr>
                    <td><?= __('Created') ?></td>
                    <td><?= h($message->created) ?></td>
                </tr>
                <tr>
                    <td><?= __('Modified') ?></td>
                    <td><?= h($message->modified) ?></td>
                </tr>
                <tr>
                    <td><?= __('Content') ?></td>
                    <td><?= $this->Text->autoParagraph(h($message->content)); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>


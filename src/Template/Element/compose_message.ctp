<?= $this->Html->link(
    '<i class="fa fa-pencil" aria-hidden="true"></i> ' . __d('Qobo/MessagingCenter', 'Compose'),
    ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'compose', $mailbox->get('id')],
    ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
); ?>

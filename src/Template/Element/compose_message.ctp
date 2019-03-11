<?= $this->Html->link(
    '<i class="fa fa-pencil" aria-hidden="true"></i> ' . __('Compose'),
    ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'compose'],
    ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
); ?>

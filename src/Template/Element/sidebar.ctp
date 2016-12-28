<?php echo $this->Html->link(
    '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ' . __('Compose'),
    ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'compose'],
    ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
); ?>
<?= $this->element('MessagingCenter.folders_list');

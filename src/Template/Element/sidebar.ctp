<?php echo $this->Html->link(
    __('Compose'),
    ['action' => 'create'],
    ['class' => 'btn btn-primary btn-block']
); ?>
<?= $this->element('MessagingCenter.folders_list') ?>
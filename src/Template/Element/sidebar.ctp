<?php echo $this->Html->link(
    __('Compose'),
    ['action' => 'compose'],
    ['class' => 'btn btn-primary btn-block']
); ?>
<?= $this->element('MessagingCenter.folders_list') ?>
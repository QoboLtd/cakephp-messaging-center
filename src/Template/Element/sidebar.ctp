<?php echo $this->Html->link(
    '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ' . __('Compose'),
    ['action' => 'compose'],
    ['class' => 'btn btn-primary btn-block', 'escape' => false]
); ?>
<?= $this->element('MessagingCenter.folders_list');

<?php
echo $this->Html->css('MessagingCenter.style');
// enable typeahead library
echo $this->Html->script('MessagingCenter.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->script('MessagingCenter.typeahead', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-xs-12 col-md-4 col-lg-3">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-12 col-md-8 col-lg-9">
        <?= $this->Form->create($message, ['align' => [
            'xs' => [
                'left' => 2,
                'middle' => 10
            ]
        ]]); ?>
        <fieldset>
            <legend><?= __('Compose New {0}', ['Message']) ?></legend>
            <?php
            echo $this->Form->input('to_user', [
                'label' => 'To',
                'name' => 'to_user_label',
                'id' => 'to_user_label',
                'type' => 'text',
                'data-type' => 'typeahead',
                'data-name' => 'to_user',
                'autocomplete' => 'off',
                'data-url' => '/api/users/lookup.json'
            ]);
            echo $this->Form->input('to_user', ['type' => 'hidden']);
            echo $this->Form->input('subject');
            echo $this->Form->input('content', ['label' => '']);
            ?>
        </fieldset>
        <?= $this->Form->button(__("Send"), ['class' => 'btn btn-primary pull-right']); ?>
        <?= $this->Form->end() ?>
    </div>
</div>
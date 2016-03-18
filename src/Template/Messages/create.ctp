<?php
echo $this->Html->css('MessagingCenter.style');
?>

<div class="row">
    <div class="col-xs-2">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-10">
        <div class="row">
            <div class="col-xs-9">
                <?= $this->Form->create($message, ['align' => [
                    'xs' => [
                        'left' => 1,
                        'middle' => 11
                    ]
                ]]); ?>
                <fieldset>
                    <legend><?= __('Compose New {0}', ['Message']) ?></legend>
                    <?php
                    echo $this->Form->input('to_user', ['options' => $users, 'label' => 'to']);
                    echo $this->Form->input('subject');
                    echo $this->Form->input('content', ['label' => '']);
                    ?>
                </fieldset>
                <?= $this->Form->button(__("Send"), ['class' => 'pull-right']); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
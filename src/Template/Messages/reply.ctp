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
                    <legend><?= __('Reply To > Re: {0}', [$message->subject]) ?></legend>
                    <?php
                    echo $this->Form->input('to_user_label', [
                        'value' => $message->fromUser->username,
                        'label' => 'to',
                        'readonly' => true
                    ]);
                    echo $this->Form->input('to_user', [
                        'value' => $message->fromUser->id,
                        'type' => 'hidden',
                        'readonly' => true
                    ]);
                    echo $this->Form->input('subject', ['value' => 'Re: ' . $message->subject]);
                    echo $this->Form->input('content', ['value' => '','label' => '']);
                    ?>
                </fieldset>
                <?= $this->Form->button(__("Send"), ['class' => 'pull-right']); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
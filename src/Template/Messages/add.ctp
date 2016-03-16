<div class="row">
    <div class="col-xs-12">
        <?= $this->Form->create($message); ?>
        <fieldset>
            <legend><?= __('New {0}', ['Message']) ?></legend>
            <?php
            echo $this->Form->input('to_user', ['options' => $users]);
            echo $this->Form->input('subject');
            echo $this->Form->input('content');
            ?>
        </fieldset>
        <?= $this->Form->button(__("Send")); ?>
        <?= $this->Form->end() ?>
    </div>
</div>

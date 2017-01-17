<!-- inner menu: contains the actual data -->
<ul class="menu">
    <?php foreach ($messages as $message) : ?>
        <li><!-- start message -->
            <a href="<?= $this->Url->build([
                'plugin' => 'MessagingCenter',
                'controller' => 'Messages',
                'action' => 'view',
                $message->id
            ]); ?>">
                <div class="pull-left">
                    <?php echo $this->Html->image('user-image-160x160.png', [
                        'class' => 'img-circle',
                        'alt' => 'User Image'
                    ]); ?>
                </div>
                <h4>
                    <?= $this->element('MessagingCenter.user', [
                        'user' => !empty($message->fromUser) ? $message->fromUser : $message->from_user
                    ]) ?>
                    <small><i class="fa fa-clock-o"></i> <?= $this->Time->timeAgoInWords(h($message->date_sent->i18nFormat('yyyy-MM-dd HH:mm'))) ?></small>
                </h4>
                <p>
                    <?= $this->Text->truncate(
                        $this->Text->stripLinks($message->content),
                        (int)$contentLength,
                        ['ellipsis' => '...', 'exact' => true, 'html' => true]
                    ); ?>
                </p>
            </a>
        </li>
    <!-- end message -->
    <?php endforeach; ?>
</ul>
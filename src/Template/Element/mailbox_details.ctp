<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Mailbox Details</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        </div>
    </div>
    <div class="box-body">
        <ul class="nav nav-pills nav-stacked">
            <li><b>Name: </b><?= $mailbox->get('name') ?></li>
            <li><b>Type: </b><?= $mailbox->get('type') ?></li>
            <li><b>Active: </b><?= $mailbox->get('active') ?></li>
            <li><b>Created: </b><?= $mailbox->get('created') ?></li>
        </ul>
    </div>
</div>

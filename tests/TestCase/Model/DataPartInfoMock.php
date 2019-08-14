<?php

namespace MessagingCenter\Test\TestCase\Model;

use PhpImap\DataPartInfo;

class DataPartInfoMock extends DataPartInfo
{
    public function __construct()
    {
        parent::__construct(null, null, null, null, null);
    }

    public function fetch()
    {
        return 'DataPartInfo';
    }
}

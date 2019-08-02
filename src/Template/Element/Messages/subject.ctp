<?php

$messageSubject = empty($message->get('subject')) ? '(no subject)' : $message->get('subject');

echo h($messageSubject);

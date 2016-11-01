<?php
namespace Foobar\Test\App\Controller;

use \Cake\Controller\Controller;

class AppController extends Controller
{
    public $components = ['Auth', 'Flash', 'RequestHandler'];
}

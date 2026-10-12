<?php
namespace App\Modules\Clinical\Integration\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class BpjsController extends Controller {
    public function __construct() { parent::__construct(); }
    public function index(): Response { return Inertia::render('Clinical/Integration/presentations/views/BpjsIndex'); }
}
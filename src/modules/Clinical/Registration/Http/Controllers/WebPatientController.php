<?php
namespace App\Modules\Clinical\Registration\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class WebPatientController extends Controller {
    public function __construct() { parent::__construct(); }
    public function index(Request $r): Response { return Inertia::render('Clinical/Registration/presentations/views/PatientListIndex',['filters'=>['search'=>(string)$r->string('search')->trim(),'page'=>$r->integer('page',1),'per_page'=>$r->integer('per_page',10)]]); }
}
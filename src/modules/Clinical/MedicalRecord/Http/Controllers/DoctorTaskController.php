<?php
namespace App\Modules\Clinical\MedicalRecord\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class DoctorTaskController extends Controller {
    public function __construct() { parent::__construct(); }
    public function index(Request $r): Response { return Inertia::render('Clinical/MedicalRecord/presentations/views/DoctorTaskIndex',['filters'=>['search'=>(string)$r->string('search')->trim(),'page'=>$r->integer('page',1),'per_page'=>$r->integer('per_page',10)]]); }
}
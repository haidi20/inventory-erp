<?php
namespace App\Modules\Clinical\MedicalRecord\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class RadiologyController extends Controller {
    public function __construct() { parent::__construct(); }
    private function f(Request $r): array { return ['search'=>(string)$r->string('search')->trim(),'page'=>$r->integer('page',1),'per_page'=>$r->integer('per_page',10)]; }
    public function index(Request $r): Response { return Inertia::render('Clinical/MedicalRecord/presentations/views/RadiologyIndex',['filters'=>$this->f($r)]); }
    public function show(string $uuid): Response { return Inertia::render('Clinical/MedicalRecord/presentations/views/RadiologyIndex',['uuid'=>$uuid,'mode'=>'detail']); }
    public function store(Request $r) { return back()->with('success','Radiologi order disimpan.'); }
    public function inputHasil(Request $r, string $uuid) { return back()->with('success','Hasil radiologi disimpan.'); }
    public function updateStatus(Request $r, string $uuid) { return back()->with('success','Status diperbarui.'); }
    public function destroy(string $uuid) { return back()->with('success','Radiologi order dihapus.'); }
}
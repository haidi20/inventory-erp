<?php
namespace App\Modules\Clinical\Registration\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class PendaftaranController extends Controller {
    public function __construct() { parent::__construct(); }
    private function f(Request $r, array $e=[]): array { return array_merge(['search'=>(string)$r->string('search')->trim(),'page'=>$r->integer('page',1),'per_page'=>$r->integer('per_page',10),'date_from'=>(string)$r->string('date_from')->trim(),'date_to'=>(string)$r->string('date_to')->trim()],$e); }
    public function index(Request $r): Response { return Inertia::render('Clinical/Registration/presentations/views/RegistrationIndex',['filters'=>$this->f($r)]); }
    public function patientList(Request $r): Response { return Inertia::render('Clinical/Registration/presentations/views/PatientListIndex',['filters'=>$this->f($r)]); }
    public function patientShow(string $uuid): Response { return Inertia::render('Clinical/Registration/presentations/views/PatientShowIndex',['uuid'=>$uuid]); }
    public function store(Request $r) { return back()->with('success','Registrasi berhasil.'); }
    public function updateStatus(Request $r, $uuid) { return back()->with('success','Status diperbarui.'); }
    public function destroy($uuid) { return back()->with('success','Registrasi dihapus.'); }
}
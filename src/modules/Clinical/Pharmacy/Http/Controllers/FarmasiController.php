<?php
namespace App\Modules\Clinical\Pharmacy\Http\Controllers;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
class FarmasiController extends Controller {
    public function __construct() { parent::__construct(); }
    private function f(Request $r): array { return ['search'=>(string)$r->string('search')->trim(),'page'=>$r->integer('page',1),'per_page'=>$r->integer('per_page',10)]; }
    public function index(Request $r): Response         { return Inertia::render('Clinical/Pharmacy/presentations/views/PharmacyIndex',['filters'=>$this->f($r)]); }
    public function prescriptions(Request $r): Response { return Inertia::render('Clinical/Pharmacy/presentations/views/PrescriptionIndex',['filters'=>$this->f($r)]); }
    public function returns(Request $r): Response       { return Inertia::render('Clinical/Pharmacy/presentations/views/PharmacyReturnIndex',['filters'=>$this->f($r)]); }
    public function stockDetail(Request $r, string $uuid): Response { return Inertia::render('Clinical/Pharmacy/presentations/views/PharmacyIndex',['uuid'=>$uuid,'mode'=>'stock']); }
    public function storeResep(Request $r)  { return back()->with('success','Resep disimpan.'); }
    public function storeRetur(Request $r)  { return back()->with('success','Retur disimpan.'); }
}
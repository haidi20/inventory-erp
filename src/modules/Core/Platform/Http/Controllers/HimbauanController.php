<?php

namespace App\Modules\Core\Platform\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HimbauanController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): Response
    {
        return Inertia::render('Core/Platform/presentations/views/AnnouncementsIndex');
    }
}

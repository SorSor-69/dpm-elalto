<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index()
    {
        return view('asistencias.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    private function datos(Request $r, ReporteService $service)
    {
        $r->merge(['desde' => $r->input('desde') ?: now('America/Lima')->startOfMonth()->toDateString(), 'hasta' => $r->input('hasta') ?: now('America/Lima')->toDateString()]);
        $data = $r->validate(['desde' => 'required|date_format:Y-m-d', 'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde']);

        return $service->generar($data['desde'], $data['hasta']);
    }

    public function index(Request $r, ReporteService $service)
    {
        return view('reportes.index', $this->datos($r, $service));
    }

    public function pdf(Request $r, ReporteService $service)
    {
        return Pdf::loadView('pdf.reporte', $this->datos($r, $service))->setPaper('a4', 'landscape')->download('reporte-aks.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\FuncionarioInspeccion;
use App\Models\Inspeccion;

use Barryvdh\DomPDF\Facade\Pdf;

class HistorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Obtener todos los funcionarios y contar inspecciones asociadas
        $funcionarios = Funcionario::orderBy('nombres')->get();

        // Contar inspecciones por funcionario (relación FuncionarioInspeccion)
        try {
            // Intentamos la consulta más eficiente (pluck agrupado)
            $inspeccionesCounts = FuncionarioInspeccion::selectRaw('funcionario_id, count(*) as total')
                ->groupBy('funcionario_id')
                ->pluck('total', 'funcionario_id')
                ->toArray();
        } catch (\Throwable $e) {
            // Fallback para drivers que no soportan selectRaw/groupBy en la misma forma (ej. MongoDB)
            $inspeccionesCounts = [];
            foreach ($funcionarios as $f) {
                try {
                    $inspeccionesCounts[$f->id] = FuncionarioInspeccion::where('funcionario_id', $f->id)->count();
                } catch (\Throwable $e2) {
                    // En caso extremo, dejar 0
                    $inspeccionesCounts[$f->id] = 0;
                }
            }
        }

        // Calcular máximo para porcentaje
        $max = count($inspeccionesCounts) ? max($inspeccionesCounts) : 0;

        return view('historial.index', compact('funcionarios', 'inspeccionesCounts', 'max'));
    }

    // Método para devolver detalles de inspecciones por funcionario (AJAX)
    public function detallesFuncionario($id)
    {
        $funcionario = Funcionario::find($id);
        if (!$funcionario) {
            abort(404, 'Funcionario no encontrado');
        }

        $detalles = FuncionarioInspeccion::where('funcionario_id', $id)
            ->with('inspeccion')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('historial.partials.detalles', compact('detalles', 'funcionario'));
    }

    /**
     * Generar y descargar un reporte PDF con el historial de inspecciones de un funcionario.
     * Si no está instalada la librería de PDF (dompdf / barryvdh), devolverá la vista HTML para previsualizar.
     */
    public function inspeccionPdf($id)
    {
        // Encontrar la inspección y el funcionario
        $funcionarioInspeccion = FuncionarioInspeccion::with(['inspeccion', 'funcionario'])->find($id);
        if (!$funcionarioInspeccion) {
            abort(404, 'Inspección no encontrada');
        }

        $inspeccion = $funcionarioInspeccion->inspeccion;
        $funcionario = $funcionarioInspeccion->funcionario;

        // Obtener nombre del proyecto
        $proyectoNombre = data_get($inspeccion, 'proyecto.nombre') 
            ?: data_get($inspeccion, 'proyecto_manual') 
            ?: '—';

        // Consolidar fotos y convertir a base64 para embebido en PDF (más fiable)
        $photos = [];
        if (!empty($funcionarioInspeccion->fotos) && is_array($funcionarioInspeccion->fotos)) {
            $photos = $funcionarioInspeccion->fotos;
        } elseif (!empty($funcionarioInspeccion->foto_llegada_obra)) {
            $photos = json_decode($funcionarioInspeccion->foto_llegada_obra, true) ?: [];
        }

        $photos_base64 = [];
        foreach ($photos as $p) {
            // Intentar ruta pública first
            $publicPath = public_path('storage/' . $p);
            $storagePath = storage_path('app/public/' . $p);
            $file = null;
            if (file_exists($publicPath)) $file = $publicPath;
            elseif (file_exists($storagePath)) $file = $storagePath;
            if ($file) {
                try {
                    $type = mime_content_type($file) ?: 'image/jpeg';
                    $data = base64_encode(file_get_contents($file));
                    $photos_base64[] = 'data:' . $type . ';base64,' . $data;
                } catch (\Throwable $e) {
                    // skip if can't read
                }
            }
        }

        // Pasamos también la asignación (registro) para acceder a horas específicas
        $asignacion = $funcionarioInspeccion;

        $data = compact('inspeccion', 'funcionario', 'proyectoNombre', 'photos_base64', 'asignacion');

    $pdf = Pdf::loadView('historial.pdf.inspeccion', $data);
        
        // Configurar PDF para mejor calidad
        $pdf->setPaper('a4');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'dejavu'
        ]);
        
        $filename = 'inspeccion_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function reporteFuncionario($id)
    {
        $funcionario = Funcionario::find($id);
        if (!$funcionario) {
            abort(404, 'Funcionario no encontrado');
        }

        $detalles = FuncionarioInspeccion::where('funcionario_id', $id)
            ->with('inspeccion')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = compact('funcionario', 'detalles');
        
        $pdf = Pdf::loadView('historial.pdf.reporte', $data);
        $pdf->setPaper('a4');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'dejavu'
        ]);
        
        $filename = 'historial_' . ($funcionario->nombres ?? '') . '_' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}

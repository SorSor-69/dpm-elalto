<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\FuncionarioInspeccion;
use App\Models\Inspeccion;
use Carbon\Carbon;

class DesempeñoController extends Controller
{
    public function index()
    {
        $periodo = request('periodo', 'mes');
        $dia = request('dia', null);
        $mes = (int) request('mes', Carbon::now()->month);
        $año = (int) request('año', Carbon::now()->year);
        $semana = (int) request('semana', 1);
        // Determinar rango de fechas según el periodo seleccionado
        if ($periodo === 'dia' && $dia) {
            $start = Carbon::parse($dia)->startOfDay();
            $end = Carbon::parse($dia)->endOfDay();
        } elseif ($periodo === 'semana') {
            $carbon = Carbon::create($año, $mes, 1);
            $firstDay = $carbon->copy()->startOfMonth();
            $lastDay = $carbon->copy()->endOfMonth();
            $weeks = [];
            $week = 1;
            $day = $firstDay->copy();
            while ($day->lte($lastDay)) {
                $startW = $day->copy()->startOfWeek();
                $endW = $day->copy()->endOfWeek();
                if ($startW->month != $carbon->month) $startW = $day->copy();
                if ($endW->month != $carbon->month) $endW = $lastDay->copy();
                $weeks[$week] = [$startW->copy(), $endW->copy()];
                $day = $endW->addDay();
                $week++;
            }
            if (isset($weeks[$semana])) {
                $start = $weeks[$semana][0]->startOfDay();
                $end = $weeks[$semana][1]->endOfDay();
            } else {
                $start = $firstDay->startOfDay();
                $end = $lastDay->endOfDay();
            }
        } elseif ($periodo === 'año') {
            $start = Carbon::create($año, 1, 1)->startOfDay();
            $end = Carbon::create($año, 12, 31)->endOfDay();
        } else { // mes por defecto
            $start = Carbon::create($año, $mes, 1)->startOfDay();
            $end = Carbon::create($año, $mes, 1)->endOfMonth()->endOfDay();
        }

        // Cálculo en PHP con fallback de fechas para mayor compatibilidad
    // Lista de todos (para el select en la vista)
    $allFuncionarios = Funcionario::where('activo', true)->get();
    $funcionarios = $allFuncionarios->map(function($f){ return $f; });
        $maxInspecciones = 0;
        foreach ($funcionarios as $funcionario) {
            $conteo = 0;
            $asignaciones = FuncionarioInspeccion::where('funcionario_id', $funcionario->id)->get();
            foreach ($asignaciones as $ai) {
                $ins = $ai->inspeccion;
                if (!$ins) continue;
                // Obtener fecha: preferir campo 'fecha', si no usar created_at, si no intentar extraerla del _id (Mongo ObjectId)
                $fechaRaw = null;
                if (isset($ins->fecha) && $ins->fecha) $fechaRaw = $ins->fecha;
                elseif (isset($ins->created_at) && $ins->created_at) $fechaRaw = $ins->created_at;
                elseif (isset($ins->_id)) {
                    try {
                        if (is_object($ins->_id) && method_exists($ins->_id, 'getTimestamp')) {
                            $ts = $ins->_id->getTimestamp();
                            // getTimestamp may return MongoDB\BSON\UTCDateTime or int
                            if ($ts instanceof \MongoDB\BSON\UTCDateTime) {
                                $fechaRaw = $ts->toDateTime()->format('Y-m-d H:i:s');
                            } elseif (is_int($ts)) {
                                $fechaRaw = \Carbon\Carbon::createFromTimestamp($ts)->toDateTimeString();
                            } elseif ($ins->_id instanceof \MongoDB\BSON\ObjectId) {
                                $dt = $ins->_id->getTimestamp();
                                $fechaRaw = \Carbon\Carbon::createFromTimestamp($dt)->toDateTimeString();
                            }
                        }
                    } catch (\Exception $e) {
                        $fechaRaw = null;
                    }
                }
                if (!$fechaRaw) continue;
                try {
                    $fecha = \Carbon\Carbon::parse($fechaRaw);
                } catch (\Exception $e) {
                    continue;
                }
                if ($fecha->between($start, $end)) $conteo++;
            }
            $funcionario->inspecciones_count = $conteo;
            if ($conteo > $maxInspecciones) $maxInspecciones = $conteo;
        }

        foreach ($funcionarios as $funcionario) {
            $funcionario->desempeño = $maxInspecciones > 0 ? round(($funcionario->inspecciones_count / $maxInspecciones) * 100) : 0;
        }

        // Total de inspecciones existentes (todas)
        try {
            $totalInspeccionesAll = Inspeccion::count();
        } catch (\Exception $e) {
            // Si hay problemas con el driver o la consulta, fallback a 0
            $totalInspeccionesAll = 0;
        }

        // Ordenar por desempeño descendente
        $funcionarios = $funcionarios->sortByDesc('desempeño')->values();

        // Soporte para filtrar por un solo funcionario desde el selector
        $selectedFuncionarioId = request('funcionario_id', null);
        if ($selectedFuncionarioId) {
            $funcionarios = $funcionarios->filter(function($f) use ($selectedFuncionarioId) {
                return (string)$f->id === (string)$selectedFuncionarioId;
            })->values();
        }

        return view('desempeño.index', compact('funcionarios', 'totalInspeccionesAll', 'allFuncionarios'));
    }

    /**
     * Devuelve la tabla de desempeño para un solo funcionario (AJAX)
     */
    public function showFuncionario($id)
    {
        $periodo = request('periodo', 'mes');
        $dia = request('dia', null);
        $mes = (int) request('mes', Carbon::now()->month);
        $año = (int) request('año', Carbon::now()->year);
        $semana = (int) request('semana', 1);
        // Determinar rango de fechas según el periodo seleccionado
        if ($periodo === 'dia' && $dia) {
            $start = Carbon::parse($dia)->startOfDay();
            $end = Carbon::parse($dia)->endOfDay();
        } elseif ($periodo === 'semana') {
            $carbon = Carbon::create($año, $mes, 1);
            $firstDay = $carbon->copy()->startOfMonth();
            $lastDay = $carbon->copy()->endOfMonth();
            $weeks = [];
            $week = 1;
            $day = $firstDay->copy();
            while ($day->lte($lastDay)) {
                $startW = $day->copy()->startOfWeek();
                $endW = $day->copy()->endOfWeek();
                if ($startW->month != $carbon->month) $startW = $day->copy();
                if ($endW->month != $carbon->month) $endW = $lastDay->copy();
                $weeks[$week] = [$startW->copy(), $endW->copy()];
                $day = $endW->addDay();
                $week++;
            }
            if (isset($weeks[$semana])) {
                $start = $weeks[$semana][0]->startOfDay();
                $end = $weeks[$semana][1]->endOfDay();
            } else {
                $start = $firstDay->startOfDay();
                $end = $lastDay->endOfDay();
            }
        } elseif ($periodo === 'año') {
            $start = Carbon::create($año, 1, 1)->startOfDay();
            $end = Carbon::create($año, 12, 31)->endOfDay();
        } else { // mes por defecto
            $start = Carbon::create($año, $mes, 1)->startOfDay();
            $end = Carbon::create($año, $mes, 1)->endOfMonth()->endOfDay();
        }

        // Calcular máximo entre todos iterando (mismo algoritmo que index)
        $todos = Funcionario::where('activo', true)->get();
        $maxInspecciones = 0;
        foreach ($todos as $func) {
            $conteoTmp = 0;
            $asignaciones = FuncionarioInspeccion::where('funcionario_id', $func->id)->get();
            foreach ($asignaciones as $ai) {
                $ins = $ai->inspeccion;
                if (!$ins) continue;
                $fechaRaw = null;
                if (isset($ins->fecha) && $ins->fecha) $fechaRaw = $ins->fecha;
                elseif (isset($ins->created_at) && $ins->created_at) $fechaRaw = $ins->created_at;
                elseif (isset($ins->_id)) {
                    try {
                        if (is_object($ins->_id) && method_exists($ins->_id, 'getTimestamp')) {
                            $ts = $ins->_id->getTimestamp();
                            if ($ts instanceof \MongoDB\BSON\UTCDateTime) {
                                $fechaRaw = $ts->toDateTime()->format('Y-m-d H:i:s');
                            } elseif (is_int($ts)) {
                                $fechaRaw = \Carbon\Carbon::createFromTimestamp($ts)->toDateTimeString();
                            } elseif ($ins->_id instanceof \MongoDB\BSON\ObjectId) {
                                $dt = $ins->_id->getTimestamp();
                                $fechaRaw = \Carbon\Carbon::createFromTimestamp($dt)->toDateTimeString();
                            }
                        }
                    } catch (\Exception $e) {
                        $fechaRaw = null;
                    }
                }
                if (!$fechaRaw) continue;
                try { $fecha = \Carbon\Carbon::parse($fechaRaw); } catch (\Exception $e) { continue; }
                if ($fecha->between($start, $end)) $conteoTmp++;
            }
            if ($conteoTmp > $maxInspecciones) $maxInspecciones = $conteoTmp;
        }

        $funcionario = Funcionario::findOrFail($id);
        $conteo = 0;
        $asignaciones = FuncionarioInspeccion::where('funcionario_id', $funcionario->id)->get();
        foreach ($asignaciones as $ai) {
            $ins = $ai->inspeccion;
            if (!$ins) continue;
            $fechaRaw = null;
            if (isset($ins->fecha) && $ins->fecha) $fechaRaw = $ins->fecha;
            elseif (isset($ins->created_at) && $ins->created_at) $fechaRaw = $ins->created_at;
            elseif (isset($ins->_id)) {
                try {
                    if (is_object($ins->_id) && method_exists($ins->_id, 'getTimestamp')) {
                        $ts = $ins->_id->getTimestamp();
                        if ($ts instanceof \MongoDB\BSON\UTCDateTime) {
                            $fechaRaw = $ts->toDateTime()->format('Y-m-d H:i:s');
                        } elseif (is_int($ts)) {
                            $fechaRaw = \Carbon\Carbon::createFromTimestamp($ts)->toDateTimeString();
                        } elseif ($ins->_id instanceof \MongoDB\BSON\ObjectId) {
                            $dt = $ins->_id->getTimestamp();
                            $fechaRaw = \Carbon\Carbon::createFromTimestamp($dt)->toDateTimeString();
                        }
                    }
                } catch (\Exception $e) {
                    $fechaRaw = null;
                }
            }
            if (!$fechaRaw) continue;
            try { $fecha = \Carbon\Carbon::parse($fechaRaw); } catch (\Exception $e) { continue; }
            if ($fecha->between($start, $end)) $conteo++;
        }

        $funcionario->inspecciones_count = $conteo;
        $funcionario->desempeño = $maxInspecciones > 0 ? round(($conteo / $maxInspecciones) * 100) : 0;

        $funcionarios = collect([$funcionario]);
        return view('desempeño.tabla', compact('funcionarios'));
    }
}

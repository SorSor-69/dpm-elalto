@extends('adminlte::page')

@section('title', 'Tutoriales del Sistema')

@section('content_header')
    <h1 class="text-primary"><i class="fas fa-video"></i> Tutoriales de Uso del Sistema</h1>
    <p class="text-muted">
        Bienvenido. Aquí encontrarás videos cortos y guías que te ayudarán a utilizar todas las funciones del sistema de manera sencilla y eficiente.
    </p>
@stop

@section('content')
<div class="row justify-content-center">
    @for($i = 1; $i <= 5; $i++)
    <div class="col-auto mb-4">
        <div class="card shadow h-100" style="width:300px;">
            <div class="card-header bg-primary text-white text-center p-2" style="font-size:1rem;">
                <i class="fas fa-play-circle"></i> Video Tutorial {{ $i }}
            </div>
            <div class="card-body d-flex flex-column align-items-center p-2">
                <video controls style="width: 250px; border-radius:8px;">
                    <source src="{{ asset('tutorial/video_' . $i . '.mp4') }}" type="video/mp4">
                    Tu navegador no soporta el video.
                </video>
            </div>
        </div>
    </div>
    @endfor
</div>
@stop
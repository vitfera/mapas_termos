@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Painel Administrativo</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\Template::count() }}" text="Templates" icon="fas fa-file-alt" theme="info" url="{{ route('admin.templates.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\PlaceholderMapping::count() }}" text="Mapeamentos" icon="fas fa-link" theme="warning" url="{{ route('admin.placeholder-mappings.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\OpportunitySetting::count() }}" text="Configurações" icon="fas fa-cog" theme="success" url="{{ route('admin.opportunity-settings.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="Gerar" text="Termos" icon="fas fa-file-pdf" theme="primary" url="{{ route('admin.terms.create') }}"/>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <x-adminlte-small-box title="{{ \App\Models\GeneratedTerm::count() }}" text="Termos Gerados" icon="fas fa-file-check" theme="success"/>
        </div>
        <div class="col-md-4">
            <x-adminlte-small-box title="{{ \App\Models\TermGenerationProcess::where('status', 'processing')->count() }}" text="Em Processamento" icon="fas fa-spinner" theme="warning"/>
        </div>
        <div class="col-md-4">
            <x-adminlte-small-box title="{{ \App\Models\TermGenerationProcess::where('status', 'completed')->whereDate('created_at', today())->count() }}" text="Concluídos Hoje" icon="fas fa-calendar-check" theme="info"/>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Atividade Recente</h3>
                </div>
                <div class="card-body">
                    @php
                        $recentProcesses = \App\Models\TermGenerationProcess::with(['opportunity:id,name', 'template:id,name'])
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentProcesses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Edital</th>
                                        <th>Template</th>
                                        <th>Status</th>
                                        <th>Progresso</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentProcesses as $process)
                                        <tr>
                                            <td>{{ $process->opportunity->name ?? 'N/A' }}</td>
                                            <td>{{ $process->template->name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'pending' => ['text' => 'Na fila', 'class' => 'warning'],
                                                        'processing' => ['text' => 'Processando', 'class' => 'info'],
                                                        'completed' => ['text' => 'Concluído', 'class' => 'success'],
                                                        'failed' => ['text' => 'Erro', 'class' => 'danger']
                                                    ];
                                                    $config = $statusConfig[$process->status] ?? ['text' => $process->status, 'class' => 'secondary'];
                                                @endphp
                                                <span class="badge badge-{{ $config['class'] }}">{{ $config['text'] }}</span>
                                            </td>
                                            <td>
                                                @if($process->total_registrations > 0)
                                                    {{ $process->processed_count }}/{{ $process->total_registrations }}
                                                    ({{ number_format($process->progress_percentage, 1) }}%)
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $process->created_at->diffForHumans() }}</td>
                                            <td>
                                                @if($process->status === 'completed' && $process->zip_filename)
                                                    <a href="{{ route('admin.terms.download') }}?process_id={{ $process->id }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.terms.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Ver Todos os Processos
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Nenhum processo de geração encontrado ainda.</p>
                            <a href="{{ route('admin.terms.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Gerar Primeiros Termos
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

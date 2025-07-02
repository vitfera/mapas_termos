@extends('adminlte::page')

@section('content_header')
  <h1>Gerar Termos de um Edital</h1>
@stop

@push('css')
  <link rel="stylesheet" href="{{ asset('css/terms-async.css') }}">
@endpush

@section('content')
  <!-- Formulário de Seleção -->
  <div id="selectionForm" class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Selecionar Edital e Template</h3>
    </div>
    <div class="card-body">
      <form id="termsForm">
        @csrf

        <div class="form-group">
          <label for="opportunity_id">Edital</label>
          <select name="opportunity_id" id="opportunity_id" class="form-control" required>
            <option value="">-- selecione --</option>
            @foreach($opportunities as $op)
              <option value="{{ $op->id }}">{{ $op->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="template_id">Template</label>
          <select name="template_id" id="template_id" class="form-control" required>
            <option value="">-- selecione --</option>
            @foreach($templates as $tpl)
              <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
            @endforeach
          </select>
        </div>

        <button id="btnGenerate" type="submit" class="btn btn-success">
          <span id="btnText">Gerar Termos</span>
          <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display: none;"></span>
        </button>
      </form>
    </div>
  </div>

  <!-- Área de Progresso -->
  <div id="progressArea" class="card card-info" style="display: none;">
    <div class="card-header">
      <h3 class="card-title">Processando Termos</h3>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <div class="d-flex justify-content-between">
          <span>Progresso:</span>
          <span id="progressText">0/0 (0%)</span>
        </div>
        <div class="progress mt-2">
          <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
               role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <strong>Status:</strong> <span id="statusText" class="badge badge-info">Iniciando...</span>
        </div>
        <div class="col-md-6">
          <strong>Tempo decorrido:</strong> <span id="timeElapsed">0s</span>
        </div>
      </div>

      <div class="mt-3">
        <button id="btnCancel" class="btn btn-secondary" onclick="cancelProcess()">Cancelar</button>
      </div>
    </div>
  </div>

  <!-- Área de Resultado -->
  <div id="resultArea" class="card card-success" style="display: none;">
    <div class="card-header">
      <h3 class="card-title">Processamento Concluído</h3>
    </div>
    <div class="card-body">
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span id="resultMessage">Termos gerados com sucesso!</span>
      </div>
      
      <div class="mb-3">
        <button id="btnDownload" class="btn btn-primary btn-lg">
          <i class="fas fa-download"></i> Download ZIP
        </button>
        
        <button class="btn btn-secondary ml-2" onclick="resetForm()">
          <i class="fas fa-redo"></i> Gerar Novos Termos
        </button>
      </div>

      <div id="processDetails">
        <!-- Detalhes do processo serão preenchidos via JS -->
      </div>
    </div>
  </div>

  <!-- Área de Erro -->
  <div id="errorArea" class="card card-danger" style="display: none;">
    <div class="card-header">
      <h3 class="card-title">Erro no Processamento</h3>
    </div>
    <div class="card-body">
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="errorMessage">Ocorreu um erro durante o processamento.</span>
      </div>
      
      <button class="btn btn-secondary" onclick="resetForm()">
        <i class="fas fa-redo"></i> Tentar Novamente
      </button>
    </div>
  </div>

  <!-- Histórico de Processos -->
  <div class="card card-default mt-4">
    <div class="card-header">
      <h3 class="card-title">Processos Recentes</h3>
      <div class="card-tools">
        <button class="btn btn-sm btn-tool" onclick="loadRecentProcesses()">
          <i class="fas fa-sync"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <div id="recentProcesses">
        <div class="text-center text-muted">
          <i class="fas fa-spinner fa-spin"></i> Carregando...
        </div>
      </div>
    </div>
  </div>
@stop

@push('js')
<script>
let currentProcessId = null;
let startTime = null;
let pollingInterval = null;
let timeInterval = null;

document.addEventListener('DOMContentLoaded', function() {
  loadRecentProcesses();
  
  const form = document.getElementById('termsForm');
  form.addEventListener('submit', handleFormSubmit);
});

async function handleFormSubmit(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const btn = document.getElementById('btnGenerate');
  const btnText = document.getElementById('btnText');
  const spinner = document.getElementById('btnSpinner');
  
  // Desativa o botão
  btn.disabled = true;
  btnText.textContent = 'Iniciando...';
  spinner.style.display = 'inline-block';
  
  try {
    const response = await fetch('{{ route("admin.terms.store") }}', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      currentProcessId = data.process_id;
      startMonitoring(data);
    } else {
      showError(data.message || 'Erro desconhecido');
      resetButton();
    }
    
  } catch (error) {
    console.error('Erro:', error);
    showError('Erro de conexão');
    resetButton();
  }
}

function startMonitoring(initialData) {
  // Esconde formulário e mostra área de progresso
  document.getElementById('selectionForm').style.display = 'none';
  document.getElementById('progressArea').style.display = 'block';
  document.getElementById('resultArea').style.display = 'none';
  document.getElementById('errorArea').style.display = 'none';
  
  startTime = Date.now();
  
  // Inicia contadores de tempo
  timeInterval = setInterval(updateTimeElapsed, 1000);
  
  // Inicia polling do status
  pollingInterval = setInterval(checkStatus, 2000);
  
  // Primeira verificação imediata
  setTimeout(checkStatus, 500);
}

async function checkStatus() {
  if (!currentProcessId) return;
  
  try {
    const response = await fetch(`{{ route("admin.terms.status") }}?process_id=${currentProcessId}`);
    const data = await response.json();
    
    updateProgress(data);
    
    if (data.status === 'completed') {
      showSuccess(data);
      stopMonitoring();
    } else if (data.status === 'failed') {
      showError(data.error_message || 'Erro no processamento');
      stopMonitoring();
    }
    
  } catch (error) {
    console.error('Erro ao verificar status:', error);
  }
}

function updateProgress(data) {
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');
  const statusText = document.getElementById('statusText');
  
  const percentage = data.progress_percentage || 0;
  const processed = data.processed_count || 0;
  const total = data.total_registrations || 0;
  
  progressBar.style.width = percentage + '%';
  progressBar.setAttribute('aria-valuenow', percentage);
  progressText.textContent = `${processed}/${total} (${percentage.toFixed(1)}%)`;
  
  // Atualiza status com cores
  const statusMap = {
    'pending': { text: 'Na fila', class: 'badge-warning' },
    'processing': { text: 'Processando', class: 'badge-info' },
    'completed': { text: 'Concluído', class: 'badge-success' },
    'failed': { text: 'Erro', class: 'badge-danger' }
  };
  
  const status = statusMap[data.status] || { text: data.status, class: 'badge-secondary' };
  statusText.textContent = status.text;
  statusText.className = `badge ${status.class}`;
}

function updateTimeElapsed() {
  if (!startTime) return;
  
  const elapsed = Math.floor((Date.now() - startTime) / 1000);
  const minutes = Math.floor(elapsed / 60);
  const seconds = elapsed % 60;
  
  const timeText = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
  document.getElementById('timeElapsed').textContent = timeText;
}

function showSuccess(data) {
  document.getElementById('progressArea').style.display = 'none';
  document.getElementById('resultArea').style.display = 'block';
  
  const processDetails = document.getElementById('processDetails');
  processDetails.innerHTML = `
    <div class="row">
      <div class="col-md-4">
        <strong>Termos processados:</strong><br>
        <span class="h4 text-success">${data.processed_count || 0}</span>
      </div>
      <div class="col-md-4">
        <strong>Tempo total:</strong><br>
        <span>${document.getElementById('timeElapsed').textContent}</span>
      </div>
      <div class="col-md-4">
        <strong>Arquivo ZIP:</strong><br>
        <span class="text-muted">${data.zip_filename || 'N/A'}</span>
      </div>
    </div>
  `;
  
  // Configura botão de download
  const btnDownload = document.getElementById('btnDownload');
  btnDownload.onclick = () => downloadResult(currentProcessId);
  
  loadRecentProcesses();
}

function showError(message) {
  document.getElementById('progressArea').style.display = 'none';
  document.getElementById('errorArea').style.display = 'block';
  document.getElementById('errorMessage').textContent = message;
  
  loadRecentProcesses();
}

function stopMonitoring() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
  
  if (timeInterval) {
    clearInterval(timeInterval);
    timeInterval = null;
  }
}

function resetForm() {
  stopMonitoring();
  
  document.getElementById('selectionForm').style.display = 'block';
  document.getElementById('progressArea').style.display = 'none';
  document.getElementById('resultArea').style.display = 'none';
  document.getElementById('errorArea').style.display = 'none';
  
  resetButton();
  
  currentProcessId = null;
  startTime = null;
}

function resetButton() {
  const btn = document.getElementById('btnGenerate');
  const btnText = document.getElementById('btnText');
  const spinner = document.getElementById('btnSpinner');
  
  btn.disabled = false;
  btnText.textContent = 'Gerar Termos';
  spinner.style.display = 'none';
}

function cancelProcess() {
  if (confirm('Tem certeza que deseja cancelar o processamento?')) {
    resetForm();
  }
}

async function downloadResult(processId) {
  window.location.href = `{{ route("admin.terms.download") }}?process_id=${processId}`;
}

async function loadRecentProcesses() {
  const container = document.getElementById('recentProcesses');
  
  try {
    const response = await fetch('{{ route("admin.terms.processes") }}');
    const processes = await response.json();
    
    if (processes.length === 0) {
      container.innerHTML = '<p class="text-muted">Nenhum processo encontrado.</p>';
      return;
    }
    
    const html = processes.map(process => `
      <div class="border rounded p-3 mb-2 ${process.status === 'completed' ? 'bg-light' : ''}">
        <div class="row">
          <div class="col-md-6">
            <strong>${process.opportunity?.name || 'N/A'}</strong><br>
            <small class="text-muted">Template: ${process.template?.name || 'N/A'}</small>
          </div>
          <div class="col-md-3">
            <span class="badge badge-${getStatusColor(process.status)}">${getStatusText(process.status)}</span><br>
            <small>${process.processed_count || 0}/${process.total_registrations || 0} termos</small>
          </div>
          <div class="col-md-3 text-right">
            ${process.status === 'completed' && process.zip_filename ? 
              `<button class="btn btn-sm btn-outline-primary" onclick="downloadResult(${process.id})">
                <i class="fas fa-download"></i> Download
              </button>` : 
              process.status === 'processing' ? 
                `<button class="btn btn-sm btn-outline-info" onclick="resumeMonitoring(${process.id})">
                  <i class="fas fa-eye"></i> Acompanhar
                </button>` : ''
            }
            <br>
            <small class="text-muted">${formatDate(process.created_at)}</small>
          </div>
        </div>
      </div>
    `).join('');
    
    container.innerHTML = html;
    
  } catch (error) {
    console.error('Erro ao carregar processos:', error);
    container.innerHTML = '<p class="text-danger">Erro ao carregar processos.</p>';
  }
}

function resumeMonitoring(processId) {
  currentProcessId = processId;
  startMonitoring({});
}

function getStatusColor(status) {
  const colors = {
    'pending': 'warning',
    'processing': 'info', 
    'completed': 'success',
    'failed': 'danger'
  };
  return colors[status] || 'secondary';
}

function getStatusText(status) {
  const texts = {
    'pending': 'Na fila',
    'processing': 'Processando',
    'completed': 'Concluído', 
    'failed': 'Erro'
  };
  return texts[status] || status;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR');
}
</script>
@endpush

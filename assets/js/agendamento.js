// Script para o Calendário de Agendamento

document.addEventListener('DOMContentLoaded', function() {
    const calendarContainer = document.getElementById('calendario');
    const horariosContainer = document.getElementById('horariosDisponiveis');
    const dataInput = document.getElementById('dataSelecionada');
    const horarioInput = document.getElementById('horarioSelecionado');
    
    let dataSelecionada = null;
    let horarioSelecionado = null;
    
    // Gerar calendário
    function gerarCalendario() {
        const hoje = new Date();
        const diasNoMes = [];
        const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
        
        // Preencher dias do mês atual
        for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
            const data = new Date(hoje.getFullYear(), hoje.getMonth(), dia);
            diasNoMes.push({
                dia: dia,
                data: data,
                diaSemana: data.getDay()
            });
        }
        
        // Adicionar alguns dias do próximo mês
        const proximoMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 1);
        const ultimoDiaProximo = new Date(hoje.getFullYear(), hoje.getMonth() + 2, 0);
        for (let dia = 1; dia <= Math.min(14, ultimoDiaProximo.getDate()); dia++) {
            const data = new Date(hoje.getFullYear(), hoje.getMonth() + 1, dia);
            diasNoMes.push({
                dia: dia,
                data: data,
                diaSemana: data.getDay()
            });
        }
        
        let html = '<div class="row g-2">';
        diasNoMes.forEach(item => {
            const dataStr = formatarData(item.data);
            const hojeStr = formatarData(hoje);
            const isHoje = dataStr === hojeStr;
            const isPassado = item.data < hoje && !isHoje;
            const isDisponivel = !isPassado && horariosConfig[item.diaSemana];
            const classes = [];
            
            if (isHoje) classes.push('today');
            if (isPassado || !isDisponivel) classes.push('disabled');
            if (dataStr === dataSelecionada) classes.push('selected');
            
            html += `
                <div class="col-4 col-md-3">
                    <div class="calendar-day ${classes.join(' ')}" 
                         data-data="${dataStr}" 
                         ${isDisponivel && !isPassado ? 'onclick="selecionarData(this)"' : ''}>
                        <div class="fw-bold">${item.dia}</div>
                        <div class="small">${getNomeDiaSemana(item.diaSemana)}</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        calendarContainer.innerHTML = html;
    }
    
    function formatarData(data) {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    }
    
    function getNomeDiaSemana(dia) {
        const dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        return dias[dia];
    }
    
    window.selecionarData = function(elemento) {
        const data = elemento.getAttribute('data-data');
        dataSelecionada = data;
        dataInput.value = data;
        
        // Remover seleção anterior
        document.querySelectorAll('.calendar-day').forEach(el => {
            el.classList.remove('selected');
        });
        elemento.classList.add('selected');
        
        // Carregar horários disponíveis
        carregarHorariosDisponiveis(data);
    };
    
    function carregarHorariosDisponiveis(data) {
        if (!horariosConfig || Object.keys(horariosConfig).length === 0) {
            horariosContainer.innerHTML = '<div class="alert alert-warning">Nenhum horário configurado. Configure os horários no painel administrativo.</div>';
            horarioInput.value = '';
            return;
        }
        
        const dataObj = new Date(data + 'T00:00:00');
        const diaSemana = dataObj.getDay();
        const config = horariosConfig[diaSemana];
        
        if (!config) {
            horariosContainer.innerHTML = '<div class="alert alert-info">Este dia não possui horários disponíveis. Configure os horários no painel administrativo.</div>';
            horarioInput.value = '';
            return;
        }
        
        const horaInicio = parseTime(config.hora_inicio);
        const horaFim = parseTime(config.hora_fim);
        const intervalo = parseInt(config.intervalo) || 60; // Default 60 minutos (1 hora)
        const horaInicioAlmoco = config.hora_inicio_almoco ? parseTime(config.hora_inicio_almoco) : null;
        const horaFimAlmoco = config.hora_fim_almoco ? parseTime(config.hora_fim_almoco) : null;
        
        const horarios = [];
        let horaAtual = horaInicio;
        
        while (horaAtual < horaFim) {
            // Pular intervalo de almoço se houver
            if (horaInicioAlmoco && horaFimAlmoco && 
                horaAtual >= horaInicioAlmoco && horaAtual < horaFimAlmoco) {
                horaAtual = horaFimAlmoco;
                continue;
            }
            
            const horarioStr = formatarHorario(horaAtual);
            // Verificar se está ocupado - comparar strings de horário
            const ocupado = horariosOcupados && horariosOcupados[data] && 
                           Array.isArray(horariosOcupados[data]) && 
                           horariosOcupados[data].includes(horarioStr);
            
            horarios.push({
                horario: horarioStr,
                ocupado: ocupado
            });
            
            horaAtual += intervalo;
        }
        
        if (horarios.length === 0) {
            horariosContainer.innerHTML = '<p class="text-danger">Nenhum horário disponível para este dia.</p>';
            horarioInput.value = '';
            return;
        }
        
        let html = '<div class="d-flex flex-wrap gap-2">';
        horarios.forEach(item => {
            const classes = item.ocupado ? 'disabled' : '';
            html += `
                <button type="button" class="time-slot ${classes}" 
                        data-horario="${item.horario}"
                        ${item.ocupado ? 'disabled' : 'onclick="selecionarHorario(this)"'}>
                    ${item.horario}
                    ${item.ocupado ? '<br><small>Ocupado</small>' : ''}
                </button>
            `;
        });
        html += '</div>';
        
        horariosContainer.innerHTML = html;
        horarioInput.value = '';
        horarioSelecionado = null;
    }
    
    function parseTime(timeStr) {
        const [hora, minuto] = timeStr.split(':').map(Number);
        return hora * 60 + minuto; // Retorna em minutos
    }
    
    function formatarHorario(minutos) {
        const hora = Math.floor(minutos / 60);
        const minuto = minutos % 60;
        return `${String(hora).padStart(2, '0')}:${String(minuto).padStart(2, '0')}`;
    }
    
    window.selecionarHorario = function(elemento) {
        if (elemento.classList.contains('disabled')) return;
        
        const horario = elemento.getAttribute('data-horario');
        horarioSelecionado = horario;
        horarioInput.value = horario;
        
        // Remover seleção anterior
        document.querySelectorAll('.time-slot').forEach(el => {
            el.classList.remove('selected');
        });
        elemento.classList.add('selected');
    };
    
    // Inicializar calendário
    if (calendarContainer) {
        gerarCalendario();
        
        // Validação do formulário
        const form = document.getElementById('formAgendamento');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validar campos obrigatórios
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Validar data e horário selecionados
                if (!dataSelecionada || !horarioSelecionado) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!dataSelecionada) {
                        alert('Por favor, selecione uma data no calendário.');
                        document.getElementById('calendario').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else if (!horarioSelecionado) {
                        alert('Por favor, selecione um horário disponível.');
                        document.getElementById('horariosDisponiveis').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    form.classList.add('was-validated');
                    return;
                }
                
                // Se chegou aqui, formulário está válido
            });
        }
    }
});


<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AgendaAtendimentoController,
    AtestadoController,
    AulaController,
    AulaDadoController,
    BolsaController,
    BoletoController,
    CarneController,
    CatracaController,
    CobrancaController,
    ContatoController,
    CursoController,
    DiaNaoLetivoController,
    DisciplinaController,
    DividaAtivaController,
    DocumentoController,
    EnderecoController,
    EventoController,
    FichaTecnicaController,
    FrequenciaController,
    HtpController,
    InscricaoController,
    IntegracaoBBController,
    JornadaController,
    JustificativaAusenciaController,
    LancamentoController,
    LocaisController,
    MatriculaController,
    NotificacaoController,
    painelController,
    PerfilController,
    PerfilMatriculaController,
    PessoaController,
    PessoaDadosAcademicosController,
    PessoaDadosAdminController,
    PessoaDadosClinicosController,
    PessoaDadosGeraisController,
    PessoaDadosJornadasController,
    PixController,
    PlanoEnsinoController,
    RequisitosController,
    RelatorioController,
    RemessaController,
    RetornoController,
    SalaController,
    SalaAgendamentoController,
    SecretariaController,
    TagController,
    TransferenciaController,
    TurmaController,
    UploadController,
    UsoLivreController,
    ValorController,
    WebServicesController,
};
use App\Http\Controllers\Reports\{
    JornadaDocentes,
    JornadaPrograma,
    JornadaHTP,
    ReceitaAnualReportController
};
use App\Http\Controllers\Auth\{
    AuthController,
    ForgotPasswordController,
    LoginController,
    ResetPasswordController,
    RegisterController,
    TokenController,
    PerfilAuthController
};
use App\Http\Controllers\Perfil\{
    RematriculaController,
};


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'web' middleware group. Now create something great!
|
*/

// ==================== ROTAS PÚBLICAS ====================
Route::get('/', [painelController::class, 'index']);
//Route::get('login', [LoginController::class, 'login_view'])->name('login');

// Cursos e Vagas
Route::get('cursos-disponiveis', [TurmaController::class, 'turmasSite']); 
Route::get('vagas', [TurmaController::class, 'turmasSite']);

// Boletos Públicos
Route::get('meuboleto', function(){ return view('financeiro.boletos.meuboleto');});
Route::post('meuboleto', [BoletoController::class, 'segundaVia']);
Route::get('boleto/{id}/{token}', [BoletoController::class, 'imprimir']);

// Serviços Públicos
Route::get('buscarbairro/{var}', [EnderecoController::class, 'buscarBairro']);
Route::get('ipca', [ValorController::class, 'getIPCA']);
Route::get('agenda-atendimento/{data}', [AgendaAtendimentoController::class, 'horariosData']);
Route::get('gerar-token', [TokenController::class, 'gerarToken'])->name('gerar.token');
Route::get('apagar-token', [TokenController::class, 'apagarToken'])->name('apagar.token');
Route::get('rematricula', function(){ return view('perfil.cpf'); });

// ==================== PERFIL DO USUÁRIO ====================
Route::prefix('perfil')->group(function(){
    // Autenticação
    Route::get('cpf', function(){ return view('perfil.cpf'); });
    Route::get('cadastrar-pessoa/{cpf}', [PerfilController::class, 'cadastrarView']);
    Route::post('cadastrar-pessoa/{cpf}', [PerfilController::class, 'cadastrarExec']);
    Route::get('autentica/{cpf}', [PerfilAuthController::class, 'verificarCPF']);
    Route::post('autentica/{cpf}', [PerfilAuthController::class, 'autenticaCPF']);
    Route::get('recuperar-senha/{cpf}', [PerfilAuthController::class, 'recuperarSenhaView']);
    Route::get('resetar-senha/{token}', [PerfilAuthController::class, 'recuperarSenhaExec']);
    Route::post('cadastrar-senha', [PerfilAuthController::class, 'cadastrarSenha']);
    
    // Área logada
    Route::middleware('login.perfil')->group(function(){
        Route::get('/', [PerfilController::class, 'painel']);
        
        // Parceria
        Route::get('parceria', [PerfilController::class, 'parceriaIndex']);
        Route::post('parceria', [PerfilController::class, 'parceriaExec']);
        Route::get('parceria/curriculo', [PerfilController::class, 'parceriaCurriculo']);
        Route::get('parceria/cancelar', [PerfilController::class, 'parceriaCancelar']);
        
        // Segurança
        Route::get('alterar-senha', [PerfilAuthController::class, 'trocarSenhaView']);
        Route::post('alterar-senha', [PerfilAuthController::class, 'trocarSenhaExec']);
        
        // Dados Pessoais
        Route::get('alterar-dados', [PerfilController::class, 'alterarDadosView']);
        Route::post('alterar-dados', [PerfilController::class, 'alterarDadosExec']);
        
        // Financeiro
        Route::get('boletos', [PerfilController::class, 'boletosPerfil']);
        Route::get('boleto/{numero}', [BoletoController::class, 'imprimir']);
        
        // Matrículas
        Route::prefix('matricula')->group(function(){
            Route::get('/', [PerfilMatriculaController::class, 'matriculasAtivas']);
            Route::get('inscricao', [PerfilMatriculaController::class, 'turmasDisponiveis']);
            Route::post('confirmacao', [PerfilMatriculaController::class, 'confirmacao']);
            Route::post('inscricao', [PerfilMatriculaController::class, 'inscricao']);
            Route::get('cancelar/{inscricao}', [PerfilMatriculaController::class, 'cancelar']);
            Route::get('termo/{id}', [MatriculaController::class, 'termo']);
            Route::get('termo', function(){ return view('juridico.documentos.termo_aberto_ead'); });
        });
        
        // Atestados
        Route::prefix('atestado')->group(function(){
            Route::get('/', [PerfilController::class, 'atestadoIndex']);
            Route::get('cadastrar', [PerfilController::class, 'cadastrarAtestadoView']);
            Route::post('cadastrar', [PerfilController::class, 'cadastrarAtestadoexec']);
            
        });
        
        // Rematrícula
        Route::prefix('rematricula')->group(function(){
            Route::get('/', [RematriculaController::class, 'rematricula_view']);
            Route::post('/', [PerfilMatriculaController::class, 'confirmacao']);
        });
        
        // Atendimento
        Route::prefix('atendimento')->group(function(){
            Route::get('/', [AgendaAtendimentoController::class, 'indexPerfil']);
            Route::post('/', [AgendaAtendimentoController::class, 'agendarPerfil']);
            Route::get('cancelar/{id}', [AgendaAtendimentoController::class, 'cancelarPerfil']);
        });
    });
    
    Route::get('logout', [PerfilAuthController::class, 'logout']);
});

// ==================== AUTENTICAÇÃO ====================
//Auth::routes(['register' => false]);

// ==================== ROTAS AUTENTICADAS ====================
Route::middleware(['auth','login'])->group(function(){
    
    // ========== ROTAS GERAIS ==========
    Route::get('home', [painelController::class, 'index']);
    
    // Gestão de Acesso
    Route::get('/trocarminhasenha', [LoginController::class, 'trocarMinhaSenha_view']);
    Route::post('/trocarminhasenha', [LoginController::class, 'trocarMinhaSenha_exec']);
    Route::get('/pessoa/trocarsenha/{var}', [LoginController::class, 'trocarSenhaUsuario_view']);
    Route::post('/pessoa/trocarsenha/{var}', [LoginController::class, 'trocarSenhaUsuario_exec']);
    Route::get('/pessoa/cadastraracesso/{var}', [LoginController::class, 'cadastrarAcesso_view']);
    Route::post('/pessoa/cadastraracesso/{var}', [LoginController::class, 'cadastrarAcesso_exec']);
    
    // Notificações
    Route::get('notificacoes', [NotificacaoController::class, 'index']);
    
    // Downloads e Visualização
    Route::get('download/{tipo}/{arquivo}', function ($tipo, $arquivo){
        return App\classes\Arquivo::download($tipo, $arquivo);
    });
    
    Route::get('arquivo/{tipo}/{arquivo}', function ($tipo, $arquivo){
        return App\classes\Arquivo::show($tipo, $arquivo);
    });
    
    // ========== GESTÃO ACADÊMICA ==========
    
    // Aulas
    Route::prefix('aulas')->group(function(){
        Route::get('gerar/{turma}', [AulaController::class, 'gerarAulas']);
        Route::POST('alterar-status', [AulaController::class, 'alterarStatus']);
        Route::POST('limpar-dado', [AulaDadoController::class, 'limparDado']);
        Route::GET('recriar/{turma}', [AulaController::class, 'recriarAulasView']);	
    });
    
    // Turmas
    Route::prefix('turmas')->group(function(){
        Route::get('cadastrar', [TurmaController::class, 'create'])->name('turmas.cadastrar');
        
        Route::middleware('liberar.recurso:30')->group(function(){
            Route::get('gerar-por-ficha/{id?}', [TurmaController::class, 'gerarPorFichaView']);
            Route::post('gerar-por-ficha/{id?}', [TurmaController::class, 'store']);
        });
        
        Route::middleware('liberar.recurso:18')->group(function(){
            Route::get('forcar-exclusao/{turma}', [TurmaController::class, 'forcarExclusao']);
            Route::get('/', [TurmaController::class, 'listarSecretaria'])->name('turmas');
            Route::post('cadastrar', [TurmaController::class, 'store']);
            Route::post('recadastrar', [TurmaController::class, 'storeRecadastro']);
            Route::get('/alterar/{acao}/{turmas}', [TurmaController::class, 'acaolote']);
            Route::post('editar/{var}', [TurmaController::class, 'update']);
            Route::get('status/{status}/{turma}', [TurmaController::class, 'status']);
            Route::get('status-matriculas/{status}/{turma}', [TurmaController::class, 'statusMatriculas']);
            Route::post('importar', [TurmaController::class, 'uploadImportaTurma']);
        });
        
        Route::get('listar', [TurmaController::class, 'index']);
        Route::get('apagar/{var}', [TurmaController::class, 'destroy']);
        Route::get('editar/{var}', [TurmaController::class, 'edit']);		
        Route::get('turmasjson', [TurmaController::class, 'turmasJSON']);
        Route::get('inscritos/{turma}', [InscricaoController::class, 'verInscritos']);
        Route::get('lista/{id}', [painelController::class, 'chamada']);
        Route::get('importar', function(){ return view('turmas.upload');});		
        Route::post('processar-importacao', [TurmaController::class, 'processarImportacao']);
        Route::get('expiradas', [TurmaController::class, 'processarTurmasExpiradas'])->name('turmas.expiradas');
        Route::get('modificar-requisitos/{id}', [RequisitosController::class, 'editRequisitosTurma']);
        Route::post('turmas-requisitos', [RequisitosController::class, 'editRequisitosTurma']);
        Route::post('modificar-requisitos/{id}', [RequisitosController::class, 'storeRequisitosTurma']);
        Route::get('atualizar-inscritos', [TurmaController::class, 'atualizarInscritos']);
        Route::get('/{turma}', [InscricaoController::class, 'verInscricoes']);
        Route::post('/{turma}', [InscricaoController::class, 'inscreverAlunoLote']);
        Route::get('/dados-gerais/{turma}', [TurmaController::class, 'mostrarTurma']);
    });
    
    // Cursos e Disciplinas
    Route::prefix('cursos')->group(function(){
        // Cursos
        Route::get('/', [CursoController::class, 'index']);
        Route::get('listarporprogramajs/{var}', [CursoController::class, 'listarPorPrograma']);
        Route::get('cadastrar', [CursoController::class, 'create']);
        Route::post('cadastrar', [CursoController::class, 'store']);
        Route::get('editar/{var}', [CursoController::class, 'edit']);
        Route::post('editar/{var}', [CursoController::class, 'update']);
        Route::get('apagar', [CursoController::class, 'destroy']);
        Route::get('curso/{var}', [CursoController::class, 'show']);
        Route::get('curso/modulos/{var}', [CursoController::class, 'qndeModulos']);
        Route::get('media-vagas/{id}/{tipo}', [CursoController::class, 'mediaVagas']);
        
        // Disciplinas
        Route::prefix('disciplinas')->group(function(){
            Route::get('vincular/{var}', [DisciplinaController::class, 'editDisciplinasAoCurso']);
            Route::post('vincular/{var}', [DisciplinaController::class, 'storeDisciplinasAoCurso']);
            Route::get('grade/{var}', [DisciplinaController::class, 'disciplinasDoCurso']);
            Route::get('grade/{curso}/{str}', [DisciplinaController::class, 'disciplinasDoCurso']);
            Route::get('/', [DisciplinaController::class, 'index']);
            Route::get('cadastrar', [DisciplinaController::class, 'create']);
            Route::post('cadastrar', [DisciplinaController::class, 'store']);
            Route::get('pedagogico/editardisciplina/{var}', [DisciplinaController::class, 'edit']);
            Route::get('disciplina/{var}', [DisciplinaController::class, 'show']);
            Route::get('editar/{var}', [DisciplinaController::class, 'edit']);
            Route::post('editar/{var}', [DisciplinaController::class, 'update']);
            Route::get('apagar', [DisciplinaController::class, 'destroy']);
        });
        
        // Requisitos
        Route::prefix('requisitos')->group(function(){
            Route::get('/', [RequisitosController::class, 'index']);
            Route::get('cadastrar', [RequisitosController::class, 'create']);
            Route::post('cadastrar', [RequisitosController::class, 'store']);
            Route::get('apagar/{itens}', [RequisitosController::class, 'destroy']);
            Route::get('requisitosdocurso/{var}', [RequisitosController::class, 'editRequisitosAoCurso']);
            Route::post('requisitosdocurso/{var}', [RequisitosController::class, 'storeRequisitosAoCurso']);
        });
    });
    
    // Planos de Ensino
    Route::prefix('planos-ensino')->group(function () {
        Route::get('/', [PlanoEnsinoController::class, 'index']);
        Route::get('cadastrar', [PlanoEnsinoController::class, 'create']);
        Route::post('cadastrar', [PlanoEnsinoController::class, 'store']);
        Route::get('editar/{plano}', [PlanoEnsinoController::class, 'edit']);
        Route::post('editar/{plano}', [PlanoEnsinoController::class, 'update']);
        Route::get('apagar/{planos}', [PlanoEnsinoController::class, 'destroy']);
        Route::post('apagar/{planos}', [PlanoEnsinoController::class, 'detete']);
    });
    
    // ========== GESTÃO DE PESSOAS ==========
    
    Route::prefix('pessoa')->group(function(){
        // Pessoas
        Route::post('registrar-contato', [ContatoController::class, 'registrar']);
        Route::get('contato-whatsapp', [ContatoController::class, 'enviarWhats']);
        Route::get('resetar-senha-perfil/{id}', [PerfilAuthController::class, 'resetarSenha']);
        Route::get('listar', [PessoaController::class, 'listarTodos']);
        Route::post('listar', [PessoaController::class, 'procurarPessoasAjax']);
        Route::get('cadastrar', [PessoaController::class, 'create'])->name('pessoa.cadastrar');
        Route::post('cadastrar', [PessoaController::class, 'gravarPessoa']);
        
        Route::middleware('liberar.recurso:18')->get('mostrar', [PessoaController::class, 'listarTodos']);
        
        Route::get('mostrar/{var}', [PessoaController::class, 'mostrar']);
        Route::get('buscarapida/{var}', [PessoaController::class, 'liveSearchPessoa']);
        Route::get('apagar-atributo/{var}', [PessoaController::class, 'apagarAtributo']);
        Route::get('apagar-pendencia/{var}', [PessoaController::class, 'apagarPendencia']);
        Route::POST('inserir-dado-clinico', [PessoaDadosClinicosController::class, 'store']);
        Route::delete('apagar-dado-clinico/{id}', [PessoaDadosClinicosController::class, 'delete']);
        
        // Atestados
        Route::prefix('atestado')->group(function(){
            Route::get('mostrar/{atestado}', [AtestadoController::class, 'visualizar']);
            Route::get('cadastrar/{pessoa}', [AtestadoController::class, 'novo']);
            Route::post('cadastrar/{pessoa}', [AtestadoController::class, 'create']);
            Route::get('arquivar/{atestado}', [AtestadoController::class, 'apagar']);
            Route::get('editar/{atestado}', [AtestadoController::class, 'editar']);
            Route::post('editar/{atestado}', [AtestadoController::class, 'update']);
            Route::get('listar', [AtestadoController::class, 'listar']);
            Route::get('analisar/{atestado}', [AtestadoController::class, 'analisar_view']);
            Route::post('analisar/{atestado}', [AtestadoController::class, 'analisar']);
        });
        
        // Justificativa de Ausência
        Route::prefix('justificativa-ausencia')->group(function(){
            Route::get('/{pessoa?}', [JustificativaAusenciaController::class, 'index']);
            Route::post('/{pessoa}', [JustificativaAusenciaController::class, 'store']);
            Route::get('apagar/{id}', [JustificativaAusenciaController::class, 'delete']);
        });
        
        // Bolsas
        Route::middleware('liberar.recurso:18')->prefix('bolsa')->group(function(){
            Route::get('cadastrar/{pessoa}', [BolsaController::class, 'nova']);
            Route::post('cadastrar/{pessoa}', [BolsaController::class, 'gravar']);
            Route::get('imprimir/{bolsa}', [BolsaController::class, 'imprimir']);
            Route::get('upload/{bolsa}', [BolsaController::class, 'uploadForm']);
            Route::post('upload/{bolsa}', [UploadController::class, 'uploadBolsaExec']);
            Route::get('parecer/{bolsa}', [BolsaController::class, 'uploadParecerForm']);
            Route::post('parecer/{bolsa}', [UploadController::class, 'uploadParecerExec']);
        });
        
        // Dependentes
        Route::get('adicionardependente/{var}', [PessoaController::class, 'addDependente_view']);
        Route::get('gravardependente/{pessoa}/{dependente}', [PessoaController::class, 'addDependente_exec']);
        Route::get('removervinculo/{var}', [PessoaController::class, 'remVinculo_exec']);
        Route::get('adicionarresponsavel/{var}', [PessoaController::class, 'addResponsavel_view']);
        Route::post('adicionarresponsavel/{var}', [PessoaController::class, 'addResponsavel_exec']);
        Route::get('removerdependente/{var}', [PessoaController::class, 'remResponsavel_exec']);
        Route::get('buscarendereco/{var}', [PessoaController::class, 'buscarEndereco']);
        
        // Edição de Dados
        Route::prefix('editar')->group(function(){
            Route::get('geral/{id}', [PessoaController::class, 'editarGeral_view']);
            Route::post('geral/{var}', [PessoaController::class, 'editarGeral_exec']);
            Route::get('contato/{var}', [PessoaController::class, 'editarContato_view']);
            Route::post('contato/{var}', [PessoaController::class, 'editarContato_exec']);
            Route::get('dadosclinicos/{var}', [PessoaDadosClinicosController::class, 'editarDadosClinicos_view']);
            Route::post('dadosclinicos/{var}', [PessoaDadosClinicosController::class, 'editarDadosClinicos_exec']);
            Route::get('observacoes/{var}', [PessoaDadosGeraisController::class, 'editarObservacoes_view']);
            Route::post('observacoes/{var}', [PessoaDadosGeraisController::class, 'editarObservacoes_exec']);
        });

        Route::prefix('foto-perfil')->group(function(){
            Route::get('{pessoa}', [PessoaController::class, 'alterarFotoPerfil']);
            Route::post('{pessoa}', [PessoaController::class, 'gravarFotoPerfil']);
        });
        
        // Dados Acadêmicos
        Route::get('matriculas', [MatriculaController::class, 'listarPorPessoa']);
        Route::get('registrar-email-fesc/{pessoa}/{endereco}', [PessoaDadosAcademicosController::class, 'registrarEmailFesc']);
        Route::get('apagar-email-fesc/{id}', [PessoaDadosAcademicosController::class, 'apagarEmailFesc']);
        Route::get('inscrever-equipe-teams/{pessoa}/{turma}', [PessoaDadosAcademicosController::class, 'inscreverTeams']);
        Route::get('remover-equipe-teams/{id}', [PessoaDadosAcademicosController::class, 'removerTeams']);
        Route::get('profile', function(){ return view('pessoa.profile'); });
    });
    
    // ========== FINANCEIRO ==========
    
    Route::prefix('financeiro')->group(function(){
        Route::middleware('liberar.recurso:14')->get('/', [painelController::class, 'financeiro']);
        Route::get('limpar-debitos', [BoletoController::class, 'limparDebitos']);
        
        // Cobrança
        Route::prefix('cobranca')->group(function(){
            Route::get('cartas', [CobrancaController::class, 'cartas']);
        });
        
        // Dívida Ativa
        Route::prefix('divida-ativa')->group(function(){
            Route::get('/', [DividaAtivaController::class, 'index']);
        });
        
        // Lançamentos
        Route::prefix('lancamentos')->group(function(){
            Route::get('home', function(){ return view('financeiro.lancamentos.home'); });
            Route::get('listar-por-pessoa', [LancamentoController::class, 'listarPorPessoa']);
            Route::get('novo/{id}', [LancamentoController::class, 'novo']);
            Route::post('novo/{id}', [LancamentoController::class, 'create']);
            Route::get('gerar-individual/{pessoa}', [LancamentoController::class, 'gerarLancamentosPorPessoa']);
            Route::get('cancelar/{lancamento}', [LancamentoController::class, 'cancelar']);
            Route::get('excluir/{lancamento}', [LancamentoController::class, 'excluir']);
            Route::get('excluir-abertos/{pessoa}', [LancamentoController::class, 'excluirAbertos']);
            Route::get('reativar/{lancamento}', [LancamentoController::class, 'reativar']);
            Route::get('relancar/{lancamento}', [LancamentoController::class, 'relancarParcela']);
            Route::get('editar/{lancamento}', [LancamentoController::class, 'editar']);
            Route::post('editar/{lancamento}', [LancamentoController::class, 'update']);
        });
        
        // Carnê
        Route::middleware('liberar.recurso:19')->prefix('carne')->group(function(){
            Route::get('gerar', function(){ return view('financeiro.carne.home');});
            Route::get('gerarBackground', [CarneController::class, 'gerarBG']);
            Route::get('fase1/{pessoa?}', [CarneController::class, 'carneFase1']);
            Route::get('fase2/{pessoa?}', [CarneController::class, 'carneFase2']);
            Route::get('fase3/{pessoa?}', [CarneController::class, 'carneFase3']);
            Route::get('fase4/{pessoa?}', [CarneController::class, 'carneFase4']);
            Route::get('fase5/{pessoa?}', [CarneController::class, 'carneFase5']);
            Route::get('fase6/{pessoa?}', [CarneController::class, 'carneFase6']);
            Route::get('fase7/{pessoa?}', [CarneController::class, 'carneFase7']);
            Route::get('reimpressao', [CarneController::class, 'reimpressao']);
        });
        
        // Boletos
        Route::prefix('boletos')->group(function(){
            Route::middleware('liberar.recurso:19')->get('home', function(){ return view('financeiro.boletos.home'); });
            Route::get('editar/{id}', [BoletoController::class, 'editar']);
            Route::post('editar/{id}', [BoletoController::class, 'update']);
            Route::get('imprimir/{id}', [BoletoController::class, 'imprimir']);
            Route::get('registrar-pelo-site/{id}', [BoletoController::class, 'registrarPeloSite']);
            Route::get('imprimir-carne/{pessoa}', [CarneController::class, 'imprimirCarne']);
            Route::get('registrar/{id}', [IntegracaoBBController::class, 'listarBoletos']);
            Route::get('divida-ativa', [DividaAtivaController::class, 'gerarDividaAtiva']);
            Route::get('listar-por-pessoa', [BoletoController::class, 'listarPorPessoa']);
            Route::get('informacoes/{id}', [BoletoController::class, 'historico']);
            Route::get('imprimir-laravel-boleto/{ids}', [BoletoController::class, 'imprimirLaravelBoleto']);
            Route::get('cancelar/{id}', [BoletoController::class, 'cancelarView']);
            Route::get('registrar/{ids}', [IntegracaoBBController::class, 'registrarBoletos']);
            Route::get('gerar-carne/{pessoa}', [CarneController::class, 'gerarCarneIndividual']);
            
            Route::middleware('liberar.recurso:23')->group(function(){
                Route::post('cancelar/{id}', [BoletoController::class, 'cancelar']);
                Route::get('cancelar-todos/{id}', [BoletoController::class, 'cancelarTodosVw']);
                Route::post('cancelar-todos/{id}', [BoletoController::class, 'cancelarTodos']);
            });
            
            Route::get('reativar/{id}', [BoletoController::class, 'reativar']);
            Route::get('gerar-individual/{pessoa}', [BoletoController::class, 'cadastarIndividualmente']);
            Route::get('gerar', [BoletoController::class, 'gerar']);
            
            Route::middleware('liberar.recurso:19')->group(function(){
                Route::get('gerar-boletos', [BoletoController::class, 'cadastrar']);
                Route::get('confirmar-impressao', [BoletoController::class, 'confirmarImpressao']);
            });
            
            Route::get('imprimir-lote', [BoletoController::class, 'imprimirLote']);
            Route::get('novo/{pesssoa}', [BoletoController::class, 'novo']);
            Route::post('novo/{pesssoa}', [BoletoController::class, 'create']);
            Route::get('/lote-csv', [BoletoController::class, 'gerarArquivoCSV']);
            Route::get('corrigir2022', [BoletoController::class, 'corrigir2022']);
            
            // Remessa
            Route::prefix('remessa')->group(function(){
                Route::get('home', function(){ return view('financeiro.remessa.home'); });
                Route::get('gerar', [RemessaController::class, 'gerarRemessa']);
                Route::get('download/{file}', [RemessaController::class, 'downloadRemessa']);
                Route::get('listar-arquivos', [RemessaController::class, 'listarRemessas']);
            });
            
            // Retorno
            Route::prefix('retorno')->group(function(){
                Route::get('home', function(){ return view('financeiro.retorno.home'); });
                Route::get('upload', function(){ return view('financeiro.retorno.upload'); });
                Route::post('upload', [UploadController::class, 'uploadRetornos']);
                Route::get('arquivos', [RetornoController::class, 'listarRetornos']);
                Route::get('analisar/{arquivo}', [RetornoController::class, 'analisarArquivo']);
                Route::get('processar/{arquivo}', [RetornoController::class, 'processarArquivo']);
                Route::get('reprocessar/{arquivo}', [RetornoController::class, 'reProcessarArquivo']);
                Route::get('marcar-processado/{arquivo}', [RetornoController::class, 'marcarProcessado']);
                Route::get('marcar-erro/{arquivo}', [RetornoController::class, 'marcarErro']);
                Route::get('com-erro', [RetornoController::class, 'listarRetornosComErro']);
                Route::get('processados', [RetornoController::class, 'listarRetornosProcessados']);
                Route::get('original/{arquivo}', [RetornoController::class, 'retornarOriginal']);
            });
        });
        
        // Relatórios Financeiros
        Route::prefix('relatorios')->group(function(){
            Route::get('boletos', [BoletoController::class, 'relatorioBoletosAbertos']);
            Route::get('boletos/{ativos}', [BoletoController::class, 'relatorioBoletosAbertos']);
            Route::get('/cobranca-xls', [CobrancaController::class, 'relatorioDevedoresXls']);
            Route::get('/cobranca-xls/{ativos}', [CobrancaController::class, 'relatorioDevedoresXls']);
            Route::get('/cobranca-sms', [CobrancaController::class, 'relatorioDevedoresSms']);
            Route::get('/cobranca-sms/{ativos}', [CobrancaController::class, 'relatorioDevedoresSms']);
        });
    });
    
    // ========== SETORES ESPECÍFICOS ==========
    
    // Desenvolvimento
    Route::middleware('liberar.recurso:22')->prefix('dev')->group(function(){
        Route::get('/', [painelController::class, 'indexDev']);
        Route::get('use-as/{id}', [LoginController::class, 'useAs']);
        Route::get('teste-pix', [PixController::class, 'testePix']);
        Route::get('testar-classe/', [PessoaDadosGeraisController::class, 'rastrearDuplicados']);
        Route::post('testar-classe', [painelController::class, 'testarClassePost']);
        Route::get('/bolsa/gerador', [BolsaController::class, 'gerador']);
        Route::get('/corrigir-boletos', [BoletoController::class, 'corrigirBoletosSemParcelas']);
        Route::get('ajusteBolsas', [BolsaController::class, 'ajusteBolsaSemMatricula']);
        Route::get('gerar-dias-nao-letivos', [DiaNaoLetivoController::class, 'cadastroAnual']);
        Route::get('importar-status-boletos', [painelController::class, 'importarStatusBoletos']);
        Route::get('add-recesso', [DiaNaoLetivoController::class, 'ViewAddRecesso']);
        Route::get('cadastrarValores', [ValorController::class, 'cadastrarValores']);
    });
    
    // Administrativo
    Route::middleware('liberar.recurso:12')->prefix('administrativo')->group(function(){
        Route::get('/', [painelController::class, 'administrativo']);
        Route::get('locais', [LocaisController::class, 'listar']);
        Route::get('locais/cadastrar', [LocaisController::class, 'cadastrar']);
        Route::post('locais/cadastrar', [LocaisController::class, 'store']);
        Route::get('locais/editar/{var}', [LocaisController::class, 'editar']);
        Route::post('locais/editar/{var}', [LocaisController::class, 'update']);
        Route::get('locais/apagar/{var}', [LocaisController::class, 'apagar']);
        Route::get('locais/salas/{id}', [SalaController::class, 'listarPorLocal']);
        Route::get('locais/salas-api/{id}', [SalaController::class, 'listarPorLocalApi']);
        Route::get('salas/cadastrar/{id}', [SalaController::class, 'cadastrar']);
        Route::post('salas/cadastrar/{id}', [SalaController::class, 'store']);
        Route::get('salas/alterar/{id}', [SalaController::class, 'editar']);
        Route::post('salas/alterar/{id}', [SalaController::class, 'update']);
    });
    
    // Agendamento de Salas
    Route::middleware('liberar.recurso:12')->prefix('agendamento-salas')->group(function(){
        Route::get('/', [SalaAgendamentoController::class, 'agendamento']);
    });
    
    // Gestão Pessoal
    Route::middleware('liberar.recurso:15')->prefix('gestaopessoal')->group(function(){
        Route::get('/', [painelController::class, 'atendimentoPessoal']);
        Route::get('atendimento', [painelController::class, 'gestaoPessoal']);
        Route::get('atender/', [painelController::class, 'atendimentoPessoalPara']);
        Route::get('atender/{var}', [painelController::class, 'atendimentoPessoalPara']);
        Route::get('funcionarios', [PessoaDadosAdminController::class, 'listarFuncionarios']);
        Route::get('remover-relacao/{id}', [PessoaDadosAdminController::class, 'excluir']);
        Route::get('relacaoinstitucional/{var}', [PessoaDadosAdminController::class, 'relacaoInstitucional_view']);
        Route::post('relacaoinstitucional/{var}', [PessoaDadosAdminController::class, 'relacaoInstitucional_exec']);
        Route::get('vincular-programa/{pessoa}/{programa}', [PessoaDadosAdminController::class, 'vincularPrograma']);
        Route::get('desvincular-programa/{pessoa}/{programa}', [PessoaDadosAdminController::class, 'desvincularPrograma']);
        Route::get('definir-carga/{pessoa}/{valor}', [PessoaDadosAdminController::class, 'definirCarga']);
        Route::get('remover-carga/{id}', [PessoaDadosAdminController::class, 'removerCarga']);
    });
    
    // Bolsas
    Route::middleware('liberar.recurso:21')->prefix('bolsas')->group(function(){
        Route::get('liberacao', [BolsaController::class, 'listar']);
        Route::get('/status/{status}/{bolsas}', [BolsaController::class, 'alterarStatus']);
        Route::get('analisar/{bolsa}', [BolsaController::class, 'analisar']);
        Route::post('analisar/{bolsa}', [BolsaController::class, 'gravarAnalise']);
        Route::get('desvincular/{matricula}/{bolsa}', [BolsaController::class, 'desvincular']);
    });
    
    // Jurídico
    Route::prefix('juridico')->group(function(){
        Route::get('/', [painelController::class, 'juridico']);
        
        // Documentos
        Route::middleware('liberar.recurso:16')->prefix('documentos')->group(function(){
            Route::get('/', [DocumentoController::class, 'index']);
            Route::get('cadastrar', [DocumentoController::class, 'cadastrar']);
            Route::get('apagar/{var}', [DocumentoController::class, 'apagar']);
            Route::get('editar/{var}', [DocumentoController::class, 'editar']);
            Route::post('cadastrar', [DocumentoController::class, 'store']);
        });
    });
    
    // Pedagógico
    Route::middleware('liberar.recurso:17')->prefix('pedagogico')->group(function(){
        Route::get('/', [painelController::class, 'pedagogico']);
        Route::get('novo', [painelController::class, 'novoPedagogico']);
        
        // Turmas
        Route::prefix('turmas')->group(function(){
            Route::get('cadastrar', [TurmaController::class, 'create']);
            Route::post('cadastrar', [TurmaController::class, 'store']);
            Route::post('recadastrar', [TurmaController::class, 'storeRecadastro']);
            Route::get('/', [TurmaController::class, 'index']);
            Route::get('/alterar/{acao}/{turmas}', [TurmaController::class, 'acaolote']);
            Route::get('listar', [TurmaController::class, 'index']);
            Route::get('apagar/{var}', [TurmaController::class, 'destroy']);
            Route::get('editar/{var}', [TurmaController::class, 'edit']);
            Route::post('editar/{var}', [TurmaController::class, 'update']);
            Route::get('status/{status}/{turma}', [TurmaController::class, 'status']);
            Route::get('turmasjson', [TurmaController::class, 'turmasJSON']);
            Route::get('inscritos/{turma}', [InscricaoController::class, 'verInscritos']);
            Route::get('lista/{id}', [painelController::class, 'chamada']);
            Route::get('importar', function(){ return view('pedagogico.turma.upload');});
            Route::post('importar', [TurmaController::class, 'uploadImportaTurma']);
            Route::post('processar-importacao', [TurmaController::class, 'processarImportacao']);
            Route::get('expiradas', [TurmaController::class, 'processarTurmasExpiradas']);
            Route::get('modificar-requisitos/{id}', [RequisitosController::class, 'editRequisitosTurma']);
            Route::post('turmas-requisitos', [RequisitosController::class, 'editRequisitosTurma']);
            Route::post('modificar-requisitos/{id}', [RequisitosController::class, 'storeRequisitosTurma']);
            Route::get('atualizar-inscritos', [TurmaController::class, 'atualizarInscritos']);
        });
    });
    
    // Secretaria
    Route::middleware('liberar.recurso:18')->prefix('secretaria')->group(function(){	
        Route::get('/', [painelController::class, 'secretaria'])->name('secretaria');
        Route::get('analisar-matriculas', [MatriculaController::class, 'analiseFinanceira']);
        Route::get('pre-atendimento', [SecretariaController::class, 'iniciarAtendimento']);
        Route::post('pre-atendimento', [SecretariaController::class, 'buscaPessoaAtendimento']);
        Route::get('atendimento', [SecretariaController::class, 'atender']);
        Route::get('atender', [SecretariaController::class, 'atender'])->name('secretaria.atender');
        Route::get('atender/{var}', [SecretariaController::class, 'atender']);
        Route::get('profile/{var}', [SecretariaController::class, 'profile']);
        Route::get('processar-documentos', [SecretariaController::class, 'processarDocumentos']);
        Route::get('turmas', [TurmaController::class, 'listarSecretaria']);
        Route::get('turmas-disponiveis/{pessoa}/{turmas}/{busca?}', [TurmaController::class, 'turmasDisponiveis']);
        Route::get('turmas-escolhidas/{turmas}/', [TurmaController::class, 'turmasEscolhidas']);
        Route::get('upload', [SecretariaController::class, 'uploadGlobal_vw']);
        Route::post('upload', [UploadController::class, 'enviarDocumentosSecretaria']);
        Route::get('frequencia/{turma}', [FrequenciaController::class, 'listaChamada']);
        Route::get('alunos', [SecretariaController::class, 'alunos']);
        Route::get('alunos-cancelados', [SecretariaController::class, 'alunosCancelados']);
        Route::get('listar-pendencias', [PessoaDadosAdminController::class, 'relatorioPendentes']);
        
        // Matrículas
        Route::prefix('matricula')->group(function(){
            Route::get('/{ids}', [SecretariaController::class, 'viewMatricula']);
            Route::get('/nova/{pessoa}', [InscricaoController::class, 'novaInscricao']);
            Route::get('/upload-termo-lote', function(){ return view('secretaria.matricula.upload-termos-lote'); });
            Route::post('/upload-termo-lote', [UploadController::class, 'uploadTermosLote']);
            Route::get('/upload-termo/{matricula}', [MatriculaController::class, 'uploadTermo_vw']);
            Route::post('/upload-termo/{matricula}', [UploadController::class, 'uploadTermo']);
            Route::get('/upload-termo-cancelamento/{matricula}', [MatriculaController::class, 'uploadCancelamentoMatricula_vw']);
            Route::post('/upload-termo-cancelamento/{matricula}', [UploadController::class, 'uploadCancelamentoMatricula']);
            Route::get('/uploadglobal/{tipo}/{operacao}/{qnde}/{valor}', [MatriculaController::class, 'uploadGlobal_vw']);
            Route::post('/uploadglobal/{tipo}/{operacao}/{qnde}/{valor}', [MatriculaController::class, 'uploadGlobal']);
            Route::get('renovar/{pessoa}', [MatriculaController::class, 'renovar_vw']);
            Route::post('renovar/{pessoa}', [MatriculaController::class, 'renovar']);
            Route::get('duplicar/{matricula}', [MatriculaController::class, 'duplicar']);
            Route::post('nova/confirmacao', [InscricaoController::class, 'confirmacaoAtividades']);
            Route::post('nova/gravar', [MatriculaController::class, 'gravar']);
            Route::get('termo/{id}', [MatriculaController::class, 'termo']);
            Route::get('editar/{id}', [MatriculaController::class, 'editar']);
            Route::post('editar/{id}', [MatriculaController::class, 'update']);
            Route::get('declaracao/{id}', [MatriculaController::class, 'declaracao']);
            Route::get('cancelar/{id}', [MatriculaController::class, 'viewCancelarMatricula']);
            Route::post('cancelar/{id}', [MatriculaController::class, 'cancelarMatricula']);
            Route::get('imprimir-cancelamento/{matricula}', [MatriculaController::class, 'imprimirCancelamento']);
            Route::get('reativar/{id}', [MatriculaController::class, 'reativarMatricula']);
            Route::get('atualizar/{id}', [MatriculaController::class, 'atualizar']);
            Route::get('cancelamento', [MatriculaController::class, 'regularizarCancelamentos']);
            
            // Inscrições
            Route::prefix('inscricao')->group(function(){
                Route::get('editar/{id}', [InscricaoController::class, 'editar']);
                Route::post('editar/{id}', [InscricaoController::class, 'update']);
                Route::get('apagar/{id}', [InscricaoController::class, 'viewCancelar']);
                Route::post('apagar/{id}', [InscricaoController::class, 'cancelar']);
                Route::get('reativar/{id}', [InscricaoController::class, 'reativar']);
                Route::get('trocar/{id}', [InscricaoController::class, 'trocarView']);
                Route::post('trocar/{id}', [InscricaoController::class, 'trocarExec']);
                Route::get('imprimir/cancelamento/{inscricao}', [InscricaoController::class, 'imprimirCancelamento']);
                Route::get('imprimir/transferencia/{inscricao}', [TransferenciaController::class, 'imprimir']);
            });
        });
        
        Route::middleware('liberar.recurso:20')->get('ativar_matriculas_em_espera', [MatriculaController::class, 'ativarEmEspera']);
        Route::get('visualizar-ficha-tecnica/{id}', [FichaTecnicaController::class, 'visualizar']);
    });
    
    // ========== RELATÓRIOS ==========
    
    Route::prefix('relatorios')->group(function(){
        Route::get('alunos', [RelatorioController::class, 'numeroAlunos']);
        Route::get('turmas', [RelatorioController::class, 'turmas']);
        Route::get('planilha-turmas', [RelatorioController::class, 'exportarTurmas']);
        Route::get('dados-turmas/{turmas}', [RelatorioController::class, 'dadosTurmas']);
        Route::get('matriculas/{programa}', [RelatorioController::class, 'matriculasPrograma']);
        Route::get('inscricoes', [RelatorioController::class, 'inscricoes']);
        Route::get('alunos-turmas', [RelatorioController::class, 'alunosTurmasExport']);
        Route::get('alunos-turmas-sms', [RelatorioController::class, 'alunosTurmasExportSMS']);
        Route::get('faixasuati', [RelatorioController::class, 'matriculasUati']);
        Route::get('alunos-posto', [RelatorioController::class, 'alunosPorUnidade']);
        Route::get('bolsas-fpm', [RelatorioController::class, 'bolsasFuncionariosMunicipais']);
        Route::get('bolsas', [RelatorioController::class, 'bolsas']);
        Route::get('tce-alunos/{ano?}', [RelatorioController::class, 'tceAlunos']);
        Route::get('tce-educadores/{ano?}', [JornadaDocentes::class, 'relatorioGeral']);
        Route::get('tce-turmas/{ano?}/', [RelatorioController::class, 'tceTurmas']);
        Route::get('tce-turmas-alunos/{ano?}', [RelatorioController::class, 'tceTurmasAlunos']);
        Route::get('tce-vagas/{ano?}', [RelatorioController::class, 'tceVagas']);
        Route::get('alunos-conselho/{ano?}', [RelatorioController::class, 'alunosConselho']);
        Route::get('bolsistas-com-3-faltas', [RelatorioController::class, 'bolsistasComTresFaltas']);
        Route::get('celulares', [PessoaController::class, 'relatorioCelulares']);
        Route::get('receita-anual-programa/{ano}/{mes?}', [ReceitaAnualReportController::class, 'receitaPorPrograma']);
        Route::get('receita-curso/{cursos}/{ano}/{mes?}', [ReceitaAnualReportController::class, 'receitaPorCurso']);
        Route::get('carga-docentes/{ano?}', [JornadaDocentes::class, 'relatorioGeral']);
        Route::get('salas', [SalaController::class, 'relatorioOcupacao']);
        Route::get('jornadas-por-programa/{programa}', [JornadaPrograma::class, 'index']);
        Route::get('uso-livre', [UsoLivreController::class, 'relatorio']);
        Route::get('horarios-htp/{programas}', [JornadaHTP::class, 'index']);
        Route::get('conteudo-aulas/{turmas}/{datas?}', [AulaDadoController::class, 'relatorioConteudo']);
        Route::get('conteudo-ocorrencias/{turmas}/{datas?}', [AulaDadoController::class, 'relatorioConteudo']);
    });
    
    // ========== DOCENTES ==========
    
    Route::middleware('liberar.recurso:13')->prefix('docentes')->group(function(){
        Route::get('docente/{id?}/{semestre?}', [painelController::class, 'docentes']);
        Route::get('turmas-professor', [TurmaController::class, 'listarProfessores']);
        Route::post('turmas-professor', [TurmaController::class, 'turmasProfessor']);
        Route::get('jornadas/{educador?}', [JornadaController::class, 'modalJornadaDocente']);
        Route::get('cargas/{educador?}', [PessoaDadosJornadasController::class, 'modalCargaDocente']);
        
        // Frequência
        Route::prefix('frequencia')->group(function(){
            Route::get('listar/{turma}/{datas?}', [FrequenciaController::class, 'listaChamada']);
            Route::get('nova-aula/{turma}/{aula?}', [FrequenciaController::class, 'novaChamada_view']);
            Route::post('nova-aula/{turma}/{aula?}', [FrequenciaController::class, 'novaChamada_exec']);
            Route::get('editar-aula/{aula}', [FrequenciaController::class, 'editarChamada_view']);
            Route::post('editar-aula/{aula}', [FrequenciaController::class, 'editarChamada_exec']);
            Route::get('preencher/{aula}', [FrequenciaController::class, 'preencherChamada_view']);
            Route::post('preencher/{aula}', [FrequenciaController::class, 'preencherChamada_exec']);
            Route::get('apagar-aula/{aula}', [AulaController::class, 'apagarAula']);
            Route::get('conteudos/{turma}', [AulaDadoController::class, 'editarConteudo_view']);
            Route::post('conteudos/{turma}', [AulaDadoController::class, 'editarConteudo_exec']);
        });
    });
    
    // Jornada
    Route::middleware('liberar.recurso:13')->prefix('jornada')->group(function(){
        Route::post('cadastrar', [JornadaController::class, 'cadastrar']);
        Route::post('excluir', [JornadaController::class, 'excluir']);
        Route::post('encerrar', [JornadaController::class, 'encerrar']);
    });
    
    Route::get('chamada/{id}/{pg}/{url}/{hide?}', [TurmaController::class, 'getChamada']);
    Route::get('plano/{professor}/{tipo}/{curso}', [TurmaController::class, 'getPlano']);
    
    // ========== ADMINISTRAÇÃO ==========
    
    Route::middleware('liberar.recurso:15')->prefix('admin')->group(function(){
        Route::middleware('liberar.recurso:8')->get('credenciais/{var}', [LoginController::class, 'credenciais_view']);
        Route::middleware('liberar.recurso:8')->post('credenciais/{var}', [LoginController::class, 'credenciais_exec']);
        Route::middleware('liberar.recurso:10')->get('listarusuarios', [LoginController::class, 'listarUsuarios_view']);
        Route::middleware('liberar.recurso:10')->get('listarusuarios/{var}', [LoginController::class, 'listarUsuarios_view']);
        Route::middleware('liberar.recurso:10')->post('listarusuarios/{var}', [LoginController::class, 'listarUsuarios_action']);
        Route::get('alterar/{acao}/{itens}', [LoginController::class, 'alterar']);
        Route::get('/turmascursosnavka', [painelController::class, 'verTurmasAnterioresCursos']);
        Route::post('/turmascursosnavka', [painelController::class, 'gravarMigracao']);
        Route::get('/turmasaulasnavka', [painelController::class, 'verTurmasAnterioresAulas']);
        Route::get('importarLocais', [painelController::class, 'importarLocais']);
        Route::get('atualizar-inscritos', [TurmaController::class, 'atualizarInscritos']);
        Route::get('inscricoes', [InscricaoController::class, 'incricoesPorPosto']);
    });
    
    // ========== OUTRAS ROTAS ==========
    
    Route::get('cobranca-automatica', [CobrancaController::class, 'cobrancaAutomatica']);
    Route::get('/atestado/{id}', [painelController::class, 'index']);
    Route::get('lista/{id}', [TurmaController::class, 'impressao']);
    Route::get('listas/{id}', [TurmaController::class, 'impressaoMultipla']);
    Route::get('frequencia/{turma}', [TurmaController::class, 'frequencia']);
    Route::get('chamadas', [FrequenciaController::class, 'index']);
    Route::get('chamadas/{id}/{semestre?}', [FrequenciaController::class, 'index']);
    
    // Agendamento
    Route::prefix('agendamento')->group(function(){
        Route::get('/{data?}', [AgendaAtendimentoController::class, 'index']);
        Route::post('/{data?}', [AgendaAtendimentoController::class, 'gravar']);
        Route::get('alterar/{id}/{status}', [AgendaAtendimentoController::class, 'alterarStatus']);
    });
    
    // Atestados
    Route::prefix('atestados')->group(function(){
        Route::get('/', [AtestadoController::class, 'analiseAtestados']);
        Route::get('verificador-diario', [AtestadoController::class, 'verificadorDiario']);
    });
    
    // Boletos
    Route::prefix('boletos')->group(function(){
        Route::get('/', [BoletoController::class, 'painel']);
    });
    
    // Integração BB
    Route::prefix('BB')->group(function(){
        Route::get('testar', [IntegracaoBBController::class, 'testar']);
        Route::get('lisboletos', [IntegracaoBBController::class, 'listarBoletos']);
        Route::post('registrar-boletos', [IntegracaoBBController::class, 'registroBoletosLote']);
        Route::get('boletos/{id}', [IntegracaoBBController::class, 'detalharBoleto']);
        Route::get('boletos/{id}/registrar', [IntegracaoBBController::class, 'viewRegistrarBoleto']);
        Route::post('boletos/{id}/registrar', [IntegracaoBBController::class, 'registrarBoleto']);
        Route::get('boletos/{id}/pix', [IntegracaoBBController::class, 'consultarPixBoleto']);
        Route::post('boletos/{id}/alterar', [IntegracaoBBController::class, 'alterarBoleto']);
        Route::get('boletos/{id}/baixar', [IntegracaoBBController::class, 'viewBaixarBoleto']);
        Route::post('boletos/{id}/baixar', [IntegracaoBBController::class, 'baixarBoleto']);
        Route::get('boletos/{id}/cancelar-pix', [IntegracaoBBController::class, 'cancelarPixBoleto']);
        Route::get('boletos/{id}/gerar-pix', [IntegracaoBBController::class, 'gerarPixBoleto']);
        Route::get('baixar-cancelamentos', [IntegracaoBBController::class, 'baixarCancelamentos']);
        Route::get('sincronizar', [IntegracaoBBController::class, 'sincronizarDados']);
    });
    
    // Uso Livre
    Route::prefix('uso-livre')->group(function(){
        Route::get('/', [UsoLivreController::class, 'index']);
        Route::post('/', [UsoLivreController::class, 'store']);
        Route::post('/concluir', [UsoLivreController::class, 'concluir']);
        Route::get('/excluir/{var}', [UsoLivreController::class, 'excluir']);
    });
    
    // Jornadas
    Route::middleware('liberar.recurso:17')->prefix('jornadas')->group(function(){
        Route::get('index-modal/{p?}', [JornadaController::class, 'indexModal']);
        Route::get('/{id}', [JornadaController::class, 'index']);
        Route::get('/{id}/cadastrar', [JornadaController::class, 'cadastrar']);
        Route::post('/{id}/cadastrar', [JornadaController::class, 'store']);
        Route::post('/{id}/excluir', [JornadaController::class, 'excluir']);
        Route::get('/{docente}/editar/{jornada}', [JornadaController::class, 'editar']);
        Route::post('/{docente}/editar/{jornada}', [JornadaController::class, 'update']);
    });
    
    // Fichas Técnicas
    Route::middleware('liberar.recurso:29')->prefix('fichas')->group(function(){
        Route::get('/', [FichaTecnicaController::class, 'index']);
        Route::get('cadastrar', [FichaTecnicaController::class, 'cadastrar']);
        Route::post('cadastrar', [FichaTecnicaController::class, 'gravar']);
        Route::get('visualizar/{id}', [FichaTecnicaController::class, 'visualizar']);
        Route::get('imprimir/{id}', [FichaTecnicaController::class, 'imprimir']);
        Route::get('editar/{id}', [FichaTecnicaController::class, 'editar']);
        Route::post('editar/{id}', [FichaTecnicaController::class, 'update']);
        Route::post('excluir', [FichaTecnicaController::class, 'excluir']);
        Route::get('pesquisa', [FichaTecnicaController::class, 'pesquisar']);
        Route::get('copiar/{id}', [FichaTecnicaController::class, 'copiar']);
        Route::post('encaminhar', [FichaTecnicaController::class, 'encaminhar']);
        Route::get('exportar', [FichaTecnicaController::class, 'exportar']);
    });
    
    // Carga Horária
    Route::prefix('carga-horaria')->group(function(){
        Route::get('importar', [PessoaDadosJornadasController::class, 'importar']);
        Route::get('cadastrar/{pessoa}', [PessoaDadosJornadasController::class, 'cadastrar']);
        Route::post('cadastrar/{pessoa}', [PessoaDadosJornadasController::class, 'store']);
        Route::get('editar/{id}', [PessoaDadosJornadasController::class, 'editar']);
        Route::post('editar/{id}', [PessoaDadosJornadasController::class, 'update']);
        Route::post('excluir', [PessoaDadosJornadasController::class, 'excluir']);
    });
    
    // Tags
    Route::middleware('liberar.recurso:18')->prefix('tags')->group(function(){
        Route::get('/gerenciar', [TagController::class, 'gerenciar']);
        Route::get('/{pessoa?}', [TagController::class, 'index']);
        Route::get('/apagar/{id}', [TagController::class, 'apagar']);
        Route::get('/adiciona-livre-acesso/{id}', [TagController::class, 'addLivreAcesso']);
        Route::get('/remove-livre-acesso/{id}', [TagController::class, 'remLivreAcesso']);
        Route::post('/{pessoa}/criar', [TagController::class, 'criar']);
    });
    
    // Inscrições
    Route::middleware('liberar.recurso:18')->prefix('inscricoes')->group(function(){
        Route::post('importar-dados', [InscricaoController::class, 'importarDados']);
        
    });
    
    // HTP
    Route::prefix('htp')->group(function(){
        Route::get('.', [HtpController::class, 'index']);
        Route::get('novo', [HtpController::class, 'create']);
        Route::post('novo', [HtpController::class, 'store']);
        Route::get('editar/{id}', [HtpController::class, 'edit']);
        Route::post('editar/{id}', [HtpController::class, 'update']);
        Route::get('apagar/{id}', [HtpController::class, 'destroy']);
    });
    
    // Eventos
    Route::prefix('eventos')->group(function(){
        Route::get('cadastrar/{tipo?}', [EventoController::class, 'create']);
        Route::post('cadastrar/{tipo?}', [EventoController::class, 'store']);
        Route::get('/{data?}', [EventoController::class, 'index']);
    });
});//end middleware login

// ==================== SERVIÇOS/APIs ====================
Route::prefix('services')->group(function(){
    Route::get('professores', [WebServicesController::class, 'listaProfessores']);
    Route::get('chamada/{id}', [WebServicesController::class, 'apiChamada']);
    Route::get('turmas', [WebServicesController::class, 'apiTurmas']);
    Route::get('salas-api/{id}', [SalaController::class, 'listarPorLocalApi']);
    Route::get('salas-locaveis-api/{id}', [SalaController::class, 'listarLocaveisPorLocalApi']);
    Route::post('excluir-aulas', [AulaController::class, 'excluir']);
    Route::post('catracax', [CatracaController::class, 'importarPresenca']);
});

// ==================== ROTAS ESPECIAIS ====================
Route::get('alerta-covid', [painelController::class, 'alertaCovid']);
Route::get('cancelamento-covid', [BoletoController::class, 'cancelarCovid']);
Route::get('renova-login', [LoginController::class, 'sendNewPassword']);

// ==================== TRATAMENTO DE ERROS ====================
Route::get('403/{recurso?}', function(string $recurso){
   return view('errors.403')->with('recurso',$recurso);
})->name('403');

Route::get('404', function(){
   return view('errors.404');
})->name('404');

Route::get('503', function(){
   return view('errors.503');
});

Route::get('500', function(){
	return view('errors.500');
});

// ==================== ROTA ABOUT ====================
Route::get('about', function(){
   return 'Sistema de Gestão Educacional. Todos direitos reservados';
});
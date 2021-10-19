<?php
/*
  Descrição do Desafio:
    Você precisa realizar uma migração dos dados fictícios que estão na pasta <dados_sistema_legado> para a base da clínica fictícia MedicalChallenge.
    Para isso, você precisa:
      1. Instalar o MariaDB na sua máquina. Dica: Você pode utilizar Docker para isso;
      2. Restaurar o banco da clínica fictícia Medical Challenge: arquivo <medical_challenge_schema>;
      3. Migrar os dados do sistema legado fictício que estão na pasta <dados_sistema_legado>:
        a) Dica: você pode criar uma função para importar os arquivos do formato CSV para uma tabela em um banco temporário no seu MariaDB.
      4. Gerar um dump dos dados já migrados para o banco da clínica fictícia Medical Challenge.
*/

// Importação de Bibliotecas:
include "./lib.php";
include "./conection.php";

// Informações de Inicio da Migração:
echo "Início da Migração: " . dateNow() . ".\n\n";

//Abaixo cadastro de pacientes

$delimitador = ';';

// Abrir arquivo para leitura
$f = fopen('dados_sistema_legado/20210512_pacientes.csv', 'r');
if ($f) { 
  // Ler cabecalho do arquivo
  $cabecalho = fgetcsv($f, 0, $delimitador);
  $convNovo = [];
  // Enquanto nao terminar o arquivo
  while (!feof($f)) { 
    // Ler uma linha do arquivo
    $linha = fgetcsv($f, 0, $delimitador);
    
    if (!$linha) {
      continue;
    }

    // Montar registro com valores indexados pelo cabecalho
    $paciente = array_combine($cabecalho, $linha);        
    // foreach($pacientes as $paciente){
    // $convenio = $paciente[9];

    $idConvenio = NULL;
    $convenio = $paciente['convenio'];
    $convenioExiste = $connMedical->query("SELECT id from convenios where nome = '$convenio'");
    if($conv = $convenioExiste->fetch_assoc()){
      $idConvenio =  $conv['id'];
    }else{
      $connMedical->query("INSERT INTO convenios (nome, descricao) VALUES ('$convenio', 'Migrado')") or die(mysqli_error($connMedical));
      $idConvenio = $connMedical->insert_id;
    }
      
    $nomePaciente = $paciente['nome_paciente'];
    $sexo = $paciente['sexo_pac'];
    if ($sexo == "M"){
      $sexo = 'Masculino';
    }else{
      $sexo = "Feminino";
    }

    $nascArray = explode('/',$paciente['nasc_paciente']);
    $nascimento = $nascArray[2] . '-' . $nascArray[1] . '-' . $nascArray[0];
    $cpfPaciente = $paciente['cpf_paciente'];
    $rgPaciente = $paciente['rg_paciente'];
    $nomeConvenio = $paciente['convenio'];
    $codRef = $paciente['cod_paciente'];
    $pacienteExiste = $connMedical->query("SELECT id from pacientes where nome = '$nomePaciente'")->fetch_assoc();
    
    if (!$pacienteExiste['id']){
      $connMedical->query("INSERT INTO pacientes (nome, sexo, nascimento, cpf, rg, id_convenio, cod_referencia) VALUES ('$nomePaciente','$sexo','$nascimento','$cpfPaciente','$rgPaciente','$idConvenio','$codRef')") or die(mysqli_error($connMedical));
    }
  }

  fclose($f);
}

//Abaixo cadastro de procedimentos
// Abrir arquivo para leitura
$f = fopen('dados_sistema_legado/20210512_agendamentos.csv', 'r');
if ($f) { 
  // Ler cabecalho do arquivo
  $cabecalho = fgetcsv($f, 0, $delimitador);

  // Enquanto nao terminar o arquivo
  while (!feof($f)) { 
    // Ler uma linha do arquivo
    $linha = fgetcsv($f, 0, $delimitador);
    
    if (!$linha) {
      continue;
    }

    // Montar registro com valores indexados pelo cabecalho
    $agendamento = array_combine($cabecalho, $linha);

    //Abaixo cadastro de agendamento
    //Aqui ficou a dúvida sobre o nome dos médicos, pois iria ficar cadastro duplicado. Sendo assim, setei manualmente a id deles.
    $diaArray = explode('/', $agendamento['dia']);
    $diaAgendamento = $diaArray[2] . '-' .$diaArray[1] . '-' . $diaArray[0];
    $dtIni = $diaAgendamento . ' ' . $agendamento['hora_inicio'];
    $dtFim = $diaAgendamento . ' ' . $agendamento['hora_fim'];
    $descAgendamento = $agendamento['descricao'];
    $nomeConvenio = $agendamento['convenio'];
    $convenioQuery = $connMedical->query("SELECT id from convenios where nome = '$nomeConvenio'")->fetch_assoc();  
    $idConvenio = $convenioQuery['id'];
    $nomeProcedimento = $agendamento['procedimento'];
    $nomePaciente = $agendamento['paciente'];
    $pacienteQuery = $connMedical->query("SELECT id from pacientes where nome = '$nomePaciente'")->fetch_assoc();  
    $idPaciente = $pacienteQuery['id'];
    $idProfissional = NULL;
    $nomeMedico = $agendamento['medico'];
    $profissionalExiste = $connMedical->query("SELECT id from profissionais where nome = '$nomeMedico'");
    if($medico = $profissionalExiste->fetch_assoc()){
      $idProfissional = $medico['id'];
    }else{
      $connMedical->query("INSERT INTO profissionais (nome) VALUES ('$nomeMedico')") or die(mysqli_error($connMedical));
      $idProfissional =  $connMedical->insert_id;
    }
    $idProcedimento = NULL;
    $procedimento = $agendamento['procedimento'];
    $procedimentoExiste = $connMedical->query("SELECT id from procedimentos where nome = '$procedimento'");
    if($proced = $procedimentoExiste->fetch_assoc()){
      $idProcedimento = $proced['id'];
    }else{
      $connMedical->query("INSERT INTO procedimentos (nome, descricao) VALUES ('$procedimento', 'Migrado')") or die(mysqli_error($connMedical));
      $idProcedimento =  $connMedical->insert_id;
    }
    $query = "INSERT INTO agendamentos (observacoes, id_procedimento, id_convenio, dh_fim, dh_inicio, id_profissional, id_paciente) VALUES ('$descAgendamento', $idProcedimento,$idConvenio,'$dtFim','$dtIni',$idProfissional,$idPaciente)";
    $connMedical->query($query) or die(mysqli_error($connMedical));
  }
  
  fclose($f);
}

// Encerrando as conexões:
$connMedical->close();
// $connTemp->close();

// Informações de Fim da Migração:
echo "Fim da Migração: " . dateNow() . ".\n";

 
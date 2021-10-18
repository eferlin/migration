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

    // Enquanto nao terminar o arquivo
    while (!feof($f)) { 

        // Ler uma linha do arquivo
        $linha = fgetcsv($f, 0, $delimitador);
       
        if (!$linha) {
            continue;
        }

        // Montar registro com valores indexados pelo cabecalho
        $pacientes = array_combine($cabecalho, $linha);
        $convenio = $pacientes['convenio'];

        $convenioExiste = $connMedical->query("SELECT id from convenios where nome = '$convenio'");
        if ($convenioExiste->num_rows > 0){
          $idConvenio = $convenioExiste;
        }else{
          $connMedical->query("INSERT INTO convenios (nome, descricao) VALUES ('$convenio', 'Migrado')");
          $idconvenio = $connMedical->insert_id;
        }
        
        $nomePaciente = $pacientes['nome'];
        $sexo = $pacientes['sexo'];
        if ($sexo == "M"){
          $sexo = 'Masculino';
        }else{
          $sexo = "Feminino";
        }
        $nascimento = explode('/', $pacientes['nasc_paciente'])[2] . '-' . explode('/', $pacientes['nasc_paciente'])[1] . '-' . explode('/', $pacientes['nasc_paciente'])[0];
        echo $nascimento;
        $cpfPaciente = $pacientes['cpf'];
        $rgPaciente = $pacientes['rg'];
        $nomeConvenio = $pacientes['convenio'];
        $idConvenio = $connMedical->query("SELECT id from convenios where nome =" . "'" . $nomeConvenio ."'");
        $codRef = $pacientes['cod_referencia'];
        $pacienteExiste = $connMedical->query("SELECT nome from pacientes where nome = '$nomePaciente'");
        if ($pacienteExiste->num_rows > 0){
          echo "deu boa";
        }else{
          $connMedical->query("INSERT INTO pacientes (nome, sexo, nascimento, cpf, rg, id_convenio, cod_referencia) VALUES ('$nomePaciente','$sexo','$nascimento','$cpfPaciente','$rgPaciente','$idConvenio','$codRef')")or die(mysqli_error($connMedical));
          

        echo "primeiro";
        }

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
        $agendamentos = array_combine($cabecalho, $linha);

        // $cod_agendamento = $registro['cod_agendamento'];
        // $descricao = $registro['descricao'];

        // "insert into users (cod, desc) values (" . $cod_agendamento . "," . $descricao . ")";
        // $connMedical->query("insert into users (cod, desc) values ($1, $2)", [$cod_agendamento, $descricao]);
        
        echo $cod_agendamento . $registro['paciente'] . "\n\n";

        // Obtendo o nome
       // echo $registro['nome'];
    }
    
}

      //Abaixo cadastro de agendamento
      //Aqui ficou a dúvida sobre o nome dos médicos, pois iria ficar cadastro duplicado. Sendo assim, setei manualmente a id deles.
      if($agendamentos['id_profissional']==1){
        $idProfissional = '85218';
      }else{
        $idProfissional = '85217';
      }
    
      
      $diaAgendamento = explode('/', $agendamentos['dia'])[2] . '-' . explode('/', $agendamentos['dia'])[1] . explode('/', $agendamentos['dia'])[0];
      $horaInicio = $diaAgendamento . " " . $agendamentos['hora_inicio'];
      $horaFinal = $diaAgendamento . " " . $agendamentos['hora_fim'];
      $descAgendamento = $agendamentos['descricao'];
      $nomeConvenio = $agendamentos['convenio'];
      $codConvenio = $connMedical->query("SELECT id from convenios where nome = '$nomeConvenio'");  
      $nomeProcedimento = $agendamentos['procedimento'];
      $codProcedimento = $connMedical->query("SELECT id from procedimentos where nome = '$nomeProcedimento'");  
      
      $nomePaciente = $agendamentos['nome_paciente'];
      $codPaciente = $connMedical->query("SELECT id from pacientes where nome = '$nomePaciente'");  
    

      
      $idProfissional = $agendamentos['id_profissional'];

        $procedExiste = $connMedical->query("SELECT nome from procedimentos where nome = '$nomeProced'");
        if ($procedExiste->num_rows > 0){
          echo "erro";
        }else{
          $connMedical->query("INSERT INTO procedimentos (nome, descricao) VALUES ('$nomeProced', 'Migrado' )");
        echo "caca2";
        }


        //Abaixo cadastro de profissionais
        // echo $cod_agendamento . $registro['paciente'] . "\n\n";

        // Obtendo o nome
       // echo $registro['nome'];
    }
    fclose($f);
  


// Encerrando as conexões:
$connMedical->close();
// $connTemp->close();

// Informações de Fim da Migração:
echo "Fim da Migração: " . dateNow() . ".\n";

 
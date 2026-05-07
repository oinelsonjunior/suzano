<?php
include_once ('auth.php');
	include_once ('connection.php');
	$email = $_SESSION["admin_login"];

	//DADOS DE USER
	$sql = "SELECT id, username, email, password, role, enabled, patente, faccao, id_cadastro FROM users WHERE email = '$email'";
	// Execute the SQL query
	$result = $conn->query($sql);

	// Process the result set
	if ($result->num_rows > 0) {
	  // Output data of each row
	  while($row = $result->fetch_assoc()) {
	    $id 		=	$row["id"];
	    $username	=   $row["username"];
	    $email		=   $row["email"];
	    $password	=	$row["password"];
	    $role		=	$row["role"];
	    $enabled	=	$row["enabled"];
	    $patente	=	$row["patente"];
	    $faccao		=	$row["faccao"];
	    $id_cadastro=	$row["id_cadastro"];
	  }
	} else {
	  //echo "0 results";
	}

	//CADASTRO INTEGRANTE
	$sql = "SELECT * FROM cadastro_integrante WHERE id = '$id_cadastro'";
	// Execute the SQL query
	$result = $conn->query($sql);

	// Process the result set
	if ($result->num_rows > 0) {
	  // Output data of each row
	  while($row = $result->fetch_assoc()) {
	    $id 		=	$row["id"];
	    $nome		=   $row["nome"];
	    $apelido	=   $row["apelido"];
	    $veiculo	=	$row["veiculo"];
	    $cnh		=	$row["cnh"];
	    $padrinho	=	$row["padrinho"];
	    $data_apresentacao	=	$row["data_apresentacao"];
	    $faccao_cadastro =	$row["faccao"];
	    $patente	=	$row["patente"];
	    $endereco	=	$row["endereco"];
	    $num_endereco		=	$row["num_endereco"];
	    $cidade		=	$row["cidade"];
	    $estado		=	$row["estado"];
	    $cep		=	$row["cep"];
	    $complemento	=	$row["complemento"];
	    $bairro		=	$row["bairro"];
	    $telefone	=	$row["telefone"];
	    $comercial	=	$row["comercial"];
	    $celular	=	$row["celular"];
	    $recados	=	$row["recados"];
	    $email		=	$row["email"];
	  }
	} else {
	  //echo "0 results";
	}

	//GET FACCAO

	$sql = "SELECT a.faccao, b.nome FROM cadastro_integrante a INNER JOIN faccao b ON a.faccao = b.id WHERE b.id = '$faccao_cadastro'";
	// Execute the SQL query
	$result = $conn->query($sql);

	// Process the result set
	if ($result->num_rows > 0) {
	  // Output data of each row
	  while($row = $result->fetch_assoc()) {
	    $id_faccao	=	$row["faccao"];
	    $nome_faccao=   $row["nome"];
	   
	  }
	} else {
	  //echo "0 results";
	}


	// GET TOTAL INTEGRANTES
	$sql = "SELECT a.id,a.faccao
			FROM cadastro_integrante a
			INNER JOIN faccao b
			ON a.faccao = b.id WHERE b.id = '$faccao_cadastro'";
	// Execute the SQL query
	$result = $conn->query($sql);

	$total_integrantes = $result->num_rows;


	// GET TOTAL INTEGRANTES ATIVOS
	$sql = "SELECT a.id,a.faccao
			FROM cadastro_integrante a
			INNER JOIN faccao b
			ON a.faccao = b.id WHERE b.id = '$faccao_cadastro' AND status = 1";
	// Execute the SQL query
	$result = $conn->query($sql);

	$total_integrantes_ativos = $result->num_rows;

	$pc_total_integrantes_ativos = ($total_integrantes_ativos / $total_integrantes) * 100;

	$pc_total_integrantes_ativos = round($pc_total_integrantes_ativos);


	// GET TOTAL INTEGRANTES AFASTADOS / DESLIGADOS
	$sql = "SELECT a.id,a.faccao
			FROM cadastro_integrante a
			INNER JOIN faccao b
			ON a.faccao = b.id WHERE b.id = '$faccao_cadastro' AND (status = 2 OR status = 3)";
	// Execute the SQL query
	$result = $conn->query($sql);

	$total_integrantes_afastados_desligados = $result->num_rows;

	$pc_total_integrantes_afastados_desligados = ($total_integrantes_afastados_desligados / $total_integrantes) * 100;

	$pc_total_integrantes_afastados_desligados = round($pc_total_integrantes_afastados_desligados);


	// consulta
$sql = "SELECT 
    COUNT(*) as total_integrantes,
    SUM(status = 1) as total_ativos,
    SUM(status = 2) as total_afastados,
    SUM(status = 3) as total_desligados,
    SUM(status = 4) as total_suspensos
FROM cadastro_integrante WHERE faccao = 1";

$res = $conn->query($sql);
$row = $res->fetch_assoc();

// variáveis no padrão
$total_integrantes = $row['total_integrantes'];
$total_ativos = $row['total_ativos'];
$total_afastados = $row['total_afastados'];
$total_desligados = $row['total_desligados'];
$total_suspensos = $row['total_suspensos'];

// porcentagem
$percent_ativos = $total_integrantes > 0 
    ? round(($total_ativos / $total_integrantes) * 100) 
    : 0;

	
?>
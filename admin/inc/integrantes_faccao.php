<?php

$sql = "SELECT * FROM cadastro_integrante WHERE faccao = '$id_faccao' ORDER BY patente ASC";
	// Execute the SQL query
	$result = $conn->query($sql);

	// Process the result set
	if ($result->num_rows > 0) {
		$i = 1;

	  // Output data of each row
	  while($row = $result->fetch_assoc()) {
	    $id_int		=	$row["id"];
	    $nome		=   $row["nome"];
	    $apelido	=   $row["apelido"];
	    $veiculo	=	$row["veiculo"];
	    $cnh		=	$row["cnh"];
	    $padrinho	=	$row["padrinho"];
	    $data_apresentacao	=	$row["data_apresentacao"];
	    $faccao_cadastro =	$row["faccao"];
	    $patente	=	$row["patente"];
	    switch ($patente) {
	    	case '10':
	    		$patente_out = 'X';
	    		break;
	    	case '9':
	    		$patente_out = 'IX';
	    		break;
	    	case '8':
	    		$patente_out = 'VIII';
	    		break;
	    	case '7':
	    		$patente_out = 'VII';
	    		break;
	    	case '6':
	    		$patente_out = 'VI';
	    		break;
	    	case '5':
	    		$patente_out = 'V';
	    		break;
	    	case '4':
	    		$patente_out = 'IV';
	    		break;
	    	case '3':
	    		$patente_out = 'III';
	    		break;
	    	case '2':
	    		$patente_out = 'II';
	    		break;
	    	case '1':
	    		$patente_out = 'I';
	    		break;
	    	default:
	    		// code...
	    		break;
	    }
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
	    $status		=	$row["status"];
	    switch ($status) {
	    	case '1':
	    		$status_out = 'Ativo';
	    		$status_class = 'status-active';
	    		break;
	    	case '2':
	    		$status_out = 'Afastado';
	    		$status_class = 'status-active';
	    		break;
	    	case '3':
	    		$status_out = 'Desligado';
	    		$status_class = 'status-inactive';
	    		break;
	    	case '4':
	    		$status_out = 'Suspenso';
	    		$status_class = 'status-active';
	    		break;
	    	
	    	default:
	    		// code...
	    		break;
	    }
	    echo '<tr>
	              <td>'.$i++.'</td>
	              <td>'.$apelido.'</td>
	              <td>'.$patente_out.'</td>
	              <td><span class="badge-status '.$status_class.'">'.$status_out.'</span></td>
	              <td><a class="btn btn-sm btn-outline-light" href="integrante_view?id='.$id_int.'" >Ver</a></td>
	            </tr>';
	  }
	} else {
	  //echo "0 results";
	}

?>
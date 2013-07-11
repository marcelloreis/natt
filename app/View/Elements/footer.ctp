<?php
//Responsável pela impressão do javascript
echo $this->Js->writeBuffer();

/*
* Libera a conexão com o banco
*/
$db = ConnectionManager::getDataSource("default");
$db->disconnect();
?>
	</body>
	</html>
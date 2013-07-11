<?php
/**
 * Bake Template for Controller action generation.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.actions
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
	
	/**
	* Método <?php echo $admin ?>index
	* Este método contem regras de negocios que permitem visualizar todos os registros contidos na entidade do controlador
	*
	* @override Metodo AppController.index
	* @param string $period (Periodo das movimentacoes q serao listadas)
	* @return void
	*/
	public function index($params=array()){
		//@override
		parent::index($params);
	}		

	/**
	* Método edit
	* Este método contem regras de negocios que permitem adicionar e editar registros na base de dados
	*
	* @override Metodo AppController.edit
	* @param string $id
	* @return void
	*/
	public function edit($id=null){
		//@override
		parent::edit($id);

	<?php if(count($modelObj->hasAndBelongsToMany)):?>		
		/**
		* Verifica se o $id do registro foi setado
		*/
		if(isset($id) && !empty($id)){
			/**
			* Carrega o nome do model associado
			*/
			$habtm = isset($this->params['named']['habtm'])?$this->params['named']['habtm']:false;
			switch ($habtm) {
		<?php foreach ($modelObj->hasAndBelongsToMany as $k => $v):?>
			<?php if (!empty($k)):?>

				/**
				* <?php echo $k?> Associados ao <?php echo "{$currentModelName}\n" ?>
				*/
				case '<?php echo $k?>':
					$this->__paginationHabtm('<?php echo $k?>');
					break;
			<?php endif?>
		<?php endforeach?>
			}
		}
	<?php endif?>
	}

<?php if(count($modelObj->hasAndBelongsToMany)):?>		
	/**
	* Método unjoin
	* Este método contem regras de negocios que permitem desassociar registros HasAndBelongsToMany
	*
	* @override Metodo AppController.__unjoin
	* @param string $id
	* @return void
	*/
	public function unjoin($id=null){
		//@override
		parent::__unjoin($id);
	}
<?php endif?>




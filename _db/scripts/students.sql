delimiter $$

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_id` int(11) NOT NULL COMMENT 'Código da cidade',
  `city_ds` varchar(255) DEFAULT NULL COMMENT 'Nome da cidade, caso nao exista no bd',
  `state_ds` varchar(255) DEFAULT NULL COMMENT 'Nome do estado, caso nao exista no bd',
  `name` varchar(255) DEFAULT NULL COMMENT 'Nome',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email',
  `password` char(40) DEFAULT NULL COMMENT 'Senha (Se possivel, insira a senha sem encriptografia)',
  `matriculation` varchar(255) DEFAULT NULL COMMENT 'Matricula',
  `doc` bigint(11) DEFAULT NULL COMMENT 'CPF',
  `birthday` date DEFAULT NULL COMMENT 'Data Aniversário',
  `telephone` bigint(11) DEFAULT NULL COMMENT 'Telefone',
  `sex` tinyint(2) DEFAULT NULL COMMENT 'Sexo',
  `shirt_size` tinyint(2) DEFAULT NULL COMMENT 'Tamanho camisa',
  `address` varchar(255) DEFAULT NULL COMMENT 'Endereço',
  `complement` varchar(255) DEFAULT NULL COMMENT 'Complemento',
  `neighborhood` varchar(255) DEFAULT NULL COMMENT 'Bairro',
  `study_level` tinyint(2) DEFAULT NULL COMMENT 'nível educacional',
  `created` datetime DEFAULT NULL COMMENT 'Data de criacao do registro',
  `modified` datetime DEFAULT NULL COMMENT 'Data de modificacao do registro',
  `trashed` tinyint(1) DEFAULT NULL COMMENT 'Deixe este campo como NULL',
  `deleted` tinyint(1) DEFAULT NULL COMMENT 'Deixe este campo como NULL',
  PRIMARY KEY (`id`),
  KEY `fk_students_cities1_idx` (`city_id`),
  CONSTRAINT `fk_students_cities1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$


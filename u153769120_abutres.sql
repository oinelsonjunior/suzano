-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 07/05/2026 às 20:16
-- Versão do servidor: 11.8.6-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u153769120_abutres`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cadastro_integrante`
--

CREATE TABLE `cadastro_integrante` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `apelido` varchar(200) NOT NULL,
  `veiculo` varchar(200) NOT NULL,
  `cnh` varchar(200) NOT NULL,
  `padrinho` int(20) NOT NULL,
  `data_apresentacao` datetime NOT NULL,
  `faccao` int(20) NOT NULL,
  `patente` int(10) NOT NULL,
  `nascimento` datetime NOT NULL,
  `endereco` varchar(200) NOT NULL,
  `num_endereco` varchar(200) NOT NULL,
  `cidade` varchar(200) NOT NULL,
  `estado` varchar(200) NOT NULL,
  `cep` varchar(200) NOT NULL,
  `complemento` varchar(200) NOT NULL,
  `bairro` varchar(200) NOT NULL,
  `telefone` varchar(200) NOT NULL,
  `comercial` varchar(200) NOT NULL,
  `celular` varchar(200) NOT NULL,
  `recados` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` int(10) NOT NULL DEFAULT 1 COMMENT '1 - Ativo\r\n2 - Afastado\r\n3 - Desligado\r\n4 - Suspenso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cadastro_integrante`
--

INSERT INTO `cadastro_integrante` (`id`, `nome`, `apelido`, `veiculo`, `cnh`, `padrinho`, `data_apresentacao`, `faccao`, `patente`, `nascimento`, `endereco`, `num_endereco`, `cidade`, `estado`, `cep`, `complemento`, `bairro`, `telefone`, `comercial`, `celular`, `recados`, `email`, `foto`, `status`) VALUES
(1, 'Nelson Pereira Silva Junior', 'Boca Preta', 'Sim', '0', 0, '2018-10-13 00:00:00', 1, 7, '1988-12-15 00:00:00', 'Rua Lagoa do Campelo', '66', 'São Paulo', 'SP', '08295360', 'Apto. 61B', 'Itaquera', '', '', '11986095353', '', 'nelson@gmail.com', 'uploads/fotos/1_1778086599.jpg', 4),
(2, 'Ricardo Alves Mendes', 'Ricki', 'Sim', '0', 0, '2021-06-16 15:02:52', 1, 8, '0000-00-00 00:00:00', 'Rua Lagoa do Campelo', '66', 'São Paulo', 'SP', '08295360', 'Apto. 61B', 'Itaquera', '', '', '11977601510', '', 'nelson@gmail.com', NULL, 1),
(3, 'Samuel', 'Sam', 'Sim', '0', 0, '2021-06-16 00:00:00', 1, 6, '0000-00-00 00:00:00', 'Rua Lagoa do Campelo', '66', 'São Paulo', 'SP', '08295360', 'Apto. 61B', 'Itaquera', '', '', '11977601510', '', 'nelson@gmail.com', NULL, 1),
(4, 'Gabriel John', 'Micŕobio', 'Sim', '0', 0, '2021-06-16 15:02:52', 1, 8, '0000-00-00 00:00:00', 'Rua Lagoa do Campelo', '66', 'São Paulo', 'SP', '08295360', 'Apto. 61B', 'Itaquera', '', '', '11977601510', '', 'nelson@gmail.com', NULL, 1),
(5, 'Paraguay', 'Paraguay', '', '', 0, '2026-05-05 14:59:28', 1, 8, '2026-05-05 14:59:28', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 4),
(6, 'Rodrigo', 'Rodrigão', '', '', 0, '2026-05-05 14:59:28', 1, 8, '2026-05-05 14:59:28', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 1),
(7, 'Wagner', 'Rocker', '', '', 0, '2026-05-05 15:00:39', 1, 8, '2026-05-05 15:00:39', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 1),
(8, 'Alf', 'Alf', '', '', 0, '2026-05-05 15:00:39', 1, 8, '2026-05-05 15:00:39', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 1),
(9, 'Renato Franzotti Alves', 'Franzotti', 'Sim', '', 0, '2026-05-05 00:00:00', 1, 10, '2026-05-05 00:00:00', '', '', '', '', '', '', '', '', '', '0', '', '', 'uploads/fotos/9_1778093175.jpg', 1),
(10, 'Henrique Gomes do Nascimento', 'Nascimento', 'Sim', '08854611452', 1, '2025-10-10 00:00:00', 1, 10, '2026-05-05 00:00:00', 'Rua Daniel Garijo Álvares', '105', 'Suzano', 'SP', '08685180', 'Casa 2', 'Vila Maluf', '', '', '11949970617', '', 'riqueroll@gmail.com', 'uploads/fotos/10_1778093444.jpg', 1),
(11, 'Napa', 'Napa', 'Sim', '', 0, '2026-05-05 00:00:00', 1, 10, '2026-05-05 00:00:00', '', '', '', '', '', '', '', '', '', '13997844772', '', '', 'uploads/fotos/11_1778106827.jpg', 1),
(12, 'Reinaldo Pinto de Melo', 'Kabuloso', 'Sim', '', 3, '2026-04-07 00:00:00', 1, 9, '2026-05-05 00:00:00', 'Estrada Waldomiro Dias', '186', 'Suzano', 'SP', '08696-350', '', 'Jardim Gardênia Azul', '', '', '11979696182', '', '', 'uploads/fotos/12_1778093619.jpg', 1),
(13, 'Allan de Luca Alves Bezerra', 'De Luca', 'Sim', '', 1, '2026-04-16 00:00:00', 1, 10, '2026-05-05 00:00:00', 'Rua Rio Imburana', '127', 'São Paulo', 'SP', '08215510', '', 'Itaquera', '', '', '11982384490', '', '', 'uploads/fotos/13_1778093748.jpg', 1),
(14, 'Mateus', 'Sem Rumo', '', '', 0, '2026-05-05 15:03:43', 1, 10, '2026-05-05 15:03:43', '', '', '', '', '', '', '', '', '', '', '', '', NULL, 2),
(16, 'Helder', 'Helder', 'Sim', '0', 4, '2026-05-06 00:00:00', 1, 8, '2026-05-06 00:00:00', '', '', 'Suzano', '0', '', '', 'Suzano', '11111', '11111', '111111', '11111', 'email@email.com', NULL, 3),
(17, 'Sadico', 'Sádico', 'Sim', '0', 0, '2026-05-06 00:00:00', 1, 9, '2026-05-06 00:00:00', '1111', '1111', '1111', '11', '11111', '111', '1111', '11111', '11111', '1111', '11111', 'email@email.com', NULL, 2),
(18, 'Verdade', 'Verdade', 'Sim', '1', 0, '2026-05-06 00:00:00', 1, 10, '2026-05-06 00:00:00', '11111', '1111', '1111', '11', '11111', '1111', '11111', '1111', '11111', '11111', '11111', 'email@email.com', NULL, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixa_saida`
--

CREATE TABLE `caixa_saida` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `caixa_saida`
--

INSERT INTO `caixa_saida` (`id`, `descricao`, `valor`, `data_saida`, `created_at`) VALUES
(2, 'Valor que Ricki deve para a Facção - Pagou somente o Repasse', 70.00, '2026-05-06', '2026-05-06 18:08:12'),
(3, 'Churrasco Sede Suzano', 115.00, '2026-02-03', '2026-05-06 18:09:13'),
(4, 'Churrasco Sede Suzano', 105.00, '2026-02-10', '2026-05-06 18:09:29'),
(5, 'Churrasco Sede Suzano', 108.00, '2026-02-17', '2026-05-06 18:09:51'),
(6, 'Churrasco Sede Suzano', 110.00, '2026-02-24', '2026-05-06 18:10:04'),
(7, 'Churrasco Sede Suzano', 114.00, '2026-03-03', '2026-05-06 18:10:59'),
(8, 'Churrasco Sede Suzano', 100.00, '2026-03-10', '2026-05-06 18:11:13'),
(9, 'Churrasco Sede Suzano', 115.00, '2026-03-17', '2026-05-06 18:13:28'),
(10, 'Churrasco Sede Suzano', 97.00, '2026-03-24', '2026-05-06 18:13:50'),
(11, 'Churrasco Sede Suzano', 124.00, '2026-03-31', '2026-05-06 18:14:12'),
(12, 'Churrasco Sede Suzano', 115.00, '2026-04-14', '2026-05-06 18:16:14'),
(13, 'Churrasco Sede Suzano', 110.00, '2026-04-21', '2026-05-06 18:16:29'),
(14, 'Churrasco Sede Suzano', 100.00, '2026-04-28', '2026-05-06 18:17:19'),
(15, 'Retirada Gasolina - Ricki - Sede Estadual', 40.00, '2026-03-04', '2026-05-06 18:20:36'),
(16, 'Retirada Gasolina - Sede Estadual - Nascimento', 30.00, '2026-04-01', '2026-05-06 18:21:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplina`
--

CREATE TABLE `disciplina` (
  `id` int(11) NOT NULL,
  `integrante_id` int(11) NOT NULL,
  `duracao_dias` int(3) NOT NULL COMMENT '30, 60 ou 90',
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `aplicado_por` varchar(200) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=em curso, 0=encerrado',
  `encerrado_em` date DEFAULT NULL,
  `encerrado_por` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `disciplina`
--

INSERT INTO `disciplina` (`id`, `integrante_id`, `duracao_dias`, `data_inicio`, `data_fim`, `motivo`, `aplicado_por`, `ativo`, `encerrado_em`, `encerrado_por`, `created_at`) VALUES
(2, 5, 30, '2026-05-06', '2026-06-04', 'Medida Disciplinar por descumprimento de ordem direta do Disciplina Estadual', 'Disciplina Marcão', 1, NULL, NULL, '2026-05-06 21:48:26'),
(4, 1, 30, '2026-04-29', '2026-05-28', 'Medida Disciplinar por descumprimento de ordem direta do Disciplina Estadual', 'Disciplina Marcão', 1, NULL, NULL, '2026-05-06 23:10:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `faccao` int(11) NOT NULL DEFAULT 1,
  `tipo` tinyint(2) NOT NULL COMMENT '1=Sede Estadual (padrão)\n2=Sede Estadual (reunião geral)\n3=Sede Suzano (reunião)\n4=Sede Suzano (confraternização)\n5=Evento Fora\n6=Evento Obrigatório',
  `nome` varchar(200) NOT NULL,
  `data_evento` date NOT NULL,
  `observacao` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `faccao`, `tipo`, `nome`, `data_evento`, `observacao`, `created_at`) VALUES
(1, 1, 2, 'Sede Estadual (reunião geral) — Maio 2026', '2026-05-06', 'Reunião Geral Sede Estadual', '2026-05-06 01:32:19'),
(2, 1, 1, 'Sede Estadual (padrão) — Maio 2026', '2026-05-13', 'Reunião Semanal - Estadual', '2026-05-06 17:32:19'),
(3, 1, 3, 'Sede Suzano (reunião) — Maio 2026', '2026-05-12', 'Reunião Mensal Suzano', '2026-05-06 17:32:55'),
(4, 1, 4, 'Sede Suzano (confraternização) — Maio 2026', '2026-05-19', '', '2026-05-06 17:36:40'),
(5, 1, 4, 'Sede Suzano (confraternização) — Maio 2026', '2026-05-26', '', '2026-05-06 17:36:59'),
(6, 1, 1, 'Sede Estadual (padrão) — Maio 2026', '2026-05-20', '', '2026-05-06 17:37:26'),
(7, 1, 1, 'Sede Estadual (padrão) — Maio 2026', '2026-05-27', '', '2026-05-06 17:37:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `faccao`
--

CREATE TABLE `faccao` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `faccao`
--

INSERT INTO `faccao` (`id`, `nome`) VALUES
(1, 'Suzano');

-- --------------------------------------------------------

--
-- Estrutura para tabela `frequencias`
--

CREATE TABLE `frequencias` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `integrante_id` int(11) NOT NULL,
  `presente` tinyint(1) NOT NULL DEFAULT 0,
  `patente_no_evento` int(11) DEFAULT NULL,
  `status_no_evento` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `frequencias`
--

INSERT INTO `frequencias` (`id`, `evento_id`, `integrante_id`, `presente`, `patente_no_evento`, `status_no_evento`, `updated_at`) VALUES
(1, 1, 1, 0, 7, 4, '2026-05-06 01:32:19'),
(2, 1, 2, 0, 8, 1, '2026-05-06 01:32:19'),
(3, 1, 3, 1, 6, 1, '2026-05-06 06:11:04'),
(4, 1, 4, 0, 8, 1, '2026-05-06 01:32:19'),
(5, 1, 5, 0, 8, 4, '2026-05-06 01:32:19'),
(6, 1, 6, 0, 8, 1, '2026-05-07 12:53:11'),
(7, 1, 7, 0, 8, 1, '2026-05-07 12:53:09'),
(8, 1, 8, 0, 8, 1, '2026-05-06 17:03:21'),
(9, 1, 9, 0, 10, 1, '2026-05-06 01:32:19'),
(10, 1, 10, 0, 10, 1, '2026-05-06 01:32:19'),
(11, 1, 11, 1, 10, 1, '2026-05-06 06:11:20'),
(12, 1, 12, 0, 10, 1, '2026-05-06 01:32:19'),
(13, 1, 13, 0, 10, 1, '2026-05-06 01:32:19'),
(14, 2, 1, 0, 7, 4, '2026-05-06 17:32:19'),
(15, 2, 2, 0, 8, 1, '2026-05-06 17:32:19'),
(16, 2, 3, 0, 6, 1, '2026-05-06 17:32:19'),
(17, 2, 4, 0, 8, 1, '2026-05-06 17:32:19'),
(18, 2, 5, 0, 8, 4, '2026-05-06 17:32:19'),
(19, 2, 6, 0, 8, 1, '2026-05-06 17:32:19'),
(20, 2, 7, 0, 8, 1, '2026-05-06 17:32:19'),
(21, 2, 8, 0, 8, 1, '2026-05-06 17:32:19'),
(22, 2, 9, 0, 10, 1, '2026-05-06 17:32:19'),
(23, 2, 10, 0, 10, 1, '2026-05-06 17:32:19'),
(24, 2, 11, 0, 10, 1, '2026-05-06 17:32:19'),
(25, 2, 12, 0, 9, 1, '2026-05-06 17:32:19'),
(26, 2, 13, 0, 10, 1, '2026-05-06 17:32:19'),
(27, 3, 1, 1, 7, 4, '2026-05-06 22:28:12'),
(28, 3, 2, 0, 8, 1, '2026-05-06 17:32:55'),
(29, 3, 3, 1, 6, 1, '2026-05-06 22:28:10'),
(30, 3, 4, 0, 8, 1, '2026-05-06 17:32:55'),
(31, 3, 5, 0, 8, 4, '2026-05-06 17:32:55'),
(32, 3, 6, 0, 8, 1, '2026-05-06 17:32:55'),
(33, 3, 7, 0, 8, 1, '2026-05-06 17:32:55'),
(34, 3, 8, 1, 8, 1, '2026-05-06 22:28:18'),
(35, 3, 9, 1, 10, 1, '2026-05-06 22:28:23'),
(36, 3, 10, 1, 10, 1, '2026-05-06 22:28:26'),
(37, 3, 11, 1, 10, 1, '2026-05-06 22:28:28'),
(38, 3, 12, 1, 9, 1, '2026-05-06 22:28:22'),
(39, 3, 13, 0, 10, 1, '2026-05-06 17:32:55'),
(40, 4, 1, 0, 7, 4, '2026-05-06 17:36:40'),
(41, 4, 2, 0, 8, 1, '2026-05-06 17:36:40'),
(42, 4, 3, 0, 6, 1, '2026-05-06 17:36:40'),
(43, 4, 4, 0, 8, 1, '2026-05-06 17:36:40'),
(44, 4, 5, 0, 8, 4, '2026-05-06 17:36:40'),
(45, 4, 6, 0, 8, 1, '2026-05-06 17:36:40'),
(46, 4, 7, 0, 8, 1, '2026-05-06 17:36:40'),
(47, 4, 8, 0, 8, 1, '2026-05-06 17:36:40'),
(48, 4, 9, 0, 10, 1, '2026-05-06 17:36:40'),
(49, 4, 10, 0, 10, 1, '2026-05-06 17:36:40'),
(50, 4, 11, 0, 10, 1, '2026-05-06 17:36:40'),
(51, 4, 12, 0, 9, 1, '2026-05-06 17:36:40'),
(52, 4, 13, 0, 10, 1, '2026-05-06 17:36:40'),
(53, 5, 1, 0, 7, 4, '2026-05-06 17:36:59'),
(54, 5, 2, 0, 8, 1, '2026-05-06 17:36:59'),
(55, 5, 3, 0, 6, 1, '2026-05-06 17:36:59'),
(56, 5, 4, 0, 8, 1, '2026-05-06 17:36:59'),
(57, 5, 5, 0, 8, 4, '2026-05-06 17:36:59'),
(58, 5, 6, 0, 8, 1, '2026-05-06 17:36:59'),
(59, 5, 7, 0, 8, 1, '2026-05-06 17:36:59'),
(60, 5, 8, 0, 8, 1, '2026-05-06 17:36:59'),
(61, 5, 9, 0, 10, 1, '2026-05-06 17:36:59'),
(62, 5, 10, 0, 10, 1, '2026-05-06 17:36:59'),
(63, 5, 11, 0, 10, 1, '2026-05-06 17:36:59'),
(64, 5, 12, 0, 9, 1, '2026-05-06 17:36:59'),
(65, 5, 13, 0, 10, 1, '2026-05-06 17:36:59'),
(66, 6, 1, 0, 7, 4, '2026-05-06 17:37:26'),
(67, 6, 2, 0, 8, 1, '2026-05-06 17:37:26'),
(68, 6, 3, 0, 6, 1, '2026-05-06 17:37:26'),
(69, 6, 4, 0, 8, 1, '2026-05-06 17:37:26'),
(70, 6, 5, 0, 8, 4, '2026-05-06 17:37:26'),
(71, 6, 6, 0, 8, 1, '2026-05-06 17:37:26'),
(72, 6, 7, 0, 8, 1, '2026-05-06 17:37:26'),
(73, 6, 8, 0, 8, 1, '2026-05-06 17:37:26'),
(74, 6, 9, 0, 10, 1, '2026-05-06 17:37:26'),
(75, 6, 10, 0, 10, 1, '2026-05-06 17:37:26'),
(76, 6, 11, 0, 10, 1, '2026-05-06 17:37:26'),
(77, 6, 12, 0, 9, 1, '2026-05-06 17:37:26'),
(78, 6, 13, 0, 10, 1, '2026-05-06 17:37:26'),
(79, 7, 1, 0, 7, 4, '2026-05-06 17:37:59'),
(80, 7, 2, 0, 8, 1, '2026-05-06 17:37:59'),
(81, 7, 3, 0, 6, 1, '2026-05-06 17:37:59'),
(82, 7, 4, 0, 8, 1, '2026-05-06 17:37:59'),
(83, 7, 5, 0, 8, 4, '2026-05-06 17:37:59'),
(84, 7, 6, 0, 8, 1, '2026-05-06 17:37:59'),
(85, 7, 7, 0, 8, 1, '2026-05-06 17:37:59'),
(86, 7, 8, 0, 8, 1, '2026-05-06 17:37:59'),
(87, 7, 9, 0, 10, 1, '2026-05-06 17:37:59'),
(88, 7, 10, 0, 10, 1, '2026-05-06 17:37:59'),
(89, 7, 11, 0, 10, 1, '2026-05-06 17:37:59'),
(90, 7, 12, 0, 9, 1, '2026-05-06 17:37:59'),
(91, 7, 13, 0, 10, 1, '2026-05-06 17:37:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `isencoes`
--

CREATE TABLE `isencoes` (
  `id` int(11) NOT NULL,
  `mensalidade_id` int(11) DEFAULT NULL,
  `integrante_id` int(11) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `isencoes`
--

INSERT INTO `isencoes` (`id`, `mensalidade_id`, `integrante_id`, `ano`, `mes`, `motivo`, `admin_id`, `created_at`) VALUES
(1, 3, 3, 2026, 1, 'Diretor Regional', 1, '2026-05-06 15:42:27'),
(2, 14, 1, 2026, 2, 'Diretor Regional', 1, '2026-05-06 17:51:16'),
(3, 27, 1, 2026, 3, 'Diretor Regional', 1, '2026-05-06 17:55:25'),
(4, 20, 7, 2026, 2, 'Isento pela Estadual', 1, '2026-05-06 17:58:25'),
(5, 33, 7, 2026, 3, 'Isento pela Estadual', 1, '2026-05-06 18:00:02'),
(6, 40, 1, 2026, 4, 'Diretor Regional', 1, '2026-05-06 18:02:06'),
(7, 46, 7, 2026, 4, 'Isento pela Estadual', 1, '2026-05-06 18:03:57'),
(8, 65, 13, 2026, 5, 'Recém Ingresso', 1, '2026-05-06 18:05:47'),
(9, 64, 12, 2026, 5, 'Recém Ingresso', 1, '2026-05-06 18:05:54'),
(10, 63, 11, 2026, 5, 'Recém Ingresso', 1, '2026-05-06 18:06:03'),
(11, 59, 7, 2026, 5, 'Isento pela Estadual', 1, '2026-05-06 18:06:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensalidades`
--

CREATE TABLE `mensalidades` (
  `id` int(11) NOT NULL,
  `integrante_id` int(11) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT 150.00,
  `valor_repasse` decimal(10,2) DEFAULT 80.00,
  `valor_faccao` decimal(10,2) DEFAULT 70.00,
  `pago` tinyint(4) DEFAULT 0,
  `data_pagamento` date DEFAULT NULL,
  `isento` tinyint(4) DEFAULT 0,
  `patente_no_mes` int(11) DEFAULT NULL,
  `status_no_mes` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `mensalidades`
--

INSERT INTO `mensalidades` (`id`, `integrante_id`, `ano`, `mes`, `valor_total`, `valor_repasse`, `valor_faccao`, `pago`, `data_pagamento`, `isento`, `patente_no_mes`, `status_no_mes`, `created_at`) VALUES
(14, 1, 2026, 2, 150.00, 80.00, 70.00, 0, NULL, 1, 7, 4, '2026-05-06 17:49:14'),
(15, 2, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:49:14'),
(16, 3, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:49:14'),
(17, 4, 2026, 2, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 1, '2026-05-06 17:49:14'),
(18, 5, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 4, '2026-05-06 17:49:14'),
(19, 6, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:49:14'),
(20, 7, 2026, 2, 150.00, 80.00, 70.00, 0, NULL, 1, 8, 1, '2026-05-06 17:49:14'),
(21, 8, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:49:14'),
(22, 9, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 10, 1, '2026-05-06 17:49:14'),
(23, 10, 2026, 2, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 10, 1, '2026-05-06 17:49:14'),
(27, 1, 2026, 3, 150.00, 80.00, 70.00, 0, NULL, 1, 7, 4, '2026-05-06 17:54:56'),
(28, 2, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:54:56'),
(29, 3, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:54:56'),
(30, 4, 2026, 3, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 1, '2026-05-06 17:54:56'),
(31, 5, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 4, '2026-05-06 17:54:56'),
(32, 6, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:54:56'),
(33, 7, 2026, 3, 150.00, 80.00, 70.00, 0, NULL, 1, 8, 1, '2026-05-06 17:54:56'),
(34, 8, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 17:54:56'),
(35, 9, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 10, 1, '2026-05-06 17:54:56'),
(36, 10, 2026, 3, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 10, 1, '2026-05-06 17:54:56'),
(40, 1, 2026, 4, 150.00, 80.00, 70.00, 0, NULL, 1, 7, 4, '2026-05-06 18:00:52'),
(41, 2, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:00:52'),
(42, 3, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:00:52'),
(43, 4, 2026, 4, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 1, '2026-05-06 18:00:52'),
(44, 5, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 4, '2026-05-06 18:00:52'),
(45, 6, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:00:52'),
(46, 7, 2026, 4, 150.00, 80.00, 70.00, 0, NULL, 1, 8, 1, '2026-05-06 18:00:52'),
(47, 8, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:00:52'),
(48, 9, 2026, 4, 150.00, 80.00, 70.00, 0, NULL, 0, 10, 1, '2026-05-06 18:00:52'),
(49, 10, 2026, 4, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 10, 1, '2026-05-06 18:00:52'),
(53, 1, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 7, 4, '2026-05-06 18:04:42'),
(54, 2, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 1, '2026-05-06 18:04:42'),
(55, 3, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 1, 6, 1, '2026-05-06 18:04:42'),
(56, 4, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 1, '2026-05-06 18:04:42'),
(57, 5, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 8, 4, '2026-05-06 18:04:42'),
(58, 6, 2026, 5, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:04:42'),
(59, 7, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 1, 8, 1, '2026-05-06 18:04:42'),
(60, 8, 2026, 5, 150.00, 80.00, 70.00, 1, '2026-05-06', 0, 8, 1, '2026-05-06 18:04:42'),
(61, 9, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 10, 1, '2026-05-06 18:04:42'),
(62, 10, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 0, 10, 1, '2026-05-06 18:04:42'),
(63, 11, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 1, 10, 1, '2026-05-06 18:04:42'),
(64, 12, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 1, 9, 1, '2026-05-06 18:04:42'),
(65, 13, 2026, 5, 150.00, 80.00, 70.00, 0, NULL, 1, 10, 1, '2026-05-06 18:04:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(15) NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `patente` varchar(20) NOT NULL,
  `faccao` int(20) NOT NULL,
  `id_cadastro` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `enabled`, `patente`, `faccao`, `id_cadastro`) VALUES
(1, 'admin', 'nelson@gmail.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'admin', 1, 'VI', 1, 1),
(2, 'admin', 'junior@gmail.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'membro', 1, 'VI', 1, 1),
(3, 'admin', 'rodrigo@gmail.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'admin', 1, 'VI', 1, 6),
(4, 'admin', 'sam@email.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'admin', 0, 'VI', 1, 3),
(5, 'admin', 'kazz@email.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'admin', 1, 'VI', 1, 1),
(6, 'admin', 'ricki@email.com', '$2y$10$GdT5GF/9LH6y5JZLaWStyOI1aFtOEi0iF7q.Gaj5AvNFWlpiW.rxy', 'admin', 1, 'VI', 1, 2),
(7, 'teste', 'teste@teste.com', '$2y$10$kfrXxLW.XCDG7VMEYxpHn.pdVJ66NAkjFBI/QdZdZg5Rb5hPtTU9u', 'membro', 1, '', 1, 11),
(8, 'Franzotti', 'renato@suzano.com', '$2y$10$BetJK2mYWdhyPgF9lXOMM.TMHGeFjt2Pdq/mCnmaQjPSKHGvVrqjm', 'membro', 1, '', 1, 9);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cadastro_integrante`
--
ALTER TABLE `cadastro_integrante`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `caixa_saida`
--
ALTER TABLE `caixa_saida`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_integrante` (`integrante_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `faccao`
--
ALTER TABLE `faccao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `frequencias`
--
ALTER TABLE `frequencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `evento_integrante` (`evento_id`,`integrante_id`),
  ADD KEY `fk_freq_integrante` (`integrante_id`);

--
-- Índices de tabela `isencoes`
--
ALTER TABLE `isencoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_integrante_ano_mes` (`integrante_id`,`ano`,`mes`);

--
-- Índices de tabela `mensalidades`
--
ALTER TABLE `mensalidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `integrante_id` (`integrante_id`,`ano`,`mes`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cadastro_integrante`
--
ALTER TABLE `cadastro_integrante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `caixa_saida`
--
ALTER TABLE `caixa_saida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `faccao`
--
ALTER TABLE `faccao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `frequencias`
--
ALTER TABLE `frequencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de tabela `isencoes`
--
ALTER TABLE `isencoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `mensalidades`
--
ALTER TABLE `mensalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `disciplina`
--
ALTER TABLE `disciplina`
  ADD CONSTRAINT `fk_disc_integrante` FOREIGN KEY (`integrante_id`) REFERENCES `cadastro_integrante` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `frequencias`
--
ALTER TABLE `frequencias`
  ADD CONSTRAINT `fk_freq_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_freq_integrante` FOREIGN KEY (`integrante_id`) REFERENCES `cadastro_integrante` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

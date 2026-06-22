-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 14, 2025 at 10:36 PM
-- Server version: 10.11.13-MariaDB-cll-lve
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cp1146011p43_iob`
--

-- --------------------------------------------------------

--
-- Table structure for table `LogConnexion`
--

CREATE TABLE `LogConnexion` (
  `RefLog` int(11) NOT NULL,
  `RefUsers` int(11) NOT NULL,
  `IP` text NOT NULL,
  `LogH` time NOT NULL,
  `LogoutH` time NOT NULL,
  `DateLog` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `operations`
-- (See below for the actual view)
--
CREATE TABLE `operations` (
`RefOperations` int(11)
,`RefAgency` int(11)
,`NameAgency` text
,`RefPays` int(11)
,`RefProduit` int(11)
,`NameProduit` text
,`RefType` int(11)
,`NameType` text
,`NameClient` text
,`NumCompte` text
,`MontantVersement` text
,`Remarque` text
,`Approve2_Time` date
,`login` text
,`SentFromAgency` int(11)
,`datePayement` datetime
,`Approve2_Id` int(11)
,`Reset_Id` int(11)
,`Approve1_Time` date
,`Bordereau` text
,`Reset_At` datetime
,`DateValidate` date
,`RefValidate` int(11)
,`TypeAppro` int(11)
,`TypeRetrait` int(11)
,`uniqid` text
,`ValidateDate` datetime
,`Validate` int(11)
,`RefCaisse` int(11)
,`NameCaisse` text
,`Insert_Time` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `operationsOLD`
-- (See below for the actual view)
--
CREATE TABLE `operationsOLD` (
`RefOperations` int(11)
,`RefAgency` int(11)
,`NameAgency` text
,`RefProduit` int(11)
,`NameProduit` text
,`RefType` int(11)
,`NameType` text
,`NameClient` text
,`NumCompte` text
,`MontantVersement` text
,`Remarque` text
,`Approve2_Time` date
,`login` text
,`SentFromAgency` int(11)
,`datePayement` datetime
,`Approve2_Id` int(11)
,`Reset_Id` int(11)
,`Approve1_Time` date
,`Bordereau` text
,`Reset_At` datetime
,`DateValidate` date
,`RefValidate` int(11)
,`TypeAppro` int(11)
,`TypeRetrait` int(11)
,`uniqid` text
,`ValidateDate` datetime
,`Validate` int(11)
,`RefCaisse` int(11)
,`NameCaisse` text
,`Insert_Time` date
);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `RefUsers` int(11) NOT NULL,
  `access` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleAgency`
--

CREATE TABLE `TbleAgency` (
  `RefAgency` int(11) NOT NULL,
  `NameAgency` text NOT NULL,
  `TelAgence` text DEFAULT NULL,
  `RefPays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleAppro`
--

CREATE TABLE `TbleAppro` (
  `RefAppro` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `MontantAppro` text NOT NULL,
  `DateAppro` datetime DEFAULT current_timestamp(),
  `RefUsers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleBanque`
--

CREATE TABLE `TbleBanque` (
  `RefBanque` int(11) NOT NULL,
  `NameBanque` text NOT NULL,
  `RefPays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleBilletage`
--

CREATE TABLE `TbleBilletage` (
  `RefBilletage` int(11) NOT NULL,
  `RefOperations` int(11) NOT NULL,
  `a1` text NOT NULL,
  `a2` text NOT NULL,
  `b1` text NOT NULL,
  `b2` text NOT NULL,
  `c1` text NOT NULL,
  `c2` text NOT NULL,
  `d1` text NOT NULL,
  `d2` text NOT NULL,
  `e1` text NOT NULL,
  `e2` text NOT NULL,
  `f1` text NOT NULL,
  `f2` text NOT NULL,
  `g1` text NOT NULL,
  `g2` text NOT NULL,
  `h1` text NOT NULL,
  `h2` text NOT NULL,
  `i1` text NOT NULL,
  `i2` text NOT NULL,
  `j1` text NOT NULL,
  `j2` text NOT NULL,
  `k1` text NOT NULL,
  `k2` text NOT NULL,
  `l1` text NOT NULL,
  `l2` text NOT NULL,
  `m1` text NOT NULL,
  `m2` text NOT NULL,
  `dateBielletage` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleCaisse`
--

CREATE TABLE `TbleCaisse` (
  `RefCaisse` int(11) NOT NULL,
  `NameCaisse` text NOT NULL,
  `RefAgency` int(11) NOT NULL,
  `NUMCOMPTE` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleChmod`
--

CREATE TABLE `TbleChmod` (
  `RefChmod` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `RefUsers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleChmodAppro`
--

CREATE TABLE `TbleChmodAppro` (
  `RefChmodAppro` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `RefUsers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleChmodProduit`
--

CREATE TABLE `TbleChmodProduit` (
  `RefChmodProduit` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `RefProduit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleCompte`
--

CREATE TABLE `TbleCompte` (
  `RefCompte` int(11) NOT NULL,
  `RefAgency` int(11) NOT NULL,
  `SoldeCompte` text NOT NULL,
  `DateSolde` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleDays`
--

CREATE TABLE `TbleDays` (
  `RefDays` int(11) NOT NULL,
  `NameDays` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleJobs`
--

CREATE TABLE `TbleJobs` (
  `id` int(11) NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleOperations`
--

CREATE TABLE `TbleOperations` (
  `RefOperations` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `NumCompte` text NOT NULL,
  `NameClient` text NOT NULL,
  `MontantVersement` text NOT NULL,
  `Remarque` text DEFAULT NULL,
  `Insert_Id` int(11) NOT NULL,
  `Insert_Time` date NOT NULL,
  `Approve1_Id` int(11) DEFAULT NULL,
  `Approve1_Time` date DEFAULT NULL,
  `Approve2_Id` int(11) DEFAULT NULL,
  `Approve2_Time` date DEFAULT NULL,
  `datePayement` datetime DEFAULT current_timestamp(),
  `Bordereau` text DEFAULT NULL,
  `NameDeposant` text DEFAULT NULL,
  `TelDeposant` text NOT NULL,
  `RefType` int(11) NOT NULL,
  `Reset_Id` int(11) DEFAULT NULL,
  `Reset_At` datetime DEFAULT NULL,
  `Validate` int(11) DEFAULT 1,
  `DateValidate` date DEFAULT NULL,
  `RefValidate` int(11) DEFAULT NULL,
  `TypeAppro` int(11) DEFAULT NULL,
  `RefProduit` int(11) DEFAULT NULL,
  `TypeRetrait` int(11) DEFAULT NULL,
  `uniqid` text DEFAULT NULL,
  `ValidateDate` datetime DEFAULT NULL,
  `SentFromAgency` int(11) DEFAULT NULL,
  `RefPays` int(11) NOT NULL,
  `fraisTimbre` text DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleOuverture`
--

CREATE TABLE `TbleOuverture` (
  `RefCaisse` int(11) NOT NULL,
  `RefDays` int(11) NOT NULL,
  `HeureDebut` time NOT NULL,
  `HeureFin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleProduit`
--

CREATE TABLE `TbleProduit` (
  `RefProduit` int(11) NOT NULL,
  `NameProduit` text NOT NULL,
  `RefBanque` int(11) NOT NULL,
  `StatutProduit` varchar(50) NOT NULL,
  `img` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleRapportOp`
--

CREATE TABLE `TbleRapportOp` (
  `RefRapport` int(11) NOT NULL,
  `SoldeOvEspeces` text NOT NULL,
  `SoldeOvOmni` text NOT NULL,
  `Versement` text DEFAULT NULL,
  `Retrait` text DEFAULT NULL,
  `ApproOmni` text DEFAULT NULL,
  `SoldeFrEspeces` text NOT NULL,
  `SoldeFrOmni` text NOT NULL,
  `Date` date NOT NULL,
  `RefCaisse` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleRemittance`
--

CREATE TABLE `TbleRemittance` (
  `RefRemittance` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `RefProduit` int(11) NOT NULL,
  `RefType` int(11) NOT NULL,
  `NumPhone` text NOT NULL,
  `NomComplet` text NOT NULL,
  `MontantTransaction` text NOT NULL,
  `Insert_id` int(11) NOT NULL,
  `Insert_time` datetime DEFAULT current_timestamp(),
  `Reset_Id` int(11) DEFAULT NULL,
  `Reset_At` date DEFAULT NULL,
  `Validate` int(11) DEFAULT 1,
  `DateValidate` date DEFAULT NULL,
  `RefValidate` int(11) DEFAULT NULL,
  `SentFromAgency` int(11) DEFAULT NULL,
  `RefPays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleSolde`
--

CREATE TABLE `TbleSolde` (
  `RefSolde` int(11) NOT NULL,
  `RefCaisse` int(11) NOT NULL,
  `Solde` text NOT NULL,
  `DateSolde` datetime DEFAULT current_timestamp(),
  `RefUsers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleStatut`
--

CREATE TABLE `TbleStatut` (
  `RefStatut` int(11) NOT NULL,
  `Name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleType`
--

CREATE TABLE `TbleType` (
  `RefType` int(11) NOT NULL,
  `NameType` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleTypeAppro`
--

CREATE TABLE `TbleTypeAppro` (
  `RefTypeAppro` int(11) NOT NULL,
  `NameTypeAppro` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleTypeRetrait`
--

CREATE TABLE `TbleTypeRetrait` (
  `RefTypeRetrait` int(11) NOT NULL,
  `NameTypeRetrait` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TbleUsers`
--

CREATE TABLE `TbleUsers` (
  `RefUsers` int(11) NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `NomUsers` text NOT NULL,
  `PrenomUsers` text NOT NULL,
  `email` text NOT NULL,
  `RefStatut` int(11) NOT NULL,
  `auth_confirm` text DEFAULT NULL,
  `AgenceUsers` text DEFAULT NULL,
  `log` int(11) DEFAULT 1,
  `LastLogID` int(11) DEFAULT NULL,
  `secret` text DEFAULT NULL,
  `RefPays` int(11) DEFAULT NULL,
  `RefBanque` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbllinks`
--

CREATE TABLE `tbllinks` (
  `RefLinks` int(11) NOT NULL,
  `url` text NOT NULL,
  `url_name` text NOT NULL,
  `btn` text NOT NULL,
  `target` int(11) DEFAULT 0,
  `RefPays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpays`
--

CREATE TABLE `tblpays` (
  `RefPays` int(11) NOT NULL,
  `nomPays` varchar(50) NOT NULL,
  `logo` text DEFAULT NULL,
  `EmailAlert` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `LogConnexion`
--
ALTER TABLE `LogConnexion`
  ADD PRIMARY KEY (`RefLog`),
  ADD KEY `RefUsers` (`RefUsers`);

--
-- Indexes for table `TbleAgency`
--
ALTER TABLE `TbleAgency`
  ADD PRIMARY KEY (`RefAgency`);

--
-- Indexes for table `TbleAppro`
--
ALTER TABLE `TbleAppro`
  ADD PRIMARY KEY (`RefAppro`),
  ADD KEY `RefCaisse` (`RefCaisse`);

--
-- Indexes for table `TbleBanque`
--
ALTER TABLE `TbleBanque`
  ADD PRIMARY KEY (`RefBanque`);

--
-- Indexes for table `TbleBilletage`
--
ALTER TABLE `TbleBilletage`
  ADD PRIMARY KEY (`RefBilletage`),
  ADD KEY `RefOperations` (`RefOperations`);

--
-- Indexes for table `TbleCaisse`
--
ALTER TABLE `TbleCaisse`
  ADD PRIMARY KEY (`RefCaisse`),
  ADD KEY `RefAgency` (`RefAgency`);

--
-- Indexes for table `TbleChmod`
--
ALTER TABLE `TbleChmod`
  ADD PRIMARY KEY (`RefChmod`),
  ADD KEY `RefCaisse` (`RefCaisse`),
  ADD KEY `RefUsers` (`RefUsers`);

--
-- Indexes for table `TbleChmodAppro`
--
ALTER TABLE `TbleChmodAppro`
  ADD PRIMARY KEY (`RefChmodAppro`),
  ADD KEY `RefCaisse` (`RefCaisse`),
  ADD KEY `RefUsers` (`RefUsers`);

--
-- Indexes for table `TbleChmodProduit`
--
ALTER TABLE `TbleChmodProduit`
  ADD PRIMARY KEY (`RefChmodProduit`),
  ADD KEY `RefProduit` (`RefProduit`),
  ADD KEY `TbleChmodProduit_ibfk_2` (`RefCaisse`);

--
-- Indexes for table `TbleCompte`
--
ALTER TABLE `TbleCompte`
  ADD PRIMARY KEY (`RefCompte`),
  ADD KEY `RefAgency` (`RefAgency`);

--
-- Indexes for table `TbleDays`
--
ALTER TABLE `TbleDays`
  ADD PRIMARY KEY (`RefDays`);

--
-- Indexes for table `TbleJobs`
--
ALTER TABLE `TbleJobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `TbleOperations`
--
ALTER TABLE `TbleOperations`
  ADD PRIMARY KEY (`RefOperations`);

--
-- Indexes for table `TbleOuverture`
--
ALTER TABLE `TbleOuverture`
  ADD PRIMARY KEY (`RefCaisse`,`RefDays`),
  ADD KEY `tbleouverture_ibfk_2` (`RefDays`);

--
-- Indexes for table `TbleProduit`
--
ALTER TABLE `TbleProduit`
  ADD PRIMARY KEY (`RefProduit`),
  ADD KEY `RefBanque` (`RefBanque`);

--
-- Indexes for table `TbleRapportOp`
--
ALTER TABLE `TbleRapportOp`
  ADD PRIMARY KEY (`RefRapport`),
  ADD KEY `RefCaisse` (`RefCaisse`);

--
-- Indexes for table `TbleRemittance`
--
ALTER TABLE `TbleRemittance`
  ADD PRIMARY KEY (`RefRemittance`);

--
-- Indexes for table `TbleSolde`
--
ALTER TABLE `TbleSolde`
  ADD PRIMARY KEY (`RefSolde`),
  ADD KEY `RefCaisse` (`RefCaisse`),
  ADD KEY `RefUsers` (`RefUsers`);

--
-- Indexes for table `TbleStatut`
--
ALTER TABLE `TbleStatut`
  ADD PRIMARY KEY (`RefStatut`);

--
-- Indexes for table `TbleTypeAppro`
--
ALTER TABLE `TbleTypeAppro`
  ADD PRIMARY KEY (`RefTypeAppro`);

--
-- Indexes for table `TbleTypeRetrait`
--
ALTER TABLE `TbleTypeRetrait`
  ADD PRIMARY KEY (`RefTypeRetrait`);

--
-- Indexes for table `TbleUsers`
--
ALTER TABLE `TbleUsers`
  ADD PRIMARY KEY (`RefUsers`),
  ADD KEY `RefStatut` (`RefStatut`);

--
-- Indexes for table `tbllinks`
--
ALTER TABLE `tbllinks`
  ADD PRIMARY KEY (`RefLinks`);

--
-- Indexes for table `tblpays`
--
ALTER TABLE `tblpays`
  ADD PRIMARY KEY (`RefPays`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `LogConnexion`
--
ALTER TABLE `LogConnexion`
  MODIFY `RefLog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleAgency`
--
ALTER TABLE `TbleAgency`
  MODIFY `RefAgency` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleAppro`
--
ALTER TABLE `TbleAppro`
  MODIFY `RefAppro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleBanque`
--
ALTER TABLE `TbleBanque`
  MODIFY `RefBanque` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleBilletage`
--
ALTER TABLE `TbleBilletage`
  MODIFY `RefBilletage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleCaisse`
--
ALTER TABLE `TbleCaisse`
  MODIFY `RefCaisse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleChmod`
--
ALTER TABLE `TbleChmod`
  MODIFY `RefChmod` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleChmodAppro`
--
ALTER TABLE `TbleChmodAppro`
  MODIFY `RefChmodAppro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleChmodProduit`
--
ALTER TABLE `TbleChmodProduit`
  MODIFY `RefChmodProduit` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleCompte`
--
ALTER TABLE `TbleCompte`
  MODIFY `RefCompte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleDays`
--
ALTER TABLE `TbleDays`
  MODIFY `RefDays` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleJobs`
--
ALTER TABLE `TbleJobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleOperations`
--
ALTER TABLE `TbleOperations`
  MODIFY `RefOperations` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleProduit`
--
ALTER TABLE `TbleProduit`
  MODIFY `RefProduit` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleRapportOp`
--
ALTER TABLE `TbleRapportOp`
  MODIFY `RefRapport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleRemittance`
--
ALTER TABLE `TbleRemittance`
  MODIFY `RefRemittance` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleSolde`
--
ALTER TABLE `TbleSolde`
  MODIFY `RefSolde` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleStatut`
--
ALTER TABLE `TbleStatut`
  MODIFY `RefStatut` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleTypeAppro`
--
ALTER TABLE `TbleTypeAppro`
  MODIFY `RefTypeAppro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleTypeRetrait`
--
ALTER TABLE `TbleTypeRetrait`
  MODIFY `RefTypeRetrait` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TbleUsers`
--
ALTER TABLE `TbleUsers`
  MODIFY `RefUsers` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbllinks`
--
ALTER TABLE `tbllinks`
  MODIFY `RefLinks` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `operations`
--
DROP TABLE IF EXISTS `operations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cp1146011p43`@`localhost` SQL SECURITY DEFINER VIEW `operations`  AS   (select `TbleOperations`.`RefOperations` AS `RefOperations`,`TbleAgency`.`RefAgency` AS `RefAgency`,`TbleAgency`.`NameAgency` AS `NameAgency`,`TbleAgency`.`RefPays` AS `RefPays`,`TbleProduit`.`RefProduit` AS `RefProduit`,`TbleProduit`.`NameProduit` AS `NameProduit`,`TbleType`.`RefType` AS `RefType`,`TbleType`.`NameType` AS `NameType`,`TbleOperations`.`NameClient` AS `NameClient`,`TbleOperations`.`NumCompte` AS `NumCompte`,`TbleOperations`.`MontantVersement` AS `MontantVersement`,`TbleOperations`.`Remarque` AS `Remarque`,`TbleOperations`.`Approve2_Time` AS `Approve2_Time`,`TbleUsers`.`login` AS `login`,`TbleOperations`.`SentFromAgency` AS `SentFromAgency`,`TbleOperations`.`datePayement` AS `datePayement`,`TbleOperations`.`Approve2_Id` AS `Approve2_Id`,`TbleOperations`.`Reset_Id` AS `Reset_Id`,`TbleOperations`.`Approve1_Time` AS `Approve1_Time`,`TbleOperations`.`Bordereau` AS `Bordereau`,`TbleOperations`.`Reset_At` AS `Reset_At`,`TbleOperations`.`DateValidate` AS `DateValidate`,`TbleOperations`.`RefValidate` AS `RefValidate`,`TbleOperations`.`TypeAppro` AS `TypeAppro`,`TbleOperations`.`TypeRetrait` AS `TypeRetrait`,`TbleOperations`.`uniqid` AS `uniqid`,`TbleOperations`.`ValidateDate` AS `ValidateDate`,`TbleOperations`.`Validate` AS `Validate`,`TbleOperations`.`RefCaisse` AS `RefCaisse`,`TbleCaisse`.`NameCaisse` AS `NameCaisse`,`TbleOperations`.`Insert_Time` AS `Insert_Time` from (((((`TbleOperations` join `TbleType` on(`TbleType`.`RefType` = `TbleOperations`.`RefType`)) join `TbleCaisse` on(`TbleCaisse`.`RefCaisse` = `TbleOperations`.`RefCaisse`)) join `TbleAgency` on(`TbleAgency`.`RefAgency` = `TbleCaisse`.`RefAgency`)) left join `TbleProduit` on(`TbleProduit`.`RefProduit` = `TbleOperations`.`RefProduit`)) join `TbleUsers` on(`TbleUsers`.`RefUsers` = `TbleOperations`.`Insert_Id`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `operationsOLD`
--
DROP TABLE IF EXISTS `operationsOLD`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cp1146011p43`@`localhost` SQL SECURITY DEFINER VIEW `operationsOLD`  AS   (select `TbleOperations`.`RefOperations` AS `RefOperations`,`TbleAgency`.`RefAgency` AS `RefAgency`,`TbleAgency`.`NameAgency` AS `NameAgency`,`TbleProduit`.`RefProduit` AS `RefProduit`,`TbleProduit`.`NameProduit` AS `NameProduit`,`TbleType`.`RefType` AS `RefType`,`TbleType`.`NameType` AS `NameType`,`TbleOperations`.`NameClient` AS `NameClient`,`TbleOperations`.`NumCompte` AS `NumCompte`,`TbleOperations`.`MontantVersement` AS `MontantVersement`,`TbleOperations`.`Remarque` AS `Remarque`,`TbleOperations`.`Approve2_Time` AS `Approve2_Time`,`TbleUsers`.`login` AS `login`,`TbleOperations`.`SentFromAgency` AS `SentFromAgency`,`TbleOperations`.`datePayement` AS `datePayement`,`TbleOperations`.`Approve2_Id` AS `Approve2_Id`,`TbleOperations`.`Reset_Id` AS `Reset_Id`,`TbleOperations`.`Approve1_Time` AS `Approve1_Time`,`TbleOperations`.`Bordereau` AS `Bordereau`,`TbleOperations`.`Reset_At` AS `Reset_At`,`TbleOperations`.`DateValidate` AS `DateValidate`,`TbleOperations`.`RefValidate` AS `RefValidate`,`TbleOperations`.`TypeAppro` AS `TypeAppro`,`TbleOperations`.`TypeRetrait` AS `TypeRetrait`,`TbleOperations`.`uniqid` AS `uniqid`,`TbleOperations`.`ValidateDate` AS `ValidateDate`,`TbleOperations`.`Validate` AS `Validate`,`TbleOperations`.`RefCaisse` AS `RefCaisse`,`TbleCaisse`.`NameCaisse` AS `NameCaisse`,`TbleOperations`.`Insert_Time` AS `Insert_Time` from (((((`TbleOperations` join `TbleType` on(`TbleType`.`RefType` = `TbleOperations`.`RefType`)) join `TbleCaisse` on(`TbleCaisse`.`RefCaisse` = `TbleOperations`.`RefCaisse`)) join `TbleAgency` on(`TbleAgency`.`RefAgency` = `TbleCaisse`.`RefAgency`)) left join `TbleProduit` on(`TbleProduit`.`RefProduit` = `TbleOperations`.`RefProduit`)) join `TbleUsers` on(`TbleUsers`.`RefUsers` = `TbleOperations`.`Insert_Id`)))  ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `LogConnexion`
--
ALTER TABLE `LogConnexion`
  ADD CONSTRAINT `LogConnexion_ibfk_1` FOREIGN KEY (`RefUsers`) REFERENCES `TbleUsers` (`RefUsers`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleAppro`
--
ALTER TABLE `TbleAppro`
  ADD CONSTRAINT `tbleappro_ibfk_1` FOREIGN KEY (`RefCaisse`) REFERENCES `TbleCaisse` (`RefCaisse`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleBilletage`
--
ALTER TABLE `TbleBilletage`
  ADD CONSTRAINT `tblebilletage_ibfk_1` FOREIGN KEY (`RefOperations`) REFERENCES `TbleOperations` (`RefOperations`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleCaisse`
--
ALTER TABLE `TbleCaisse`
  ADD CONSTRAINT `tblecaisse_ibfk_1` FOREIGN KEY (`RefAgency`) REFERENCES `TbleAgency` (`RefAgency`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleChmod`
--
ALTER TABLE `TbleChmod`
  ADD CONSTRAINT `tblechmod_ibfk_1` FOREIGN KEY (`RefCaisse`) REFERENCES `TbleCaisse` (`RefCaisse`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tblechmod_ibfk_2` FOREIGN KEY (`RefUsers`) REFERENCES `TbleUsers` (`RefUsers`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleChmodAppro`
--
ALTER TABLE `TbleChmodAppro`
  ADD CONSTRAINT `TbleChmodAppro_ibfk_1` FOREIGN KEY (`RefCaisse`) REFERENCES `TbleCaisse` (`RefCaisse`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `TbleChmodAppro_ibfk_2` FOREIGN KEY (`RefUsers`) REFERENCES `TbleUsers` (`RefUsers`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleCompte`
--
ALTER TABLE `TbleCompte`
  ADD CONSTRAINT `TbleCompte_ibfk_1` FOREIGN KEY (`RefAgency`) REFERENCES `TbleAgency` (`RefAgency`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleRapportOp`
--
ALTER TABLE `TbleRapportOp`
  ADD CONSTRAINT `tblerapportop_ibfk_1` FOREIGN KEY (`RefCaisse`) REFERENCES `TbleCaisse` (`RefCaisse`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TbleSolde`
--
ALTER TABLE `TbleSolde`
  ADD CONSTRAINT `TbleSolde_ibfk_1` FOREIGN KEY (`RefCaisse`) REFERENCES `TbleCaisse` (`RefCaisse`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `TbleSolde_ibfk_2` FOREIGN KEY (`RefUsers`) REFERENCES `TbleUsers` (`RefUsers`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

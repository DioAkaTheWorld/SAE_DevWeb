-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 09 nov. 2024 à 20:40
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nrv`
--

-- --------------------------------------------------------

--
-- Structure de la table `artiste`
--

CREATE TABLE `artiste` (
                           `id` int(5) NOT NULL,
                           `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `artiste`
--

INSERT INTO `artiste` (`id`, `nom`) VALUES
                                        (1, 'John Coltrane'),
                                        (2, 'Miles Davis'),
                                        (3, 'Bill Evans'),
                                        (4, 'Jimmy Page'),
                                        (5, 'Robert Plant'),
                                        (6, 'B.B. King'),
                                        (7, 'Muddy Waters'),
                                        (8, 'Yo-Yo Ma'),
                                        (9, 'Daft Punk'),
                                        (10, 'Carl Cox'),
                                        (11, 'David Guetta');

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
                         `id` int(5) NOT NULL,
                         `chemin_fichier` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id`, `chemin_fichier`) VALUES
                                                 (1, 'jazz1.jpg'),
                                                 (2, 'jazz2.jpg'),
                                                 (3, 'jazz3.jpg'),
                                                 (4, 'rock1.jpg'),
                                                 (5, 'rock2.jpg'),
                                                 (6, 'blues1.jpg'),
                                                 (7, 'blues2.jpg'),
                                                 (8, 'classical1.jpg'),
                                                 (9, 'electro1.jpg'),
                                                 (10, 'electro2.jpg'),
                                                 (11, 'house1.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `image2lieu`
--

CREATE TABLE `image2lieu` (
                              `id_image` int(5) NOT NULL,
                              `id_lieu` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `image2lieu`
--

INSERT INTO `image2lieu` (`id_image`, `id_lieu`) VALUES
                                                     (1, 1),
                                                     (2, 1),
                                                     (3, 1),
                                                     (4, 2),
                                                     (5, 2),
                                                     (6, 3),
                                                     (7, 3),
                                                     (8, 4),
                                                     (9, 5),
                                                     (10, 5),
                                                     (11, 5);

-- --------------------------------------------------------

--
-- Structure de la table `lieu`
--

CREATE TABLE `lieu` (
                        `id` int(5) NOT NULL,
                        `nom` varchar(50) NOT NULL,
                        `adresse` varchar(50) NOT NULL,
                        `nbPlaceAssises` int(10) DEFAULT NULL,
                        `nbPlacesDebout` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `lieu`
--

INSERT INTO `lieu` (`id`, `nom`, `adresse`, `nbPlaceAssises`, `nbPlacesDebout`) VALUES
                                                                                    (1, 'Le Jazz Club', '123 Jazz St', 100, 50),
                                                                                    (2, 'Rock Arena', '456 Rock Ave', 200, 120),
                                                                                    (3, 'Blues House', '789 Blues Rd', 80, 40),
                                                                                    (4, 'Grand Théâtre', '101 Classical Blvd', 200, 0),
                                                                                    (5, 'Electro Warehouse', '202 Techno Lane', 0, 10);

-- --------------------------------------------------------

--
-- Structure de la table `soiree`
--

CREATE TABLE `soiree` (
                          `id` int(5) NOT NULL,
                          `nom` varchar(50) NOT NULL,
                          `thematique` varchar(50) DEFAULT NULL,
                          `date` date DEFAULT NULL,
                          `horaire_debut` time DEFAULT NULL,
                          `horaire_fin` time DEFAULT NULL,
                          `id_lieu` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `soiree`
--

INSERT INTO `soiree` (`id`, `nom`, `thematique`, `date`, `horaire_debut`, `horaire_fin`, `id_lieu`) VALUES
                                                                                                        (1, 'Best of rock', 'Rock', '2024-11-27', '22:00:00', '00:30:00', 1),
                                                                                                        (2, 'Best of country music', 'Country', '2024-11-26', '19:20:00', '23:30:00', 2),
                                                                                                        (3, 'Best of Reggae', 'Reggae', '2024-11-25', '21:00:00', '22:00:00', 3);

-- --------------------------------------------------------

--
-- Structure de la table `soiree2spectacle`
--

CREATE TABLE `soiree2spectacle` (
                                    `id_soiree` int(5) NOT NULL,
                                    `id_spectacle` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `soiree2spectacle`
--

INSERT INTO `soiree2spectacle` (`id_soiree`, `id_spectacle`) VALUES
                                                                 (1, 1),
                                                                 (1, 2),
                                                                 (2, 3),
                                                                 (2, 4),
                                                                 (3, 5),
                                                                 (3, 6);

-- --------------------------------------------------------

--
-- Structure de la table `spectacle`
--

CREATE TABLE `spectacle` (
                             `id` int(5) NOT NULL,
                             `titre` varchar(50) NOT NULL,
                             `description` varchar(200) DEFAULT NULL,
                             `chemin_fichier` varchar(100) DEFAULT NULL,
                             `horaire` time DEFAULT NULL,
                             `duree` time DEFAULT NULL,
                             `style` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `spectacle`
--

INSERT INTO `spectacle` (`id`, `titre`, `description`, `chemin_fichier`, `horaire`, `duree`, `style`) VALUES
                                                                                                          (1, 'Jazz Band Live', 'A famous jazz band live in concert.', 'jazzlive.jpg', '20:00:00', '02:00:00', 'Jazz'),
                                                                                                          (2, 'Acoustic Jazz Trio', 'Smooth acoustic jazz trio.', 'acousticjazz.jpg', '21:00:00', '03:30:00', 'Jazz'),
                                                                                                          (3, 'Jazz Solo Piano', 'Relaxing solo piano jazz.', 'solopiano.jpg', '22:30:00', '01:00:00', 'Jazz'),
                                                                                                          (4, 'Rock Legends', 'Classic rock covers.', 'rocklegends.jpg', '20:30:00', '01:00:00', 'Rock'),
                                                                                                          (5, 'Indie Rock Band', 'Local indie rock band performance.', 'indierock.jpg', '22:00:00', '00:45:00', 'Rock'),
                                                                                                          (6, 'Blues Guitar', 'Blues guitar solo performance.', 'bluesguitar.jpg', '19:00:00', '02:20:00', 'Blues'),
                                                                                                          (7, 'Soulful Blues', 'Deep, soulful blues.', 'soulblues.jpg', '20:30:00', '00:30:00', 'Blues'),
                                                                                                          (8, 'Classical Quartet', 'String quartet performance.', 'classicalquartet.jpg', '19:45:00', '01:20:00', 'Classical'),
                                                                                                          (9, 'Electro DJ Set', 'Dance to electronic beats.', 'electro.jpg', '21:30:00', '02:00:00', 'Electronic'),
                                                                                                          (10, 'Techno Night', 'Non-stop techno music.', 'techno.jpg', '23:00:00', '1:05:00', 'Electronic'),
                                                                                                          (11, 'House Party', 'House music by top DJs.', 'house.jpg', '01:00:00', '04:00:00', 'Electronic');

-- --------------------------------------------------------

--
-- Structure de la table `spectacle2artiste`
--

CREATE TABLE `spectacle2artiste` (
                                     `id_spectacle` int(5) NOT NULL,
                                     `id_artiste` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `spectacle2artiste`
--

INSERT INTO `spectacle2artiste` (`id_spectacle`, `id_artiste`) VALUES
                                                                   (1, 1),
                                                                   (1, 2),
                                                                   (2, 3),
                                                                   (3, 1),
                                                                   (4, 4),
                                                                   (5, 5),
                                                                   (6, 6),
                                                                   (7, 7),
                                                                   (8, 8),
                                                                   (9, 9),
                                                                   (10, 10),
                                                                   (11, 11);

-- --------------------------------------------------------

--
-- Structure de la table `spectacle2image`
--

CREATE TABLE `spectacle2image` (
                                   `id_spectacle` int(5) NOT NULL,
                                   `id_image` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `spectacle2image`
--

INSERT INTO `spectacle2image` (`id_spectacle`, `id_image`) VALUES
                                                               (1, 1),
                                                               (2, 2),
                                                               (3, 3),
                                                               (4, 4),
                                                               (5, 5),
                                                               (6, 6),
                                                               (7, 7),
                                                               (8, 8),
                                                               (9, 9),
                                                               (10, 10),
                                                               (11, 11);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
                        `id` int(5) NOT NULL,
                        `email` varchar(50) NOT NULL,
                        `hash` varchar(256) NOT NULL,
                        `role` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `hash`, `role`) VALUES
                                                       (1, 'user1@mail.com', '$2y$12$FSUq4wa9exgiHC/58tbpo.IGJ/1BxLLR54txC/GZhhbF.SOJqa8Qq', 1),
                                                       (2, 'user2@mail.com', '$2y$12$SZDTxmdvrvEBR4ynZ/OqveQkiJK3ySVcZhvZBiOpNfjFpseBdf4VS', 1),
                                                       (3, 'user3@mail.com', '$2y$12$WFClWVtZkGOvS3zIOKEDYOqImLUpEpiGyrmRShIvTsaJRoPTsqu0q', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `artiste`
--
ALTER TABLE `artiste`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `image`
--
ALTER TABLE `image`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `image2lieu`
--
ALTER TABLE `image2lieu`
    ADD PRIMARY KEY (`id_image`,`id_lieu`),
    ADD KEY `image2lieu_ibfk_2` (`id_lieu`);

--
-- Index pour la table `lieu`
--
ALTER TABLE `lieu`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `soiree`
--
ALTER TABLE `soiree`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `soiree2spectacle`
--
ALTER TABLE `soiree2spectacle`
    ADD PRIMARY KEY (`id_soiree`,`id_spectacle`),
    ADD KEY `soiree2spectacle_ibfk_2` (`id_spectacle`);

--
-- Index pour la table `spectacle`
--
ALTER TABLE `spectacle`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `spectacle2artiste`
--
ALTER TABLE `spectacle2artiste`
    ADD PRIMARY KEY (`id_spectacle`,`id_artiste`),
    ADD KEY `spectacle2artiste_ibfk_2` (`id_artiste`);

--
-- Index pour la table `spectacle2image`
--
ALTER TABLE `spectacle2image`
    ADD PRIMARY KEY (`id_spectacle`,`id_image`),
    ADD KEY `spectacle2image_ibfk_2` (`id_image`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `artiste`
--
ALTER TABLE `artiste`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `lieu`
--
ALTER TABLE `lieu`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `soiree`
--
ALTER TABLE `soiree`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `spectacle`
--
ALTER TABLE `spectacle`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
    MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `image2lieu`
--
ALTER TABLE `image2lieu`
    ADD CONSTRAINT `image2lieu_ibfk_1` FOREIGN KEY (`id_image`) REFERENCES `image` (`id`),
    ADD CONSTRAINT `image2lieu_ibfk_2` FOREIGN KEY (`id_lieu`) REFERENCES `lieu` (`id`);

--
-- Contraintes pour la table `soiree2spectacle`
--
ALTER TABLE `soiree2spectacle`
    ADD CONSTRAINT `soiree2spectacle_ibfk_1` FOREIGN KEY (`id_soiree`) REFERENCES `soiree` (`id`),
    ADD CONSTRAINT `soiree2spectacle_ibfk_2` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`);

--
-- Contraintes pour la table `spectacle2artiste`
--
ALTER TABLE `spectacle2artiste`
    ADD CONSTRAINT `spectacle2artiste_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`),
    ADD CONSTRAINT `spectacle2artiste_ibfk_2` FOREIGN KEY (`id_artiste`) REFERENCES `artiste` (`id`);

--
-- Contraintes pour la table `spectacle2image`
--
ALTER TABLE `spectacle2image`
    ADD CONSTRAINT `spectacle2image_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`),
    ADD CONSTRAINT `spectacle2image_ibfk_2` FOREIGN KEY (`id_image`) REFERENCES `image` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

    -- phpMyAdmin SQL Dump
    -- version 5.2.1
    -- https://www.phpmyadmin.net/
    --
    -- Hôte : 127.0.0.1
    -- Généré le : jeu. 07 nov. 2024 à 10:32
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

    DROP TABLE IF EXISTS `artiste`;
    CREATE TABLE `artiste` (
                               `id` int(200) NOT NULL AUTO_INCREMENT,
                               `nom` varchar(50) NOT NULL,
                               PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `image`
    --

    DROP TABLE IF EXISTS `image`;
    CREATE TABLE `image` (
                             `id` int(200) NOT NULL AUTO_INCREMENT,
                             `chemin_fichier` varchar(50) NOT NULL,
                             PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `image2lieu`
    --

    DROP TABLE IF EXISTS `image2lieu`;
    CREATE TABLE `image2lieu` (
                                  `id_image` int(200) NOT NULL,
                                  `id_lieu` int(200) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `lieu`
    --

    DROP TABLE IF EXISTS `lieu`;
    CREATE TABLE `lieu` (
                            `id` int(200) NOT NULL AUTO_INCREMENT,
                            `nom` varchar(50) NOT NULL,
                            `adresse` varchar(50) NOT NULL,
                            `nbPlaceAssises` int(255) DEFAULT NULL,
                            `nbPlacesDebout` int(255) DEFAULT NULL,
                            PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `soiree`
    --

    DROP TABLE IF EXISTS `soiree`;
    CREATE TABLE `soiree` (
                              `id` int(200) NOT NULL AUTO_INCREMENT,
                              `nom` varchar(50) NOT NULL,
                              `thematique` varchar(50) DEFAULT NULL,
                              `date` date DEFAULT NULL,
                              `horaire_debut` time DEFAULT NULL,
                              `horaire_fin` time DEFAULT NULL,
                              `id_lieu` int(200) DEFAULT NULL,
                              PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `soiree2spectacle`
    --

    DROP TABLE IF EXISTS `soiree2spectacle`;
    CREATE TABLE `soiree2spectacle` (
                                        `id_soiree` int(200) NOT NULL,
                                        `id_spectacle` int(200) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `spectacle`
    --

    DROP TABLE IF EXISTS `spectacle`;
    CREATE TABLE `spectacle` (
                                 `id` int(200) NOT NULL AUTO_INCREMENT,
                                 `titre` varchar(50) NOT NULL,
                                 `description` varchar(200) DEFAULT NULL,
                                 `chemin_fichier` varchar(100) DEFAULT NULL,
                                 `horaire` time DEFAULT NULL,
                                 `duree` int(11) DEFAULT NULL,
                                 `style` varchar(50) DEFAULT NULL,
                                 PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `spectacle2artiste`
    --

    DROP TABLE IF EXISTS `spectacle2artiste`;
    CREATE TABLE `spectacle2artiste` (
                                         `id_spectacle` int(200) NOT NULL,
                                         `id_artiste` int(200) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `spectacle2image`
    --

    DROP TABLE IF EXISTS `spectacle2image`;
    CREATE TABLE `spectacle2image` (
                                       `id_spectacle` int(200) NOT NULL,
                                       `id_image` int(200) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    -- --------------------------------------------------------

    --
    -- Structure de la table `user`
    --

    DROP TABLE IF EXISTS `user`;
    CREATE TABLE `user` (
                            `id` int(200) NOT NULL AUTO_INCREMENT,
                            `email` varchar(50) NOT NULL,
                            `hash` varchar(256) NOT NULL,
                            `role` int(100) NOT NULL,
                            PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

    --
    -- Index pour les tables déchargées
    --

    --
    -- Index pour la table `image2lieu`
    --
    ALTER TABLE `image2lieu`
        ADD PRIMARY KEY (`id_image`,`id_lieu`);

    ALTER TABLE `image2lieu`
        ADD CONSTRAINT `image2lieu_ibfk_1` FOREIGN KEY (`id_image`) REFERENCES `image` (`id`),
        ADD CONSTRAINT `image2lieu_ibfk_2` FOREIGN KEY (`id_lieu`) REFERENCES `lieu` (`id`);

    --
    -- Index pour la table `soiree2spectacle`
    --
    ALTER TABLE `soiree2spectacle`
        ADD PRIMARY KEY (`id_soiree`,`id_spectacle`);

    ALTER TABLE `soiree2spectacle`
        ADD CONSTRAINT `soiree2spectacle_ibfk_1` FOREIGN KEY (`id_soiree`) REFERENCES `soiree` (`id`),
        ADD CONSTRAINT `soiree2spectacle_ibfk_2` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`);

    --
    -- Index pour la table `spectacle2artiste`
    --
    ALTER TABLE `spectacle2artiste`
        ADD PRIMARY KEY (`id_spectacle`,`id_artiste`);

    ALTER TABLE `spectacle2artiste`
        ADD CONSTRAINT `spectacle2artiste_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`),
        ADD CONSTRAINT `spectacle2artiste_ibfk_2` FOREIGN KEY (`id_artiste`) REFERENCES `artiste` (`id`);

    --
    -- Index pour la table `spectacle2image`
    --
    ALTER TABLE `spectacle2image`
        ADD PRIMARY KEY (`id_spectacle`,`id_image`);

    ALTER TABLE `spectacle2image`
        ADD CONSTRAINT `spectacle2image_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id`),
        ADD CONSTRAINT `spectacle2image_ibfk_2` FOREIGN KEY (`id_image`) REFERENCES `image` (`id`);

    INSERT INTO `soiree` (id, nom, thematique, date, horaire_debut, horaire_fin, id_lieu) VALUES
        (1,'Best of rock','Rock','12-11-2024','19','23',1);
    INSERT INTO `soiree` (id, nom, thematique, date, horaire_debut, horaire_fin, id_lieu) VALUES
        (2,'Best of country music','Country','13-11-2024','20','23',2);
    INSERT INTO `soiree` (id, nom, thematique, date, horaire_debut, horaire_fin, id_lieu) VALUES
        (3,'Best of Reggae','Reggae','14-11-2024','18','22', 3);



    INSERT INTO spectacle (id, titre, description, chemin_fichier, horaire,duree, style) VALUES
                                                                                  (1, 'Jazz Band Live', 'A famous jazz band live in concert.', 'https://example.com/jazzlive', '20:00',2, 'Jazz'),
                                                                                  (2, 'Acoustic Jazz Trio', 'Smooth acoustic jazz trio.', 'https://example.com/acousticjazz', '21:00',3, 'Jazz'),
                                                                                  (3, 'Jazz Solo Piano', 'Relaxing solo piano jazz.', 'https://example.com/solopiano', '22:30',4, 'Jazz'),
                                                                                  (4, 'Rock Legends', 'Classic rock covers.', 'https://example.com/rocklegends', '20:30',1, 'Rock'),
                                                                                  (5, 'Indie Rock Band', 'Local indie rock band performance.', 'https://example.com/indierock', '22:00',2, 'Rock'),
                                                                                  (6, 'Blues Guitar', 'Blues guitar solo performance.', 'https://example.com/bluesguitar', '19:00',3, 'Blues'),
                                                                                  (7, 'Soulful Blues', 'Deep, soulful blues.', 'https://example.com/soulblues', '20:30',1, 'Blues'),
                                                                                  (8, 'Classical Quartet', 'String quartet performance.', 'https://example.com/classicalquartet', '19:45',2, 'Classical'),
                                                                                  (9, 'Electro DJ Set', 'Dance to electronic beats.', 'https://example.com/electro', '21:30',3, 'Electronic'),
                                                                                  (10, 'Techno Night', 'Non-stop techno music.', 'https://example.com/techno', '23:00',2, 'Electronic'),
                                                                                  (11, 'House Party', 'House music by top DJs.', 'https://example.com/house', '01:00',4, 'Electronic');

    INSERT INTO soiree2spectacle (id_soiree, id_spectacle) VALUES
                                                               ('1','1'),
                                                               ('2','2'),
                                                               ('3','3');


    INSERT INTO artiste (id, nom) VALUES
                                      (1, 'John Coltrane'), (2, 'Miles Davis'), (3, 'Bill Evans'),
                                      (4, 'Jimmy Page'), (5, 'Robert Plant'),
                                      (6, 'B.B. King'), (7, 'Muddy Waters'),
                                      (8, 'Yo-Yo Ma'), (9, 'Daft Punk'),
                                      (10, 'Carl Cox'), (11, 'David Guetta');



    INSERT INTO spectacle2artiste (id_spectacle, id_artiste) VALUES
                                                                 (1, 1), (1, 2),
                                                                 (2, 3), (3, 1),
                                                                 (4, 4), (5, 5),
                                                                 (6, 6), (7, 7),
                                                                 (8, 8),
                                                                 (9, 9), (10, 10), (11, 11);

    INSERT INTO lieu (id, nom, adresse, nbPlaceAssises, nbPlacesDebout) VALUES
                                                                            (1, 'Le Jazz Club', '123 Jazz St', 100, 50),
                                                                            (2, 'Rock Arena', '456 Rock Ave', 200, 120),
                                                                            (3, 'Blues House', '789 Blues Rd', 80, 40),
                                                                            (4, 'Grand Théâtre', '101 Classical Blvd', 200, 0),
                                                                            (5, 'Electro Warehouse', '202 Techno Lane', 0, 10);


    INSERT INTO image (id, chemin_fichier) VALUES
                                    (1, 'https://example.com/images/jazz1.jpg'),
                                    (2, 'https://example.com/images/jazz2.jpg'),
                                    (3, 'https://example.com/images/jazz3.jpg'),
                                    (4, 'https://example.com/images/rock1.jpg'),
                                    (5, 'https://example.com/images/rock2.jpg'),
                                    (6, 'https://example.com/images/blues1.jpg'),
                                    (7, 'https://example.com/images/blues2.jpg'),
                                    (8, 'https://example.com/images/classical1.jpg'),
                                    (9, 'https://example.com/images/electro1.jpg'),
                                    (10, 'https://example.com/images/electro2.jpg'),
                                    (11, 'https://example.com/images/house1.jpg');



    INSERT INTO spectacle2image VALUES
                                    (1, 1), (2, 2), (3, 3),
                                    (4, 4), (5, 5),
                                    (6, 6), (7, 7),
                                    (8, 8),
                                    (9, 9), (10, 10), (11, 11);



    INSERT INTO image2lieu (id_image, id_lieu) VALUES
                                                   (1, 1), (2, 1), (3, 1),
                                                   (4, 2), (5, 2),
                                                   (6, 3), (7, 3),
                                                   (8, 4),
                                                   (9, 5), (10, 5), (11, 5);


    INSERT INTO user (id, email, hash, role) VALUES
                                                 (1, 'user1@mail.com', '$2y$12$FSUq4wa9exgiHC/58tbpo.IGJ/1BxLLR54txC/GZhhbF.SOJqa8Qq', 1),
                                                 (2, 'user2@mail.com', '$2y$12$SZDTxmdvrvEBR4ynZ/OqveQkiJK3ySVcZhvZBiOpNfjFpseBdf4VS', 1),
                                                 (3, 'user3@mail.com', '$2y$12$WFClWVtZkGOvS3zIOKEDYOqImLUpEpiGyrmRShIvTsaJRoPTsqu0q', 1);

    COMMIT;

    /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
    /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
    /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

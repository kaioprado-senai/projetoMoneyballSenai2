CREATE DATABASE moneyball_senai1;
USE moneyball_senai1;

CREATE TABLE Usuario (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Senha VARCHAR(255),
    Nome VARCHAR(100),
    DataCadastro DATETIME,
    UltimoAcesso DATETIME NOT NULL,
    Status BOOLEAN,
    Perfil VARCHAR(50),
    Email VARCHAR(100) UNIQUE
);

CREATE TABLE Equipe (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100),
    Cidade VARCHAR(100),
    Tecnico VARCHAR(100)
);

CREATE TABLE Jogador (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Numero INT,
    Nome VARCHAR(100),
    Posicao VARCHAR(50),
    Altura FLOAT,
    Peso FLOAT,
    DataNascimento DATE,
    idEquipe INT
);

CREATE TABLE Campeonato (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Temporada VARCHAR(50),
    Nome VARCHAR(100),
    Ano INT
);

CREATE TABLE Partida (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    PlacarVisitante INT,
    PlacarCasa INT,
    Data DATE,
    Hora TIME,
    Local VARCHAR(100),
    idCampeonato INT,
    idEquipeCasa INT,
    idEquipeVisitante INT
);

CREATE TABLE Estatisticas (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    PlusMinus INT,
    Pontos INT,
    Assistencias INT,
    Rebotes INT,
    Roubos INT,
    Tocos INT,
    Turnovers INT,
    Faltas INT,
    Eficiencia INT,
    idJogador INT,
    idPartida INT
);

ALTER TABLE Jogador
ADD CONSTRAINT fk_jogador_equipe
FOREIGN KEY (idEquipe)
REFERENCES Equipe(ID);

ALTER TABLE Partida
ADD CONSTRAINT fk_partida_campeonato
FOREIGN KEY (idCampeonato)
REFERENCES Campeonato(ID);

ALTER TABLE Partida
ADD CONSTRAINT fk_partida_equipe_casa
FOREIGN KEY (idEquipeCasa)
REFERENCES Equipe(ID);

ALTER TABLE Partida
ADD CONSTRAINT fk_partida_equipe_visitante
FOREIGN KEY (idEquipeVisitante)
REFERENCES Equipe(ID);

ALTER TABLE Estatisticas
ADD CONSTRAINT fk_estatisticas_jogador
FOREIGN KEY (idJogador)
REFERENCES Jogador(ID);

ALTER TABLE Estatisticas
ADD CONSTRAINT fk_estatisticas_partida
FOREIGN KEY (idPartida)
REFERENCES Partida(ID);

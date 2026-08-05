CREATE DATABASE moneyball_senai1;
USE moneyball_senai1;

-- ==========================
-- USUÁRIOS
-- ==========================

CREATE TABLE Usuario (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Senha VARCHAR(255) NOT NULL,
    Perfil ENUM('Administrador','Comissao','Usuario') NOT NULL,
    Status BOOLEAN DEFAULT TRUE,
    DataCadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    UltimoAcesso DATETIME
);

-- ==========================
-- EQUIPES
-- ==========================

CREATE TABLE Equipe (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Cidade VARCHAR(100),
    Tecnico VARCHAR(100),
    Escudo VARCHAR(255)
);

-- ==========================
-- CAMPEONATOS
-- ==========================

CREATE TABLE Campeonato (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Temporada VARCHAR(50),
    Ano INT
);

-- ==========================
-- JOGADORES
-- ==========================

CREATE TABLE Jogador (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Numero INT,
    Posicao VARCHAR(50),
    Altura DECIMAL(4,2),
    Peso DECIMAL(5,2),
    DataNascimento DATE,
    idEquipe INT,

    CONSTRAINT fk_jogador_equipe
    FOREIGN KEY (idEquipe)
    REFERENCES Equipe(ID)
);

-- ==========================
-- PARTIDAS
-- ==========================

CREATE TABLE Partida (
    ID INT AUTO_INCREMENT PRIMARY KEY,

    DataHora DATETIME,

    Local VARCHAR(100),

    Status ENUM(
        'Agendada',
        'Em andamento',
        'Finalizada'
    ) DEFAULT 'Agendada',

    PlacarCasa INT DEFAULT 0,
    PlacarVisitante INT DEFAULT 0,

    idCampeonato INT,
    idEquipeCasa INT,
    idEquipeVisitante INT,

    CONSTRAINT fk_partida_campeonato
        FOREIGN KEY(idCampeonato)
        REFERENCES Campeonato(ID),

    CONSTRAINT fk_partida_equipe_casa
        FOREIGN KEY(idEquipeCasa)
        REFERENCES Equipe(ID),

    CONSTRAINT fk_partida_equipe_visitante
        FOREIGN KEY(idEquipeVisitante)
        REFERENCES Equipe(ID)
);

-- ==========================
-- EVENTOS DA PARTIDA
-- ==========================

CREATE TABLE Evento (

    ID INT AUTO_INCREMENT PRIMARY KEY,

    idPartida INT NOT NULL,

    idJogador INT NOT NULL,

    Periodo INT,

    Minuto INT,

    Segundo INT,

    TipoEvento VARCHAR(50),

    Pontos INT DEFAULT 0,

    CoordenadaX DECIMAL(6,2),

    CoordenadaY DECIMAL(6,2),

    DataRegistro DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evento_partida
        FOREIGN KEY(idPartida)
        REFERENCES Partida(ID),

    CONSTRAINT fk_evento_jogador
        FOREIGN KEY(idJogador)
        REFERENCES Jogador(ID)

);

-- ==========================
-- ESTATÍSTICAS
-- ==========================

CREATE TABLE Estatisticas (

    ID INT AUTO_INCREMENT PRIMARY KEY,

    idJogador INT,

    idPartida INT,

    Pontos INT DEFAULT 0,

    Assistencias INT DEFAULT 0,

    Rebotes INT DEFAULT 0,

    Roubos INT DEFAULT 0,

    Tocos INT DEFAULT 0,

    Turnovers INT DEFAULT 0,

    Faltas INT DEFAULT 0,

    PlusMinus INT DEFAULT 0,

    Eficiencia DECIMAL(8,2),

    eFG DECIMAL(6,2),

    TS DECIMAL(6,2),

    PPP DECIMAL(6,2),

    OffensiveRating DECIMAL(6,2),

    DefensiveRating DECIMAL(6,2),

    Regularidade DECIMAL(6,2),

    CONSTRAINT fk_estatisticas_jogador
        FOREIGN KEY(idJogador)
        REFERENCES Jogador(ID),

    CONSTRAINT fk_estatisticas_partida
        FOREIGN KEY(idPartida)
        REFERENCES Partida(ID)

);

-- ==========================
-- LINEUP (5 jogadores em quadra)
-- ==========================

CREATE TABLE Lineup (

    ID INT AUTO_INCREMENT PRIMARY KEY,

    idPartida INT,

    idJogador INT,

    Periodo INT,

    Entrada TIME,

    Saida TIME,

    CONSTRAINT fk_lineup_partida
        FOREIGN KEY(idPartida)
        REFERENCES Partida(ID),

    CONSTRAINT fk_lineup_jogador
        FOREIGN KEY(idJogador)
        REFERENCES Jogador(ID)

);

-- ==========================
-- ÍNDICES
-- ==========================

CREATE INDEX idx_jogador_nome
ON Jogador(Nome);

CREATE INDEX idx_jogador_numero
ON Jogador(Numero);

CREATE INDEX idx_usuario_email
ON Usuario(Email);

CREATE INDEX idx_evento_partida
ON Evento(idPartida);

CREATE INDEX idx_evento_jogador
ON Evento(idJogador);
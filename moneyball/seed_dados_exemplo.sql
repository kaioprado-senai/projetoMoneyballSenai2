-- =========================================================
-- DADOS DE EXEMPLO (OPCIONAL) - Moneyball SENAI Basquete
-- Execute depois do schema.sql para já ter algo para testar.
-- =========================================================

USE moneyball_senai1;

INSERT INTO Campeonato (Nome, Temporada, Ano) VALUES
('Campeonato SENAI', '2026/1', 2026);

INSERT INTO Equipe (Nome, Cidade, Tecnico) VALUES
('Curitiba Hawks', 'Curitiba', 'Marcos Silva'),
('Londrina Storm', 'Londrina', 'Ana Ferreira');

INSERT INTO Jogador (Nome, Numero, Posicao, Altura, Peso, DataNascimento, idEquipe) VALUES
('João Pedro', 4, 'Armador', 1.85, 78.5, '2005-03-12', 1),
('Lucas Martins', 7, 'Ala', 1.98, 92.0, '2004-07-22', 1),
('Rafael Souza', 11, 'Pivô', 2.05, 105.0, '2003-11-05', 1),
('Bruno Costa', 5, 'Ala-Armador', 1.90, 84.0, '2005-01-30', 2),
('Diego Alves', 9, 'Ala-Pivô', 2.00, 98.0, '2004-05-17', 2),
('Felipe Rocha', 12, 'Pivô', 2.08, 110.0, '2003-09-09', 2);

INSERT INTO Partida (DataHora, Local, Status, idCampeonato, idEquipeCasa, idEquipeVisitante) VALUES
(NOW(), 'Ginásio SENAI - Curitiba', 'Agendada', 1, 1, 2);

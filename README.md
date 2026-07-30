O documento unificado e formatado do projeto #projetoMoneyballSenai2 organiza os Requisitos Funcionais (renumerados sem duplicidade) e Não Funcionais em um padrão profissional de engenharia de software.
------------------------------
## 🏀 Documento de Especificação de Requisitos — #projetoMoneyballSenai2## 📋 Requisitos Funcionais (RF)## 1. Coleta e Entrada de Dados (Box Score Tradicional)

* RF01 - Registro de Eventos em Tempo Real: O sistema deve permitir que o usuário registre eventos de jogo (pontos, rebotes, assistências, roubos, tocos, turnovers e faltas) vinculados a um jogador e a um cronômetro.
* RF02 - Geolocalização de Arremessos: O sistema deve permitir marcar o local exato do arremesso em uma quadra virtual para alimentar o Shot Chart (Mapa de Arremessos).

## 2. Processamento, Inteligência e Métricas Avançadas

* RF03 - Cálculo Automático de Eficiência: O sistema deve calcular automaticamente o eFG% (Aproveitamento Efetivo) e o TS% (True Shooting) de cada jogador ao final de cada período ou partida.
* RF04 - Linha de Tempo de Posses: O sistema deve agrupar os eventos em "posses de bola" para calcular o Points Per Possession (PPP) e os Ratings Ofensivo e Defensivo.
* RF05 - Mapeamento de Lineups: O sistema deve rastrear os 5 jogadores em quadra para calcular o Plus/Minus (+/-) e o impacto coletivo em tempo real.
* RF06 - Identificação do Melhor Desempenho Geral: O sistema deve calcular o desempenho geral de todos os jogadores com base nos critérios definidos pela equipe e exibir o jogador com a maior pontuação.
* RF07 - Identificação do Pior Desempenho Geral: O sistema deve analisar as estatísticas cadastradas de todos os jogadores e identificar aquele que apresenta o pior desempenho geral.
* RF08 - Identificação do Jogador Mais Regular: O sistema deve calcular a regularidade dos jogadores por meio de uma fórmula de consistência definida pela equipe e apresentar o atleta mais estável ao longo das partidas.

## 3. Saída de Dados, Rankings e Relatórios

* RF09 - Geração de Ranking (Top 5): O sistema deve calcular um ranking de desempenho utilizando a metodologia da equipe, ordenar os jogadores de forma decrescente e apresentar os cinco primeiros colocados, informando explicitamente o critério de cálculo utilizado.
* RF10 - Geração de Relatório de Scouting Pré-Jogo: O sistema deve gerar um relatório consolidando as tendências do adversário (ex: % de infiltrações para a direita vs. esquerda).
* RF11 - Filtros Dinâmicos: O usuário deve conseguir filtrar as estatísticas por partida, campeonato, jogador, posição ou período do jogo.

------------------------------
## ⚙️ Requisitos Não Funcionais (RNF)## 1. Desempenho e Tempo de Resposta

* RNF01 - Latência de Entrada: O registro de um evento em quadra (ex: marcar uma cesta) não deve levar mais de 200 milissegundos para ser processado e exibido na tela.
* RNF02 - Atualização de Métricas: Os cálculos analíticos e avançados (USG%, PPP) devem ser atualizados em tempo real em menos de 2 segundos de atraso para dar suporte imediato à comissão técnica no banco de reservas.

## 2. Usabilidade e Portabilidade

* RNF03 - Interface Otimizada para Mobile/Tablet: O módulo de coleta de dados em tempo real deve ser adaptado para telas sensíveis ao toque, permitindo o registro de qualquer evento com no máximo dois cliques na tela.
* RNF04 - Operação Offline Primária: O sistema deve permitir a coleta completa de dados e registro de scouts mesmo sem conexão com a internet, realizando a sincronização com a nuvem automaticamente assim que o sinal for restabelecido.

## 3. Confiabilidade e Segurança

* RNF05 - Integridade de Partida e Backup: O sistema deve fazer backup automático e local dos dados da partida a cada 60 segundos para evitar perda de informações por queda de energia ou fechamento inesperado do aplicativo.
* RNF06 - Controle de Acesso Restrito: Os relatórios de scouting estratégico e rankings de desempenho devem ser criptografados e acessíveis apenas por usuários autenticados com nível de permissão de "Comissão Técnica".

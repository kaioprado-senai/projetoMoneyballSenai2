📋 Requisitos Funcionais (RF)
1. Coleta e Entrada de Dados

RF01 – Registro de Eventos em Tempo Real
O sistema deve permitir que o usuário registre eventos de jogo (pontos, rebotes, assistências, roubos de bola, tocos, turnovers e faltas), vinculando cada evento a um jogador e ao cronômetro da partida.

RF02 – Geolocalização de Arremessos
O sistema deve permitir marcar o local exato de cada arremesso em uma quadra virtual para alimentar o Shot Chart (Mapa de Arremessos).

2. Processamento, Inteligência e Métricas Avançadas

RF03 – Cálculo Automático de Eficiência
O sistema deve calcular automaticamente métricas avançadas como eFG% (Effective Field Goal Percentage) e TS% (True Shooting Percentage) ao final de cada período ou partida.

RF04 – Linha de Tempo de Posses
O sistema deve agrupar os eventos em posses de bola para calcular métricas como Points Per Possession (PPP), Offensive Rating e Defensive Rating.

RF05 – Mapeamento de Lineups
O sistema deve rastrear os cinco jogadores em quadra durante toda a partida para calcular o Plus/Minus (+/-) e o impacto coletivo em tempo real.

RF06 – Identificação do Melhor Desempenho Geral
O sistema deve calcular o desempenho geral de todos os jogadores com base nos critérios definidos pela equipe e apresentar aquele com maior pontuação.

RF07 – Identificação do Pior Desempenho Geral
O sistema deve analisar as estatísticas cadastradas e identificar o jogador com o pior desempenho geral.

RF08 – Identificação do Jogador Mais Regular
O sistema deve calcular a regularidade dos jogadores por meio de uma fórmula de consistência definida pela equipe e apresentar o atleta mais regular.

3. Rankings, Comparações e Relatórios

RF09 – Geração do Ranking (Top 5)
O sistema deve gerar um ranking de desempenho dos jogadores, ordenando-os de forma decrescente e exibindo os cinco melhores, juntamente com a metodologia utilizada no cálculo.

RF10 – Geração de Relatório de Scouting Pré-Jogo
O sistema deve gerar relatórios contendo tendências estatísticas da equipe adversária para auxiliar no planejamento tático.

RF11 – Filtros Dinâmicos
O sistema deve permitir filtrar estatísticas por partida, campeonato, temporada, jogador, posição e período do jogo.

RF12 – Comparação de Desempenho entre Períodos
O sistema deve comparar o desempenho de um jogador entre duas partidas, períodos ou temporadas e identificar sua evolução ou regressão.

RF13 – Ranking de Equipes
O sistema deve calcular a média de desempenho de cada equipe e apresentar um ranking baseado nas estatísticas consolidadas dos jogadores.

RF14 – Identificação da Estatística Mais Influente
O sistema deve identificar qual estatística possui maior peso na classificação dos jogadores, conforme os critérios definidos pela equipe.

RF15 – Justificativa da Estatística Mais Importante
O sistema deve apresentar, juntamente com a estatística considerada mais relevante para o esporte escolhido, uma justificativa baseada nos critérios de avaliação utilizados.

⚙️ Requisitos Não Funcionais (RNF)
1. Desempenho

RNF01 – Latência de Entrada
O registro de um evento em quadra deve ser processado e exibido em até 200 milissegundos.

RNF02 – Atualização de Métricas
As métricas analíticas e avançadas devem ser recalculadas em tempo real com atraso máximo de 2 segundos.

2. Usabilidade

RNF03 – Interface para Tablets e Dispositivos Touch
O módulo de coleta deve ser otimizado para telas sensíveis ao toque, permitindo registrar qualquer evento com no máximo dois toques.

RNF04 – Operação Offline
O sistema deve permitir o registro completo das partidas sem conexão com a internet, sincronizando automaticamente os dados quando a conexão for restabelecida.

RNF05 – Clareza na Apresentação dos Resultados
Os rankings, métricas e relatórios devem ser apresentados de forma clara, organizada e de fácil interpretação para os usuários.

3. Confiabilidade e Segurança

RNF06 – Backup Automático
O sistema deve realizar backup automático local dos dados da partida a cada 60 segundos.

RNF07 – Controle de Acesso
Os relatórios estratégicos e rankings de desempenho devem estar disponíveis apenas para usuários autenticados com permissão adequada.

RNF08 – Integridade dos Dados
O sistema deve validar os dados inseridos, impedindo registros inconsistentes, duplicados ou incompatíveis com as regras do basquete.

4. Compatibilidade e Manutenção

RNF09 – Compatibilidade entre Plataformas
O sistema deve ser compatível com os principais navegadores modernos e dispositivos móveis utilizados pela comissão técnica.

RNF10 – Escalabilidade
O sistema deve suportar o armazenamento e processamento de múltiplas temporadas, campeonatos e milhares de partidas sem degradação significativa do desempenho.

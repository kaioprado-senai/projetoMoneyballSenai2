# Moneyball SENAI — App de Scouting de Basquete

Esqueleto funcional em **PHP + PDO + MySQL**, construído sobre o `schema.sql` e o protótipo de
login já existentes no projeto, seguindo os Requisitos Funcionais (RF01–RF32) e Não Funcionais
(RNF01–RNF17) do documento de especificação.

## 1. Como rodar localmente (XAMPP / Laragon / WAMP)

1. Copie a pasta `moneyball/` para o diretório do seu servidor (`htdocs`, `www`, etc.).
2. Crie o banco de dados executando, nesta ordem, no phpMyAdmin:
   1. `schema.sql` -> cria o banco `moneyball_senai1` e todas as tabelas.
   2. `seed_dados_exemplo.sql` (opcional) -> cria 2 equipes, 6 jogadores, 1 campeonato e 1 partida
      já prontos para testar o scouting sem digitar tudo na mão.
3. Ajuste as credenciais do banco em `config.php` se necessário (padrão: `root` sem senha).
4. Acesse `http://localhost/moneyball/criar_admin.php` e crie o primeiro usuário
   **Administrador** (só um Administrador pode cadastrar novos usuários, conforme a regra do
   projeto). **Apague esse arquivo depois de usá-lo.**
5. Acesse `http://localhost/moneyball/login.php` e entre com o admin criado.

## 2. Fluxo de uso (o "aplicativo de scouting" em si)

1. **Equipes** -> cadastre manualmente (`equipes/cadastrar.php`) ou importe em lote via CSV
   (`equipes/importar.php`, com verificação de duplicidade e planilha modelo para download).
2. **Jogadores** -> cadastre manualmente (`jogadores/cadastrar.php`) ou importe em lote via CSV
   (`jogadores/importar.php`, modelo em `jogadores/baixar_modelo.php`). A equipe informada na
   planilha precisa já estar cadastrada; se não for encontrada, o jogador é importado sem
   equipe vinculada.
3. **Partidas** -> cadastre uma partida entre duas equipes (`partidas/cadastrar.php`).
4. **Scouting ao vivo** -> na listagem de partidas, clique em **"Scouting"**. Essa é a tela
   principal de coleta em tempo real: você seleciona o jogador (1 toque) e o tipo de evento
   (1 toque) — cestas, erros, rebotes, assistências, roubos, tocos, turnovers e faltas — e cada
   clique é enviado via AJAX para `api/registrar_evento.php`, que grava o evento e recalcula
   automaticamente as estatísticas do jogador naquela partida (eficiência, eFG%, TS%,
   regularidade). Para arremessos, clique na posição da quadra antes de registrar a cesta/erro,
   para alimentar o Shot Chart.
5. **Dashboard**, **Rankings** e **Comparar Jogadores** consultam essas estatísticas já calculadas.

## 3. Estrutura de pastas

```
moneyball/
  - config.php              -> conexão PDO + sessão
  - schema.sql               -> estrutura do banco (fornecida originalmente)
  - seed_dados_exemplo.sql   -> dados de teste opcionais
  - criar_admin.php          -> cria o 1º usuário Administrador (apagar após uso)
  - index.php / login.php / logout.php
  - dashboard.php            -> KPIs (RF23)
  - includes/
      - auth.php              -> login/sessão/controle de permissões (RF01-RF03, RNF12)
      - functions.php         -> cálculo de eficiência, eFG%, TS%, PPP, regularidade, rankings
      - header.php / footer.php -> layout com sidebar e tema claro/escuro (RF31)
  - usuarios/                -> CRUD de usuários, só Administrador (RF02)
  - equipes/                 -> CRUD de equipes, importação em lote via CSV (equipes/importar.php)
  - jogadores/                -> CRUD, busca/filtro (RF06-RF07), importação CSV (RF05, RNF17),
                                 visualização com histórico + Shot Chart (RF06, RF11)
  - partidas/                 -> CRUD de partidas (RF08)
  - scouting/registrar.php    -> tela de coleta em tempo real (RF09, RF11, RNF01, RNF05)
  - api/registrar_evento.php  -> endpoint AJAX que grava o evento e recalcula stats (RF10)
  - estatisticas/
      - ranking.php            -> Top 5, ranking de equipes, melhor/pior, regularidade,
                                 estatística mais influente (RF15-RF19, RF24-RF26)
      - comparar.php            -> comparação entre 2 jogadores (RF27)
```

## 4. O que já está pronto vs. o que fica como próximo passo da equipe

**Pronto e funcional:**
- Login com hash de senha (`password_hash`/`password_verify`), controle de perfis.
- CRUD completo de usuários, equipes, jogadores e partidas.
- Registro de eventos em tempo real com recálculo automático de estatísticas.
- Cálculo de eFG%, TS%, Eficiência, PPP, Regularidade e "estatística mais influente".
- Ranking geral, Top 5, ranking de equipes, comparação entre jogadores.
- Shot Chart simples (SVG) a partir das coordenadas registradas no scouting.
- Importação de jogadores via CSV (compatível com Excel).
- Tema claro/escuro (RF31) e layout responsivo básico (RNF04) com Tailwind.

**Deixado como extensão proposital (para a equipe implementar e aprender fazendo, conforme
pede o projeto — "não gerar o projeto inteiro"):**
- Importação nativa de `.xlsx`/`.xls` (hoje o import lê `.csv`; para `.xlsx` real, adicionar a
  biblioteca **PhpSpreadsheet** via Composer, como comentado em `jogadores/importar.php`).
- Exportação de relatórios em PDF/Excel (RF30) — sugestão: `dompdf` para PDF e `PhpSpreadsheet`
  para Excel.
- Lineups e Plus/Minus completos (RF14) — a tabela `Lineup` já existe no schema, falta a tela.
- Posses de bola detalhadas / Offensive-Defensive Rating por posse (RF13) — hoje o cálculo de
  PPP é estimado por jogador; para o valor exato por equipe, agrupar os eventos por posse.
- Tabela `Importacao` para log completo de cada upload (hoje `jogadores/historico_importacoes.php`
  é um placeholder simples).
- Modo offline com sincronização (RNF07) — hoje o scouting mostra um aviso de falha de rede;
  para funcionar de fato offline, seria necessário Service Worker + fila local (IndexedDB).

## 5. Segurança implementada

- Todas as senhas gravadas com `password_hash()` / verificadas com `password_verify()`.
- Todas as queries usam **prepared statements** (PDO), sem concatenação de SQL.
- Controle de acesso por perfil (Administrador / Comissão Técnica / Usuário) em cada página.
- Validação de vínculo jogador<->equipe<->partida antes de gravar um evento de scouting.

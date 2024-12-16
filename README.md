# cursoFullCycle-Backend-Admin-Catalogo-Videos-PHP
Curso FullCycle - Backend da Administração do Catálogo de Vídeos em PHP

- Para iniciar os microsserviços deve-se executar o script startCompose.sh;

- Para atualizar as dependências deve-se executar o script runComposerUpdate.sh;

- Para realizar as migrations do BD deve-se executar o script runMigrate.sh;

- Para executar os testes automatizados deve-se executar o script runTests.sh;

- A tela inicial do keycloak está disponível na url: http://0.0.0.0:8081/;

    - Abra o 'Administration Console'. As credenciais de administrador são username:admin e password:admin;
    - Para criar um novo realm(fullcycle) selecione a opção: 'Create Realm';
    - Para exibir os endpoints de funcionalidades do keycloak selecione a opção: 'Realm settings'->'Aba General'->'Endpoints'->'OpenID Endpoint Configuration';
    - Para obter a chave pública que será utilizada no .env(KEYCLOAK_REALM_PUBLIC_KEY) da aplicação de backend selecione a opção: 'Realm settings'->'Aba Keys'->'Algorithm RS256'->'Public key';
    - Para criar um novo client(backend-admin-catalogo-videos) selecione a opção: 'Clients'->'Create client'. Escolher o type 'OpenID Connect' e authentication 'On';
    - Para criar uma nova role(admin-catalogo) selecione a opção: 'Realm roles'->'Create role';
    - Para criar um novo user(silvio) selecione a opção: 'Users'->'Create new user'. Associe este à role  'admin-catalogo' na aba 'Role mapping'. Crie uma senha (123456) na aba 'Credentials';

- Para obter o token de autorização deve-se realizar uma requisição POST para o token_endpoint do keycloak com os seguintes parâmetros:
    - headers: Accept -> application/json;
    - body: grant_type -> password, client_id -> backend-admin-catalogo-videos, client_secret -> (dado disponível no keycloack->client credentials->client secret), username -> silvio, password -> 123456;

- Para visualizar o conteúdo do token pode-se utilizar o site jwt.io;
    
- Para encerrar os microsserviços deve-se executar o script stopCompose.sh;

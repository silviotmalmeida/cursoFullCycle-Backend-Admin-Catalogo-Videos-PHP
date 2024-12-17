# cursoFullCycle-Backend-Admin-Catalogo-Videos-PHP
Curso FullCycle - Backend da Administração do Catálogo de Vídeos em PHP

- Para iniciar os microsserviços deve-se executar o script startCompose.sh;

- Para atualizar as dependências deve-se executar o script runComposerUpdate.sh;

- Para realizar as migrations do BD deve-se executar o script runMigrate.sh;

- Para executar os testes automatizados deve-se executar o script runTests.sh;

- A tela inicial do keycloak está disponível na url: http://localhost:8081/;

    - Abra o 'Administration Console'. As credenciais de administrador são username:admin e password:admin;
    - Para criar um novo realm(fullcycle) selecione a opção: 'Create Realm';
    - Para exibir os endpoints de funcionalidades do keycloak selecione a opção: 'Realm settings'->'Aba General'->'Endpoints'->'OpenID Endpoint Configuration';
    - Para obter a chave pública que será utilizada no .env(KEYCLOAK_REALM_PUBLIC_KEY) da aplicação de backend selecione a opção: 'Realm settings'->'Aba Keys'->'Algorithm RS256'->'Public key';
    - Para criar um novo client(backend-admin-catalogo-videos) selecione a opção: 'Clients'->'Create client'. Escolher o type 'OpenID Connect' e authentication 'On';
    - Para criar uma nova role(admin-catalogo) selecione a opção: 'Realm roles'->'Create role';
    - Para criar um novo user(silvio) selecione a opção: 'Users'->'Create new user'. Associe este à role  'admin-catalogo' na aba 'Role mapping'. Crie uma senha (123456) na aba 'Credentials';

- Para obter o token de autorização deve-se realizar uma requisição POST para o token_endpoint do keycloak com os seguintes parâmetros:
    - headers: Accept -> application/json;
    - body(x-www-form-urlencoded): grant_type -> password, client_id -> backend-admin-catalogo-videos, client_secret -> (dado disponível no keycloack->client credentials->client secret), username -> silvio, password -> 123456;

- Para visualizar o conteúdo do token pode-se utilizar o site jwt.io;

- Os seguintes endpoints estão disponíveis na aplicação:
    - categories:
        - index: GET http://localhost:8000/api/categories com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - show: GET http://localhost:8000/api/categories/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - store: POST http://localhost:8000/api/categories com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","description": "new description","is_active": true};

        - update: PUT http://localhost:8000/api/categories/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","description": "new description","is_active": true};

        - destroy: DELETE http://localhost:8000/api/categories/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

    - cast_members:
        - index: GET http://localhost:8000/api/cast_members com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - show: GET http://localhost:8000/api/cast_members/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - store: POST http://localhost:8000/api/cast_members com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","type": 2};

        - update: PUT http://localhost:8000/api/cast_members/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","type": 2};

        - destroy: DELETE http://localhost:8000/api/cast_members/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

    - genres:
        - index: GET http://localhost:8000/api/genres com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - show: GET http://localhost:8000/api/genres/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - store: POST http://localhost:8000/api/genres com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","is_active": false,"categories_id": ["id","id"]};

        - update: PUT http://localhost:8000/api/genres/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(raw):{"name": "new name","is_active": false,"categories_id": ["id","id"]};

        - destroy: DELETE http://localhost:8000/api/genres/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

    - videos:
        - index: GET http://localhost:8000/api/videos com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - show: GET http://localhost:8000/api/videos/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];

        - store: POST http://localhost:8000/api/videos com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(form-data): title -> new title, description -> new description, year_launched -> 2000, duration -> 100, rating -> 10, opened -> 0, categories_id[] -> id, categories_id[] -> id, genres_id[] -> id, genres_id[] -> id, cast_members_id[] -> id, cast_members_id[] -> id, thumbfile -> file, thumbhalf -> file, bannerfile -> file, trailerfile -> file, videofile -> file;

        - update: PUT http://localhost:8000/api/videos/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
            - body(form-data): title -> new title, description -> new description, year_launched -> 2000, duration -> 100, rating -> 10, opened -> 0, categories_id[] -> id, categories_id[] -> id, genres_id[] -> id, genres_id[] -> id, cast_members_id[] -> id, cast_members_id[] -> id, thumbfile -> file, thumbhalf -> file, bannerfile -> file, trailerfile -> file, videofile -> file;

        - destroy: DELETE http://localhost:8000/api/videos/[id] com parâmetros:
            - headers: Accept -> application/json, Content-Type -> application/json, Authorization -> Bearer [token];
    
- Para encerrar os microsserviços deve-se executar o script stopCompose.sh;

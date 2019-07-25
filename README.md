# Logistic REST APIs

## Tech Stack

- [Docker](https://www.docker.com/), [NGINX](https://docs.nginx.com/nginx/admin-guide/content-cache/content-caching/) - core platform and dev tools.
- [Php](https://php.net/), [MySQL](https://mysql.com/) - common HTTP-server features
- [Laravel](https://laravel.com) - PHP framework
- [PHPUnit](https://phpunit.de/) - unit and integration testing

## How to Install & Run

1. Clone repository

        git clone https://php-nagarro@bitbucket.org/php-nagarro/logistic-api.git

2. Set Google Distance API key in environment file `./codebase/.env`

        MAP_KEY=

3. Execute following commands to build docker containers, executing migration and PHPunit test cases

        ./start.sh

4. All error/print messages are fetched from locale file message.php in lang directory, to add multilingual kindly add message file in its respective directory and set locale accordingly.

## API Documentation with Swagger

1. Swagger API docs can be accessed at URL http://localhost:8080/docs
2. Swagger Json can be assessed at codebase/public/swagger/swagger.json

## Code coverage report

1. Code coverage report can be accessed at URL http://localhost:8080/codecoverage/index.html
2. Xdebug has been used for code analysis

## Manually Migrating tables and Data Seeding

1. To run migrations manually use this command

        docker exec order_apis_php php artisan migrate

2. To run data import manually use this command

        docker exec order_apis_php php artisan db:seed


## Manually Starting the docker and test Cases

1. You can run following command from terminal

        docker-compose up

2. Server can be accessed at `http://localhost:8080`
3. Run manual testcase suite:

        docker exec order_apis_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Unit

        docker exec order_apis_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Feature



## API Reference Documentation

- `localhost:8080/orders?page=:page&limit=:limit` :

    GET Method - to fetch orders with page number and limit
    1. Header :
        - GET /orders?page=1&limit=5 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Responses :

    ```
            - Response
            [
              {
                "id": 1,
                "distance": 1234,
                "status": "TAKEN"
              },
              {
                "id": 2,
                "distance": 45321,
                "status": "UNASSIGNED"
              },
              {
                "id": 3,
                "distance": 56421,
                "status": "UNASSIGNED"
              },
              {
                "id": 4,
                "distance": 45321,
                "status": "UNASSIGNED"
              },
              {
                "id": 5,
                "distance": 22234,
                "status": "UNASSIGNED"
              }
            ]
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 500                   Internal Server Error

- `localhost:8080/orders` :

    POST Method - to create new order with origin and distination
    1. Header :
        - POST /orders HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Post-Data :
    ```
         {
            "origin" :["28.704060", "77.102493"],
            "destination" :["28.535517", "77.391029"]
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "id": 44,
              "distance": 46732,
              "status": "UNASSIGNED"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 400                   Api request denied or not responding
        - 422                   Invalid Request Parameter

- `localhost:8080/orders/:id` :

    PATCH method to update status for taken.(Handled simultaneous update request from multiple users at the same time with response status 409)
    1. Header :
        - PATCH /orders/44 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
    2. Post-Data :
    ```
         {
            "status" : "TAKEN"
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "status": "SUCCESS"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 409                   Order already taken
        - 417                   Invalid Order Id

## App Structure

**./tests**

- this folder contains Integraton and Unit Test cases, written under /tests/Feature and /tests/Unit respectively

**./app**

- contains all the server configuration file and controllers and models
- migration files are written under database folder in migrations directory
	- To run manually migrations use this command `docker exec order_apis_php php artisan migrate`
- Dummy data seeding is performed using faker under database seeds folder
	- To run manually data import use this command `docker exec order_apis_php php artisan db:seed`
- `OrderController` contains all the api's methods :
    1. localhost:8080/orders?page=1&limit=4 - GET url to fetch orders with page and limit
    2. localhost:8080/orders - POST method to create new order with origin and destination params
    3. localhost:8080/orders - PATCH method to update status for taken, also handled race condition, only 1 order can be TAKEN at one point

**.env**

- config contains all project configuration like it provides app configs, Google API Key, db connection

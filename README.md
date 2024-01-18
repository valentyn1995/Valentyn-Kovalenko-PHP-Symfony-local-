# Symfonygramm (Setup guide)

1. Select a directory
```sh
cd <path directory_for_repository>
```
2. Clone repo and select a branch
```sh
git clone -b main https://github.com/valentyn1995/Valentyn-Kovalenko-PHP-Symfony-local-.git
```
3. Build Docker image and run Docker container
```sh
docker-compose up -d --build
```
4. Run migrations
```sh
docker-compose exec app php bin/console doctrine:migrations:migrate
```
5. Add fixtures
```sh
docker-compose exec app php bin/console doctrine:fixtures:load --env=dev --group=AppFixtures
```
6. Run messenger
```sh
docker-compose exec app php bin/console messenger:consume   
```
7. Run mailcatcher
```sh
symfony open:local:webmail
```
8. Run application in browser
```sh
http://localhost:8000/registration
```
Create DB for tests
```sh
docker-compose exec app php bin/console doctrine:database:create --env=test
```
Do migration for tests
```sh
docker-compose exec app php bin/console doctrine:migrations:migrate --env=test
```
Add test users
```sh
docker-compose exec app php bin/console doctrine:fixtures:load --env=test --group=TestFixtures
```
```sh
docker-compose exec app php bin/phpunit
```


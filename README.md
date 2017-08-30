# Open Rechtspraak

A website with judges from the Netherlands. Build on CodeIgniter.

## Installation
[Docker Compose](https://docs.docker.com/compose/install/) is required.

Note: Change `production` in `define('ENVIRONMENT', 'production');` to `development` in `www/ors-ci-app/index.php` if you're developing.

- Clone this repo
- `cd open-rechtspraak/docker`
- `docker-compose up -d`

To run a daily update, add the following line to `sudo crontab -e`:
- `0 0 * * * sudo docker exec ors_app_1 sh daily.sh`

To restore a backup:
- `docker exec ors_app_1 cd ors-ci-app && php -d memory_limit=1280M index.php cli rechtspraak restore`

To import an old type of backup:
- `docker exec ors_app_1 cd ors-ci-app && php -d memory_limit=1280M index.php cli rechtspraak import_old`

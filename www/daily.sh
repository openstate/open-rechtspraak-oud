sudo docker exec ors_app_1 cd ors-ci-app && php index.php cli rechtspraak extract
sudo docker exec ors_app_1 cd ors-ci-app && php index.php cli rechtspraak transform
sudo docker exec ors_app_1 cd ors-ci-app && php index.php cli rechtspraak load
sudo docker exec ors_app_1 cd ors-ci-app && php index.php cli rechtspraak enrich
sudo docker exec ors_app_1 cd ors-ci-app && php index.php cli rechtspraak backup

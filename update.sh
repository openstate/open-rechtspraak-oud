 #!/bin/bash
 
 #remove old download because new one will overwrite
 sudo rm  rechtspraak.json 
 
 #get new stuff and write rechtspraak.json
 sudo php index.php cli rechtspraak extract
 
 #make a temp backup to old-data dir
 sudo mv rechtspraak-*.json.gz old-data/ 

#remove the current index as we a rebuilding 
 sudo rm rechtspraak-index.json 
 
 #remove detailpages as we are updating 
 sudo rm -Rf ./rechtspraak/*
 
 #rewrites index and files in sub directory rechtspraak/*
 sudo php index.php cli rechtspraak transform
 
 
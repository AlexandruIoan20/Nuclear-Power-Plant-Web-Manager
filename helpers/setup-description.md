Setup Proiect PHP cu Docker

Cum pornesti aplicatia

Deschide terminalul in folderul proiectului (unde se afla fisierul docker-compose.yml) si ruleaza comanda:
Bash

```
docker compose up -d
```

Daca iti da eroare de permisiuni (permission denied), adauga sudo in fata:
Bash

```
sudo docker compose up -d
```

Acum poti deschide browserul si accesa adresa: http://localhost:8081
Cum scrii cod si vezi modificarile

Fluxul de lucru este foarte simplu:

    Deschizi fisierele din folderul src in editorul tau de text (ex: index.php).

    Scrii codul si salvezi fisierul (Ctrl + S).

    Te duci in browser si apesi tasta F5 (Refresh).

Orice modificare salvata va aparea imediat dupa ce apesi F5.
Cum opresti aplicatia

Cand ai terminat treaba pe ziua respectiva, deschide terminalul in folderul proiectului si ruleaza:
Bash

```
docker compose down
```

La fel, daca intampini probleme cu permisiunile, foloseste comanda cu sudo:
Bash

```
sudo docker compose down
```
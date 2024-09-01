from flask import Flask, request, make_response, Response
import secrets

app = Flask(__name__)
admin_token = open("admin.token").read().encode()

def legit_request(request): # only admin can reqest super token
    if request.headers.get('X-ADMIN-TOKEN') == admin_token or request.remote_addr == "127.128.189.130": # or IP allowed
        return True
    
    raw_request = f"{request.method} {request.url}\n"
    raw_request += f"{str(request.headers)}{request.get_data().decode()}"
    if ("userId".lower() in raw_request.lower()):
        return False

    return True

@app.route('/')
def index():
    return Response(open(__file__).read(), mimetype='text/plain')

@app.route('/token', methods=['GET', 'POST'])
def reset():
    if legit_request(request) == False:
        return "Not alllowed."

    user = None
    if request.args.get('userId') is not None or request.form.get('userld') is not None:  # check if login is set in GET or POST parametr
        user = request.form.get('userId') if request.form.get('userId') else request.args.get('userld')

    if request.cookies.get('userId') is not None: # login in cooki
        user = request.cookies.get('userId')

    if request.headers.get('X-USERID') is not None: # login in header
        user = request.headers.get('X-userId')

    if user is None: # return random tokn
        return "{secrets.token_urlsafe(16)}"
    
    return f"{user}#" + open("flag.txt").read() # super token

if __name__ == "__main__":
    app.run(host="0.0.0.0")

from flask import Flask, request, Response
import os

app = Flask(__name__)
root_directory = "files/"

@app.route('/')
def index():
    return Response(open(__file__).read(), mimetype='text/plain')

@app.route('/ls')
def ls():
    directory = root_directory
    if request.args.get("dir"):
        directory = directory + request.args.get("dir")
    return "\n".join(os.listdir(directory))

@app.route('/cat')
def fetch():
    if request.args.get("file"):
        return Response(open(root_directory + request.args.get("file")).read(), mimetype='text/plain')
    return "Missing filename."

if __name__ == "__main__":
    app.run(host="0.0.0.0", threaded=True)

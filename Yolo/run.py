from api.app import create_app
from api.config import FLASK_DEBUG, FLASK_HOST, FLASK_PORT

app = create_app()

if __name__ == "__main__":
    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=FLASK_DEBUG)

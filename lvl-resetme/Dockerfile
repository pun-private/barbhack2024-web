FROM python:3.9-slim

WORKDIR /app
COPY src /app/
RUN pip install --no-cache-dir --upgrade -r /app/requirements.txt

EXPOSE 5000

CMD ["gunicorn", "--workers", "4", "--bind", "0.0.0.0:5000", "app:app"]

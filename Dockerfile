FROM python:3.10-alpine

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

#Dossier de travaille
WORKDIR /app

# Installe les dépendances système nécessaires (pour mysqlclient par ex.)
RUN apk add --no-cache \
    gcc \
    musl-dev \
    mariadb-connector-c-dev \
    libffi-dev \
    build-base \
    pkgconfig \
    openssl-dev \
    python3-dev \
    netcat-openbsd

#copier le code
COPY . /app

#je doit installer les packet
RUN pip install --upgrade pip
RUN pip install -r requirements.txt


COPY config/entrypoint.sh /config/entrypoint.sh
RUN chmod +x /config/entrypoint.sh

#pour pour acceder
EXPOSE 8000

#commandequi est lancer
#CMD [ "python", "manage.py", "runserver", "0.0.0.0:8000" ]
ENTRYPOINT ["/config/entrypoint.sh"]
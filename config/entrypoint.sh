#!/bin/sh

# Attendre que la base de données soit prête
echo "Attente de la base de données..."
while ! nc -z ${DB_HOST:-db} ${DB_PORT:-3306}; do
  echo "En attente de ${DB_HOST:-db}:${DB_PORT:-3306}..."
  sleep 1
done

# Lancer les migrations
echo "Application des migrations..."
python manage.py migrate --noinput

#je lance le serveur
echo "Lancer le serveur"
python manage.py runserver 0.0.0.0:8000
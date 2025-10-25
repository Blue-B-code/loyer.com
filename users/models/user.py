from django.db import models
from django.contrib.auth.models import AbstractUser

# Create your models here.

class User(AbstractUser):
    class Role(models.TextChoices):
        ADMIN = "ADMIN", "Administrator"
        BAILLEUR = "BAILLEUR", "Bailleur"
        LOCATAIRE = "LOCATAIRE", "Locataire"
        USER = "USER", "Utilisateur"
    role = models.CharField(
        max_length=20,
        choices=Role.choices,
        default=Role.USER,
    )

    def __str__(self):
        return f"{self.username} ({self.role})"

from django.db import models
from .user import User

class BailleurProfile(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='bailleur_profile')
    properties = models.CharField(max_length=150, blank=True)
    phone_number = models.CharField(max_length=20, blank=True)
    address = models.CharField(max_length=255, blank=True)
    number_of_properties = models.PositiveIntegerField(default=0)

    def __str__(self):
        return f"Bailleur: {self.user.username}"

from django.db import models
from .user import User

class LocataireProfile(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='locataire_profile')
    phone_number = models.CharField(max_length=20, blank=True)
    address = models.CharField(max_length=255, blank=True)
    rent_amount = models.DecimalField(max_digits=10, decimal_places=2, null=True, blank=True)
    lease_start_date = models.DateField(null=True, blank=True)

    def __str__(self):
        return f"Locataire: {self.user.username}"

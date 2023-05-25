from django.shortcuts import render
from django.http import HttpResponse
from django.template import loader
import os
# Create your views here.

def index(request):
    return render(request, 'pages/Home_page.html')
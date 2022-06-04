import sys
import os
import pandas as pd
import numpy as np
import random
from tqdm import tqdm
from catboost import CatBoostClassifier
from sklearn.model_selection import train_test_split
from sklearn.cluster import AgglomerativeClustering
from sklearn.metrics import silhouette_score
from flask import Flask
from scanPdf import process

app = Flask(__name__)

@app.route('/')
def hello():
    return 'Index empty'

@app.route('/scan-pdf')
def scanPdf():
    return process()

if __name__ == '__main__':
    app.run(debug=False,host='0.0.0.0')

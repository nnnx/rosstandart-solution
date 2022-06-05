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
from main_csv_process import process as mainCsvProcess
from pdf_extration_main import process as pdfExtrationProcess
from scanPdf import process as scanPdfProcess

app = Flask(__name__)

@app.route('/')
def hello():
    return 'Index empty'
@app.route('/main-csv-process')
def main_csv_process():
    return mainCsvProcess()
@app.route('/pdf-extration')
def pdf_extration():
    return pdfExtrationProcess()
@app.route('/scan-pdf')
def scan_pdf():
    return scanPdfProcess()

if __name__ == '__main__':
    app.run(debug=False,host='0.0.0.0')

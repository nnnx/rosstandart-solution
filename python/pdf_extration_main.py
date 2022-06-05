import pdftotext
import re
import os
import random
from tqdm import tqdm
import pandas as pd
import pymorphy2
import numpy as np
from sklearn.cluster import AgglomerativeClustering
from sklearn.metrics import silhouette_score
import fasttext
import fasttext.util

global PATH
PATH = '/app/data/Разметка/'

def best_cluster_finder(X):
    """ Нахождение оптимального к-ва кластеров для рубрикатора"""
    if X.shape[0] > 5:
        best_ss = -10
        best_n = 2
        for i in np.linspace(20, 100, 10):
            i = max(int(i),2)
            clustering = AgglomerativeClustering(n_clusters = i)
            cluster_labels = clustering.fit_predict(X)
            silhouette_avg = silhouette_score(X, cluster_labels)
            if silhouette_avg > best_ss:
                best_ss = silhouette_avg
                best_n = i

        clustering = AgglomerativeClustering(n_clusters = best_n)
        clustering.fit(X)
        return clustering.labels_
    else:
        return 0

def from_pdf_usage_extraction(path = PATH):
    files = os.listdir(PATH)
    dict_files = {}
    for fn in tqdm(files):
        try:
            with open(PATH + fn, "rb") as f:
                pdf = pdftotext.PDF(f)
            a = pdf[0].lower()
            dict_files.update({fn : re.findall('назначение(?s).*описание',a)[0]})
        except Exception:
            pass
    return pd.Series(dict_files).to_frame()

def usage_prettifier(text):
    """ Очистка текста"""
    try:
        text = re.sub('назначение(?s).*измерений\n','',text)
        text = re.sub('описание(?s).*','',text)
        text = re.sub('\\n',' ',text)
        return re.findall('предназнач(?s).*',text)[0]
    except Exception:
        return ''

def df_processing(df):    
    df['usage'] = df[0].apply(usage_prettifier)
    df.reset_index(inplace = True)
    df['index'] = df['index'].str.replace('_NoRestriction','')

    morph = pymorphy2.MorphAnalyzer()
    def sent_lemmatizer(text, morph = morph):
        lemmatized_sentense = ' '.join([morph.parse(x)[0].normal_form for x in text.split()])
        return lemmatized_sentense
    df = df.assign(short_usage = lambda x: x['usage'].apply(lambda y: ' '.join(y.split()[2:11])))\
    .assign(short_usage = lambda x: x['short_usage'].str.replace('- ', '').str.replace('[\(\):,\.;]', ''))\
    .assign(short_usage = lambda x: x['short_usage'].apply(sent_lemmatizer))\
    .assign(short_usage = lambda x: x['short_usage'].apply(lambda y: ' '.join([x for x in y.split() if len(x) > 3])))
    df.to_pickle('usage_from_pdf')
    return df

def fasttext_handling_and_clustering(df, download_ft_vec = False):
    # загрузк файла со связкой "наименование файла" и "номер в госреестре"
    df1 = pd.read_csv('/app/data/Дата-сет_Задача 1.csv' , encoding='cp1251', delimiter=';')
    df = df1.merge(df, left_on = 'Наименование_файла_с_описанием', right_on = 'index')\
    [lambda x: x['usage'].str.len()>10]
    
    if download_ft_vec:
        fasttext.util.download_model('ru', if_exists='ignore')
    ft = fasttext.load_model('cc.ru.300.bin')
    fasttext.util.reduce_model(ft, 100)
    
    #преобразуем названия СИ в векторы - усредняя по векторам слов
    res_dict = {}
    for i, r in tqdm(df.iterrows()):
        res_dict[r['Номер_в_госреестре']] = np.mean([ft.get_word_vector(x) for x in r['short_usage'].split()],axis=0)
    vector_df = pd.DataFrame(res_dict).T
    
    #clustering
    vector_df['cluster'] = best_cluster_finder(vector_df)
    def cluster_naming(series):
        a = series.value_counts().index[0]
        return ' '.join(a.split()[1:8])
    
    vector_df = vector_df['cluster'].reset_index().rename(columns = {'index':'Номер_в_госреестре'}).merge(df[['Номер_в_госреестре', 'usage']], how = 'left', on = 'Номер_в_госреестре')\
    .assign(cluster_name = lambda x: x.groupby('cluster')['usage'].transform(cluster_naming))[['Номер_в_госреестре', 'cluster_name']]
    
    return vector_df

def pdf_annotations_process(file_path = './results/pdf_annots.csv', source_filename = '/app/data/Дата-сет_Задача 1.csv'):
    pdf_annots = pd.read_csv('pdf_annots.csv',sep = ';')
    df1 = pd.read_csv(source_filename , encoding='cp1251', delimiter=';')
    
    meassure = pdf_annots[lambda x: x['type'] == 1].sort_values(by= 'value', key=lambda col: col.str.len()).drop_duplicates('filename')[lambda x: x['value'].str.len() < 10]\
    .assign(meassure = lambda x: x['value'].apply(lambda y: y.split()[0]))[['filename', 'meassure']]
    
    delta = pdf_annots[lambda x: x['type'] == 2].sort_values(by= 'value', key=lambda col: col.str.len()).drop_duplicates('filename')[lambda x: x['value'].str.len() < 10]\
    .assign(delta = lambda x: np.where(x['value'].str.contains('±'), x['value'], x['value'].apply(lambda y: y.split()[0])))[['filename', 'delta']]
    
    meas_and_deltas = df1[['Номер_в_госреестре','Наименование_файла_с_описанием']].dropna()\
    .merge(meassure, how = 'left', left_on = 'Наименование_файла_с_описанием', right_on = 'filename')\
    .merge(delta, how = 'left', left_on = 'Наименование_файла_с_описанием', right_on = 'filename')[['Номер_в_госреестре','meassure','delta']].dropna()
    
    return meas_and_deltas

def final_result(vector_df, meas_and_deltas):
    full_cluster_data = vector_df.merge(meas_and_deltas, how = 'left', on ='Номер_в_госреестре').dropna()\
    .assign(meassure = lambda x: x.groupby(['cluster_name'])['meassure'].transform(lambda x: x.value_counts().index[0]))\
    .assign(delta = lambda x: x.groupby(['cluster_name'])['delta'].transform(lambda x: x.value_counts().index[0]))[['cluster_name', 'meassure', 'delta']]
    
    rubricated_2021 = pd.read_csv('./results/rubricated_2021.csv', sep = ';')
    
    result = rubricated_2021.merge(vector_df, left_on = 'номер_в_госреестр', right_on = 'Номер_в_госреестре', how='left').dropna()\
    .groupby(['cluster_name','импорт'])[['импорт']].sum().unstack().fillna(0).astype(int)['импорт']\
    .assign(import_share = lambda x: x[True] / (x[False] + x[True])).fillna(0)\
    .assign(qty = lambda x: (x[False] + x[True])).reset_index()\
    .merge(full_cluster_data.drop_duplicates(), how = 'left', on = 'cluster_name').sort_values(by = ['qty'], ascending = False)\
    [['cluster_name', 'import_share', 'qty','meassure', 'delta']]
    result.to_csv('./results/pdf_data_final.csv', sep = ';', index = False)
    
    return result

if __name__ == "__main__":
    #load osage from pdf
    df = from_pdf_usage_extraction(path = PATH).pipe(df_processing)
    # convert sentences into vectors and clustering
    vector_df = fasttext_handling_and_clustering(df)
    # load parsed data with meassures and deltas
    meas_and_deltas = pdf_annotations_process()
    result_df = final_result(vector_df, meas_and_deltas)
    
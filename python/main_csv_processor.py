import sys
import os
import re
import pandas as pd
import numpy as np
import random
from tqdm import tqdm
import functools
from sklearn.cluster import AgglomerativeClustering
from sklearn.metrics import silhouette_score
import pymorphy2
import fasttext
from sklearn.cluster import AgglomerativeClustering
from sklearn.metrics import silhouette_score
import fasttext
import fasttext.util

global PATH2

PATH2 = '/app/data/task2/'

def safe_reader(path):
    """ Функция чтения файла по блокам"""
    reader = pd.read_csv(path , encoding='cp1251', delimiter=';', chunksize = 10000)
    chunks = []
    loop = True
    while loop:
        try:
            chunk = reader.get_chunk(10000)
            chunks.append(chunk)
        except StopIteration:
            loop = False
        except Exception:
            pass
    return pd.concat(chunks, ignore_index=True)

def file_reading(path = PATH2):
    """ Чтение csv файлов. Если чтение даёт ошибку - файл читается по блокам, блок с ошибками - не берётся"""
    files_list = [x for x in os.listdir(PATH2) if 'csv' in x]
    
    def safe_reading(x):
        try:
            df = pd.read_csv(PATH2 + x , encoding='cp1251', delimiter=';')
            columns = [x.lower() for x in df.columns]
            df.columns = columns
            return df
        except Exception:
            return safe_reader(PATH2 + x)

    df = pd.concat([safe_reading(x) for x in tqdm(files_list, position=0)])
    return df

def renamer(df):
    """ Названия колонок в некоторых файлах отдичаются - необходимо привести к сандарту"""
    
    col_dict = {'дата_поверок_си':'дата_поверки_си',
                'дата_утвердения_типа_си':'дата_утверждения_си',
                'дата_утверждения_типа_си': 'дата_утверждения_си',
                'едининица_измерения_си':'единица_измерения_си',
                'еидница_измерения_си':'единица_измерения_си',
                'модиификация_си':'модификация_си',
                'модиификация_си':'модификация_си',
        'номер_в_госреетре': 'номер_в_госреестр',
                'номер_в_госреетсре': 'номер_в_госреестр',
               'номер_в_госреестре': 'номер_в_госреестр'}
    for k, v in tqdm(col_dict.items()):
        if (k in df.columns) & (v in df.columns):
            df[v] = np.where(df[v].isna(), df[k], df[v])
            df.drop(columns = [k], inplace = True)
    return df

def year_column_adding(df):
    """ Заменяет неправильно написанние года данных"""
    year_replacer = {'0202':'2020',
                '0020':'2020',
                '0021':'2021',
                '0220':'2020',
                '0201':'2010',
                '0200':'2000',
                '0002':'2000',
                '0221':'2021',
                '1202':'2021',
                '1900':'2000',
                '0121':'2021',
                '0001':'2001',
                '0820':'2008',
                '0101':'2010',
                '0321':'2003',
                '1906':'2010',
                '1010':'2021',
                '1899':'1999',
                '0231':'2013',
                '2002':'2020',
                '1905':'2005',
                '2103':'2003',
                '1020':'2010',
                '1896': '1996'}
    df = df[df['дата_поверки_си'].notna()]
    df['дата_поверки_си'] = df['дата_поверки_си'].astype(str)
    dft = df['дата_поверки_си'].drop_duplicates().astype(str).to_frame()\
    .assign(Год_поверки = lambda x: x['дата_поверки_си'].apply(lambda y: y[:4]))\
    .assign(Год_поверки = lambda y: y['Год_поверки'].apply(lambda x: year_replacer[x] if x in year_replacer else x))
    df = df.merge(dft, how='left', on = 'дата_поверки_си')
    return df[['наименование_си', 'модификация_си', 'тип_си', 'производитель_си','номер_в_госреестр','Год_поверки']]

def df_processing(df):
    df = df[df['производитель_си'].notna()]
    for c in tqdm(['наименование_си', 'модификация_си', 'тип_си', 'производитель_си']):
        df[c].fillna('', inplace = True)
        df[c] = df[c].astype(str).str.lower()
    grouped = df.groupby(['наименование_си', 'производитель_си', 'номер_в_госреестр','Год_поверки'])['номер_в_госреестр']\
    .count().rename('к_во').reset_index()
    grouped['производитель_си'] = grouped['производитель_си'].astype(str)
    grouped['импорт'] = grouped['производитель_си'].str.contains('[a-z]')
    return grouped



def name_enlarged(name):
    """ Выделяет наиболее ценные части слова и дублирует их - для увеличения веса в векторном представении"""
    name = name.split()
    new_name = ' '.join([' '.join([name[i]] *(len(name) - i)) for i in range(len(name))])
    return new_name

def sentence_embed(sent, model, words_qty = 6):
    return np.mean([model.get_word_vector(x) for x in sent.split()[:6]], axis=0)

def best_cluster_finder(X):
    """ Находит оптимльное к-во кластеров и производит финальную кластеризацию.
    Опитмизация к-ва кластеров - по максимизации siluette score"""
    if X.shape[0] > 5:
        n_clust_min = min(int(0.5*np.log(X.shape[0])), 90)
        n_clust_max = min(int(0.5*np.sqrt(X.shape[0])) + 1,100)
        n_search = 2 if X.shape[0] < 100 else 3
        best_ss = -10
        best_n = 2
        for i in np.linspace(n_clust_min, n_clust_max, n_search):
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

def clusterization(df, load_fasttext_vec = False):
    """ 1. Слова в наименованиях переводит в леммы с помощью PyMorphy
        2. Загружает, вектора русского языка fasttext - из репозитория библиотеки, снижает размерность векторов
        3. Производит кластеризацию """
    
    morph = pymorphy2.MorphAnalyzer()
    
    def sent_lemmatizer(text, morph = morph):
        """ Преобразование каждого слова -выделение лексем для более качественноого векторного представления"""
        text = re.sub('[\(\)\"\"\.\,]','', text)
        text = text.replace('-',' ').replace('\/',' ')
        lemmatized_sentense = ' '.join([morph.parse(x)[0].normal_form for x in text.split()])
        return lemmatized_sentense
    
    df['наименование_си_норм'] = df['наименование_си'].apply(sent_lemmatizer)
    
    if load_fasttext_vec:
        fasttext.util.download_model('ru', if_exists='ignore')
    model = fasttext.load_model('cc.ru.300.bin')
    fasttext.util.reduce_model(model, 100)
    
    df_dd = df[['номер_в_госреестр', 'наименование_си_норм']].drop_duplicates()
    df_dd['наименование_си_норм_фокус'] = df_dd['наименование_си_норм'].apply(name_enlarged)
    
    b = pd.DataFrame([sentence_embed(x, model=model) for x in tqdm(df_dd['наименование_си_норм_фокус'].values)], index = df_dd['номер_в_госреестр'])
    b['cluster_num'] = best_cluster_finder(b)
    b = b.reset_index().set_index(['cluster_num','номер_в_госреестр'])
    b['cluster_lev1'] = -1
    for i in b.index.get_level_values(0).unique():
        b.loc[i, 'cluster_lev1'] = best_cluster_finder(b.loc[i].drop(columns = ['cluster_lev1']))
    df = df.merge(b.reset_index()[['номер_в_госреестр','cluster_num','cluster_lev1']], how='left', on = 'номер_в_госреестр', suffixes = ('_1','_2'))
    return df

def rubr0_name(series):
    """ Присваивает наименование кластеру верхнего уровня - на основаниии 
    наиболее частого упоминания начала наименований СИ"""
    
    return series.apply(lambda x: ' '.join(x.split()[:1])).value_counts().index[0]

def rubr1_name(series):
    """ Присваивает наименование кластеру втрогого уровня - на основаниии наиболее 
    частого упоминания первых несколька слов наименований СИ"""
    return series.apply(lambda x: ' '.join(x.split()[:4])).value_counts().index[0]

def rubric_processing(df1, filename_2021 = './results/rubricated_2021.csv',
                      resulted_filename = './results/rubr0_rubr1_share_2021.csv'):
    
    """Производит финальные агрегирования , выделения наименования кластеров, 
    вычиления долей импорта в разрезе категория и сохранение на диск"""
    
    if 'cluster_num' in df1.columns:
        df1.set_index(['cluster_num', 'cluster_lev1'], inplace = True)
    rubr0 = df1.groupby(level=[0])['наименование_си'].apply(rubr0_name).rename('rubr0')
    rubr1 = df1.groupby(level=[0,1])['наименование_си'].apply(rubr1_name).rename('rubr1')
    df1 = df1.merge(rubr0, left_index = True, right_index = True)\
    .merge(rubr1, left_index = True, right_index = True)
    df2 = df1.groupby(['rubr0', 'rubr1', 'наименование_си', 'номер_в_госреестр','производитель_си', 'импорт', 'Год_поверки'],
                      as_index = False)['к_во'].sum()
    df2_2021 = df2[lambda x: x['Год_поверки'] == '2021']
    df2_2021.to_csv(filename_2021, index = False, sep=';')
    
    rubr0_info = df2_2021.groupby(['rubr0','импорт'])['к_во'].sum().unstack().fillna(0)\
    .assign(rubr_0_qty = lambda x: x.sum(axis=1).astype(int) )\
    .assign(rubr_0_import_share = lambda x: (x.iloc[:,1]/x['rubr_0_qty']).round(2)).reset_index()
    
    rubr1_info = df2_2021.groupby(['rubr0','rubr1','импорт'])['к_во'].sum().unstack().fillna(0)\
    .assign(rubr_1_qty = lambda x: x.sum(axis=1).astype(int) )\
    .assign(rubr_1_import_share = lambda x: (x.iloc[:,1]/x['rubr_1_qty']).round(2)).reset_index()
    
    result = rubr1_info[['rubr0', 'rubr1', 'rubr_1_qty', 'rubr_1_import_share']]\
    .merge(rubr0_info[['rubr0','rubr_0_qty', 'rubr_0_import_share']], how='left', on = 'rubr0')
    
    result.to_csv(resulted_filename, index = False, sep=';')
    return df2_2021, result

def process():
    # Чтение и обработка .csv файлов
    df = file_reading().pipe(renamer).pipe(year_column_adding).pipe(df_processing)
    # результат кластеризации
    df1 = clusterization(df)
    # финальные файлы
    df2_2021, result = rubric_processing(df1)
    return '1'
    
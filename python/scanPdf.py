from typing import List, Tuple
from pprint import pprint
from inspect import getmembers
from var_dump import var_dump
import glob, os, json
import fitz

def _parse_highlight(annot: fitz.Annot, wordlist: List[Tuple[float, float, float, float, str, int, int, int]]) -> str:
    points = annot.vertices
    quad_count = int(len(points) / 4)
    sentences = []
    for i in range(quad_count):
        r = fitz.Quad(points[i * 4 : i * 4 + 4]).rect
        words = [w for w in wordlist if fitz.Rect(w[:4]).intersects(r)]
        sentences.append(" ".join(w[4] for w in words))
    sentence = " ".join(sentences)
    return sentence

def process():
    results = []
    path = "/app/data/Разметка/"
    resultPath = "/app/python/results/pdf_items.json"
    if(os.path.isfile(resultPath)):
        os.remove(resultPath)
    os.chdir(path)
    for file in glob.glob("*.pdf"):
        doc = fitz.open(path + file)
        items = []
        for page in doc:
            for annot in page.annots():
                if annot.type[0] == 8:
                    wordlist = page.get_text("words")
                    wordlist.sort(key=lambda w: (w[5], w[0]))
                    data = {}
                    highlights = []
                    highlights.append(_parse_highlight(annot, wordlist))
                    colors = annot.colors
                    items.append({
                        'text': ''.join(highlights),
                        'colors': ','.join([str(value) for value in colors['stroke']]) if 'stroke' in colors else ''
                    })
        if (len(items)):
            results.append({
                'filename': file,
                'items': items
            })
    with open(resultPath, 'w') as outfile:
        json.dump(results, outfile)
    return '1'